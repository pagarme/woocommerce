<?php

namespace Pagarme\Core\Recurrence\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod as PaymentMethod;
use Pagarme\Core\Payment\ValueObjects\CardId;
use Pagarme\Core\Payment\ValueObjects\CardToken;
use Pagarme\Core\Payment\ValueObjects\Discounts;
use Pagarme\Core\Recurrence\Aggregates\Increment;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Factories\ChargeFactory;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\OrderLogService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Payment\Aggregates\Order as PaymentOrder;
use Pagarme\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Recurrence\Aggregates\Invoice;
use Pagarme\Core\Recurrence\Aggregates\SubProduct;
use Pagarme\Core\Recurrence\Aggregates\Subscription;
use Pagarme\Core\Recurrence\Factories\InvoiceFactory;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;
use Pagarme\Core\Recurrence\ValueObjects\SubscriptionStatus;
use Pagarme\Core\Recurrence\Factories\SubscriptionFactory;

final class SubscriptionService
{
    const CAN_CREATE_PAYMENT_MESSAGE = "Can't create payment. Please review the information and try again.";
    private $logService;
    /**
     * @var LocalizationService
     */
    private $i18n;
    private $subscriptionItems;
    private $apiService;

    private $recurrenceService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
        $this->apiService = new APIService();
        $this->i18n = new LocalizationService();
    }

    public function createSubscriptionAtPagarme(PlatformOrderInterface $platformOrder)
    {
        try {
            $orderService = new OrderService();

            $this->setPlatformOrderPending($platformOrder);

            //build PaymentOrder based on platformOrder
            $order = $orderService->extractPaymentOrderFromPlatformOrder($platformOrder);
            $this->logService->orderInfo(
                $platformOrder->getCode(),
                'Creating order.',
                $order
            );
            $subscription = $this->extractSubscriptionDataFromOrder($order);

            $this->setDiscountCycleSubscription($subscription, $platformOrder);

            //Send through the APIService to Pagarme
            $subscriptionResponse = $this->apiService->createSubscription($subscription);

            $i18n = new LocalizationService();

            $forceCreateOrder = MPSetup::getModuleConfiguration()->isCreateOrderEnabled();

            if ($subscriptionResponse === null) {
                $message = $i18n->getDashboard(self::CAN_CREATE_PAYMENT_MESSAGE);
                throw new \Exception($message, 400);
            }

            $this->getSubscriptionMissingData($subscriptionResponse, $subscription);

            $originalSubscriptionResponse = $subscriptionResponse;

            $subscriptionFactory = new SubscriptionFactory();
            if (!$this->checkResponseStatus($subscriptionResponse)) {

                if (!empty($subscriptionResponse['id'])) {
                    $failedSubscription =
                        $subscriptionFactory
                            ->createFromFailedSubscription(
                                $subscriptionResponse
                            );

                    $this->cancelSubscriptionAtPagarme($failedSubscription);
                }

                if (!$forceCreateOrder) {
                    $message = $i18n->getDashboard(self::CAN_CREATE_PAYMENT_MESSAGE);
                    throw new \Exception($message, 400);
                }
            }

            $platformOrder->save();
            $response = $subscriptionFactory->createFromPostData($subscriptionResponse);

            $response->setPlatformOrder($platformOrder);
            $response->setCurrentCharge($subscriptionResponse['current_charge']);

            $handler = $this->getResponseHandler($response);
            $handler->handle($response);
            $platformOrder->save();

            if (
                $forceCreateOrder &&
                !$this->checkResponseStatus($originalSubscriptionResponse)
            ) {
                $message = $i18n->getDashboard(self::CAN_CREATE_PAYMENT_MESSAGE);
                throw new \Exception($message, 400);
            }

            return [$response];

        } catch (\Exception $e) {
            $exceptionHandler = new ErrorExceptionHandler();
            $paymentOrder = new PaymentOrder;
            $paymentOrder->setCode($platformOrder->getcode());
            $frontMessage = $exceptionHandler->handle($e, $paymentOrder);
            throw new \Exception($frontMessage, 400);
        }
    }

    /**
     * @param Subscription $subscription
     * @param PlatformOrderInterface $platformOrder
     */
    private function setDiscountCycleSubscription(
        Subscription $subscription,
        PlatformOrderInterface $platformOrder
    ) {
        $discountOrder = $platformOrder->getPlatformOrder()->getDiscountAmount();

        if ($discountOrder == 0) {
           return;
        }

        $cycles = $this->getDiscountCycles($platformOrder, $subscription);
        $discountSubscription = Discounts::FLAT((($discountOrder * -1) * 100), $cycles);
        $subscription->setDiscounts([$discountSubscription]);
    }

    private function getDiscountCycles($platformOrder, $subscription)
    {
        $orderService = new OrderService();
        $order = $orderService->extractPaymentOrderFromPlatformOrder($platformOrder);
        $subscriptionItems = $this->getSubscriptionItems($order);
        $cycles = 1;

        if (!empty($subscriptionItems)) {
            $subscriptionItems = array_filter($subscriptionItems);
        }

        $changeCycle = true;
        $planItems = [];
        foreach ($subscriptionItems as $subscriptionItem) {
            if ($subscriptionItem instanceof Plan) {
                $planItems = $subscriptionItem->getItems();
            }
            if (empty($subscriptionItem->getApplyDiscountInAllProductCycles())) {
                $changeCycle = false;
                break;
            }
        }

        $items = $subscription->getItems();

        if (empty($items) && !empty($planItems) && $subscription->getPlanId()) {
            $items = $planItems;
        }

        if ($changeCycle) {
            $validSubscriptionItems = array_filter($items, [$this, 'isValidSubscriptionItem']);
            $cycles = $this->getRecurrenceService()
                ->getGreatestCyclesFromItems($validSubscriptionItems);
        }

        return $cycles;
    }

    /**
     * @param mixed $item
     * @return bool
     */
    private function isValidSubscriptionItem($item)
    {
        return method_exists($item, 'getSelectedRepetition')
            && method_exists($item, 'getRecurrenceType')
            && (!empty($item->getSelectedRepetition() || $item->getRecurrenceType() === Plan::RECURRENCE_TYPE));
    }

    private function extractSubscriptionDataFromOrder(PaymentOrder $order)
    {
        $subscription = new Subscription();
        $config = MPSetup::getModuleConfiguration();

        $subscriptionSettings = $this->getSubscriptionSettings($order);

        $this->fillCreditCardData($subscription, $order);

        $plan = $this->extractPlanFromOrder($order);
        $this->fillSubscriptionItems(
            $subscription,
            $order
        );
        $this->fillDescription($subscription);

        $this->fillPlanId($subscription, $plan);
        $this->fillInterval($subscription, $plan);

        if ($order->getPaymentMethod() === PaymentMethod::boleto()) {
            $this->fillBoletoData($subscription);
        }

        if ($order->getShipping() !== null) {
            $this->fillShipping($subscription, $order);
        }

        $subscription->setCode($order->getCode());
        $subscription->setCustomer($order->getCustomer());
        $subscription->setBillingType($subscriptionSettings->getBillingType());
        $subscription->setPaymentMethod($order->getPaymentMethod());
        $subscription->setStatementDescriptor($config->getCardStatementDescriptor());

        return $subscription;
    }

    private function extractPlanFromOrder(PaymentOrder $order)
    {
        $planId = $order->getItems()[0]->getPagarmeId();
        if (!$planId) {
            return null;
        }

        $code = $order->getItems()[0]->getCode();

        $planService = new PlanService();
        $plan = $planService->findByProductId($code);
        if (!$plan) {
            return null;
        }

        return $plan;
    }

    private function getSubscriptionSettings($order)
    {
        $items = $this->getSubscriptionItems($order);

        if (empty($items[0]) || empty($items)) {
            throw new \Exception('Recurrence items not found', 400);
        }

        return $items[0];
    }

    /**
     * @param PaymentOrder $order
     * @return array
     */
    private function getSubscriptionItems(PaymentOrder $order)
    {
        $items = [];

        foreach ($order->getItems() as $product) {
            if ($product->getType() !== null) {
                $items[] =
                    $this->getRecurrenceService()
                        ->getRecurrenceProductByProductId(
                            $product->getCode()
                        );
            }
        }

        return $items;
    }

    private function extractSubscriptionItemsFromOrder($order)
    {
        $subscriptionItems = [];

        foreach ($order->getItems() as $item) {
            if (method_exists($item, 'getPagarmeId') && $item->getPagarmeId()) {
                continue;
            }
            $subProduct = new SubProduct();
            $cycles = 1;
            $selectedOption = $item->getSelectedOption();

            if ($selectedOption) {
                $cycles = $selectedOption->getCycles() ?: 0;
                $subProduct->setSelectedRepetition($selectedOption);
            }

            if (!empty($cycles)) {
                $subProduct->setCycles($cycles);
            }

            $subProduct->setDescription($item->getDescription());
            $subProduct->setName($item->getName());
            $subProduct->setProductId($item->getCode());
            $subProduct->setQuantity($item->getQuantity());
            $pricingScheme = PricingScheme::UNIT($item->getAmount());
            $subProduct->setPricingScheme($pricingScheme);

            $subscriptionItems[] = $subProduct;
        }

        return $subscriptionItems;
    }

    private function fillCreditCardData($subscription, $order)
    {
        if ($this->paymentExists($order)) {
            $payments = $order->getPayments();

            $identify = $this->extractCreditCardIdentifyFromPayment($payments[0]);

            if (is_a($identify, CardToken::class)) {
                $subscription->setCardToken(
                    $identify->getValue()
                );

                $subscription->setMetadata(
                    $this->extractSaveOnSuccessFromPayment($payments[0])
                );
            }

            if (is_a($identify, CardId::class)) {
                $subscription->setCardId(
                    $identify->getValue()
                );
            }

            $subscription->setInstallments(
                $this->extractInstallmentsFromPayment($payments[0])
            );
        }
    }

    private function fillBoletoData($subscription)
    {
        $boletoDays = MPSetup::getModuleConfiguration()->getBoletoDueDays();
        $subscription->setBoletoDays($boletoDays);
    }

    private function fillSubscriptionItems($subscription, $order)
    {
        $this->subscriptionItems = $this->extractSubscriptionItemsFromOrder(
            $order
        );
        $subscription->setItems($this->subscriptionItems);
    }

    private function fillPlanId($subscription, $plan)
    {
        if ($plan !== null) {
            $subscription->setPlanId($plan->getPagarmeId());
        }
        return null;
    }

    private function fillInterval($subscription, Plan $plan = null)
    {
        if ($plan !== null) {
            $subscription->setIntervalType($plan->getIntervalType());
            $subscription->setIntervalCount($plan->getIntervalCount());
            return;
        }

        /**
         * @todo Subscription Intervals are comming from subscription items
         */
        if (empty($this->subscriptionItems[0]->getSelectedRepetition())) {
            return;
        }

        $intervalCount =
            $this->subscriptionItems[0]
                ->getSelectedRepetition()
                ->getIntervalCount();

        $intervalType =
            $this->subscriptionItems[0]
                ->getSelectedRepetition()
                ->getInterval();

        $subscription->setIntervalType($intervalType);
        $subscription->setIntervalCount($intervalCount);
    }

    private function fillDescription($subscription)
    {
        $subscription->setDescription($this->subscriptionItems[0]->getDescription());
    }

    private function fillShipping($subscription, $order)
    {
        $orderShipping = $order->getShipping();
        $subscription->setShipping($orderShipping);
    }

    private function paymentExists($order)
    {
        $payments = $order->getPayments();
        if (isset($payments) && isset($payments[0])) {
            return true;
        }

        return false;
    }

    private function extractCreditCardIdentifyFromPayment($payment)
    {
        if (method_exists($payment, 'getIdentifier')) {
            return $payment->getIdentifier();
        }

        return null;
    }

    private function extractSaveOnSuccessFromPayment($payment)
    {
        return ['saveOnSuccess' => $payment->isSaveOnSuccess()];
    }

    private function extractInstallmentsFromPayment($payment)
    {
        if (method_exists($payment, 'getInstallments')) {
            return $payment->getInstallments();
        }
    }

    private function checkResponseStatus($response)
    {
        if (
            !isset($response['status']) ||
            $response['status'] == 'failed'
        ) {
            return false;
        }

        $charge = $response['current_charge'];
        $chargeStatus = $charge->getStatus()->getStatus();

        if (
            !$chargeStatus ||
            $chargeStatus == 'payment_failed'
        ) {
            return false;
        }


        return true;
    }

    public function isSubscription($platformOrder)
    {
        $orderService = new OrderService();
        $order = $orderService->extractPaymentOrderFromPlatformOrder($platformOrder);
        $subscriptionItem = $this->getSubscriptionItems($order);

        if (count($subscriptionItem) == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Subscription $response
     * @return string
     */
    private function getResponseHandler($response)
    {
        $responseClass = get_class($response);
        $responseClass = explode('\\', $responseClass ?? '');

        $responseClass =
            'Pagarme\\Core\\Recurrence\\Services\\ResponseHandlers\\' .
            end($responseClass) . 'Handler';

        return new $responseClass;
    }

    private function setPlatformOrderPending($platformOrder)
    {
        //First platform order status and state after subscription creation success
        $platformOrder->setState(OrderState::stateNew());
        $platformOrder->setStatus(OrderStatus::pending());
    }

    private function getSubscriptionMissingData(&$subscriptionResponse, $subscription)
    {
        $subscriptionResponse['invoice'] =
            $this->getInvoiceFromSubscriptionResponse(
                $subscriptionResponse
            );
        $subscriptionResponse['current_charge'] = $this->getChargeFromInvoiceResponse(
            $subscriptionResponse['invoice']
        );

        $subscriptionResponse['plan_id'] = $subscription->getPlanIdValue();

        //@todo Remove when be implemented the "code" on mark1
        $this->setProductIdOnSubscriptionItems($subscriptionResponse, $subscription);
    }

    /**
    * @todo Remove when be implemented the "code" on mark1
    */
    private function setProductIdOnSubscriptionItems(&$subscriptionResponse, $subscription)
    {
        if ($subscription->getRecurrenceType() == Plan::RECURRENCE_TYPE) {
            return;
        }

        foreach ($subscriptionResponse['items'] as &$item) {
            $item['code'] = $this->getProductCode($item, $subscription);
        }
    }

    /**
     * @todo Remove when be implemented the "code" on mark1
     */
    private function getProductCode(&$item, $subscription)
    {
        foreach ($subscription->getItems() as $subItem) {
            if ($item['name'] == $subItem->getName()) {
                return $subItem->getProductId();
            }
        }
        return null;
    }

    /**
     * @param $subscriptionResponse
     * @return Invoice
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    private function getInvoiceFromSubscriptionResponse($subscriptionResponse)
    {
        $subscriptionId = new SubscriptionId($subscriptionResponse['id']);

        $invoiceResponse = $this->apiService->getSubscriptionInvoice($subscriptionId);
        $invoiceFactory = new InvoiceFactory();

        return $invoiceFactory->createFromApiResponseData($invoiceResponse);
    }

    /**
     * @param $invoiceResponse
     * @return Charge
     */
    private function getChargeFromInvoiceResponse($invoiceResponse)
    {
        $chargeResponse = $this->apiService->getCharge(
            $invoiceResponse->getCharge()->getPagarmeId()
        );

        $chargeFactory = new ChargeFactory();
        unset($chargeResponse['invoice']);

        $chargeResponse['cycle_start'] = $invoiceResponse->getCycleStart();
        $chargeResponse['cycle_end'] = $invoiceResponse->getCycleEnd();

        $charge = $chargeFactory->createFromPostData($chargeResponse);

        $charge->setInvoice($invoiceResponse);
        $charge->setInvoiceId($invoiceResponse->getPagarmeId()->getValue());
        $charge->setSubscriptionId($invoiceResponse->getSubscriptionId()->getValue());

        return $charge;
    }

    /**
     * @return array|Subscription[]
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function listAll()
    {
        return $this->getSubscriptionRepository()
            ->listEntities(0, false);
    }

    /**
     * @param $subscriptionId
     * @return array
     */
    public function cancel($subscriptionId)
    {
        try {
            $subscription = $this->getSubscriptionRepository()
                ->find($subscriptionId);

            if (!$subscription) {
                $message = $this->i18n->getDashboard(
                    'Subscription not found'
                );

                $this->logService->orderInfo(
                    null,
                    $message . " ID {$subscriptionId} ."
                );

                return [
                    "message" => $message,
                    "code" => 404
                ];
            }

            if ($subscription->getStatus() == SubscriptionStatus::canceled()) {
                $message = $this->i18n->getDashboard(
                    'Subscription already canceled'
                );

                return [
                    "message" => $message,
                    "code" => 200
                ];
            }

            $this->cancelSubscriptionAtPagarme($subscription);

            $subscription->setStatus(SubscriptionStatus::canceled());
            $this->getSubscriptionRepository()->save($subscription);

            $message = $this->i18n->getDashboard(
                'Subscription canceled with success!'
            );

            return [
                "message" => $message,
                "code" => 200
            ];
        } catch (\Exception $exception) {

            $message = $this->i18n->getDashboard(
                'Error on cancel subscription'
            );

            $this->logService->orderInfo(
                null,
                $message . ' - ' . $exception->getMessage()
            );

            return [
                "message" => $message,
                "code" => 200
            ];
        }
    }

    public function cancelSubscriptionAtPagarme(Subscription $subscription)
    {
        $apiService = new APIService();
        $apiService->cancelSubscription($subscription);
    }

    /**
     * @return SubscriptionRepository
     */
    public function getSubscriptionRepository()
    {
        return new SubscriptionRepository();
    }

    public function getSavedSubscription(SubscriptionId $subscriptionId)
    {
        $subscriptionRepository = new SubscriptionRepository();
        return $subscriptionRepository->findByPagarmeId($subscriptionId);
    }

    private function getRecurrenceService()
    {
        if (empty($this->recurrenceService)) {
            $this->recurrenceService = new RecurrenceService();
        }
        return $this->recurrenceService;
    }
}
