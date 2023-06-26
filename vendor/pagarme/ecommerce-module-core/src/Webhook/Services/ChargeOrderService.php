<?php

namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Kernel\Interfaces\ChargeInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Repositories\ChargeRepository;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Payment\Services\ResponseHandlers\OrderHandler;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Kernel\Services\ChargeService;

final class ChargeOrderService extends AbstractHandlerService
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ChargeRepository
     */
    private $chargeRepository;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @var OrderHandler
     */
    private $orderHandlerService;

    /**
     * @var MoneyService
     */
    private $moneyService;

    /**
     * @var LocalizationService
     */
    private $i18n;

    /**
     * ChargeOrderService constructor.
     */
    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->chargeRepository = new ChargeRepository();
        $this->orderService = new OrderService();
        $this->orderHandlerService = new OrderHandler();
        $this->moneyService = new MoneyService();
        $this->i18n = new LocalizationService();
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePaid(Webhook $webhook)
    {
        /**
         * @var Order $order
         */
        $order = $this->order;

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            return [
                "message" => "It is not possible to pay an order that was already canceled.",
                "code" => 200
            ];
        }

        /**
         * @var Charge|ChargeInterface $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        $platformOrder = $this->order->getPlatformOrder();
        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($charge->getLastTransaction());
            $outdatedCharge->setStatus($charge->getStatus());
            $charge = $outdatedCharge;
        }

        $paidAmount = $transaction->getPaidAmount();
        if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
            $charge->pay($paidAmount);
        }

        if ($charge->getPaidAmount() == 0) {
            $charge->setPaidAmount($paidAmount);
        }

        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $this->order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);
        $this->addWebHookReceivedHistory($webhook);

        $platformOrder->save();

        $response = $this->tryCancelMultiMethodsWithOrder();

        $returnMessage = $this->prepareReturnMessage($charge);

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . '  ' .
                $response . '  ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePartialCanceled(Webhook $webhook)
    {
        $order = $this->order;

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($transaction);
            $outdatedCharge->setStatus($charge->getStatus());
            $charge = $outdatedCharge;
        }

        $charge->cancel($transaction->getAmount());
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" => $returnMessage . ' ' . $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleOverpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleUnderpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleRefunded(Webhook $webhook)
    {
        $order = $this->order;

        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            return [
                "message" => "It is not possible to refund a charge of an order that was canceled.",
                "code" => 200
            ];
        }

        /**
         *
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        $cancelAmount = $charge->getAmount();
        if ($transaction !== null) {
            $outdatedCharge->addTransaction($transaction);
            $outdatedCharge->setStatus($charge->getStatus());
            $cancelAmount = $transaction->getAmount();
        }

        $charge->cancel($cancelAmount);
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . ' ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleAntifraudReproved(Webhook $webhook)
    {
        $this->addHistoryComment('Antifraud reproved');
        return $this->handlePaymentFailed($webhook);
    }

    protected function handleAntifraudApproved(Webhook $webhook)
    {
        return [
            "message" => $this->addHistoryComment('Antifraud aproved'),
            "code" => 200
        ];
    }

    protected function handleAntifraudManual(Webhook $webhook)
    {
        return [
            "message" => $this->addHistoryComment('Waiting manual analise in antifraud'),
            "code" => 200
        ];
    }

    protected function handleAntifraudPending(Webhook $webhook)
    {
        return [
            "message" => $this->addHistoryComment('Antifraud pending'),
            "code" => 200
        ];
    }

    protected function handlePaymentFailed(Webhook $webhook)
    {
        $order = $this->order;

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        $outdatedCharge = $this->chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        if ($transaction !== null) {
            $outdatedCharge->addTransaction($transaction);
        }

        $charge->failed();
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        $response = $this->tryCancelMultiMethodsWithOrder();

        $order->applyOrderStatusFromCharges();

        $orderHandler = $this->orderHandlerService->handle($order);

        return [
            "code" => 200,
            "message" =>
                $returnMessage . '  ' .
                $response . '  ' .
                $this->treatOrderMessage($orderHandler)
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     */
    protected function handleChargedback(Webhook $webhook)
    {
        $order = $this->order;
        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        $outdatedCharge = $this->chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }
        /**
         * @var Charge $outdatedCharge
         */
        if ($transaction !== null) {
            $charge->addTransaction($transaction);
        }

        $charge->chargedback();
        $order->updateCharge($charge);
        $this->orderRepository->save($order);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history, false);

        $this->orderService->syncPlatformWith($order, false);

        $returnMessage = $this->prepareReturnMessage($charge);

        return [
            "code" => 200,
            "message" => $returnMessage
        ];

    }

    /**
     * @return string
     */
    private function tryCancelMultiMethodsWithOrder()
    {
        $chargeService = new ChargeService();
        $chargeListPaid = $chargeService->getNotFailedOrCanceledCharges(
            $this->order->getCharges()
        );

        $logService = new LogService(
            'ChargeOrderService',
            true
        );

        $response = [];
        if (!empty($chargeListPaid && count($this->order->getCharges()) > 1)) {
            $logService->info('Try Cancel Charge(s)');

            foreach ($chargeListPaid as $chargePaid) {
                $message =
                    ($chargeService->cancel($chargePaid))->getMessage()
                    . ' - ' .
                    $chargePaid->getPagarmeId()->getValue();

                $logService->info($message);

                $response[] = $message;
            }
        }

        return implode('/', $response);
    }

    /**
     * @param Webhook $webhook
     * @throws InvalidParamException
     * @throws NotFoundException
     */
    protected function loadOrder(Webhook $webhook)
    {
        $this->orderRepository = new OrderRepository();

        /** @var Charge $charge */
        $charge = $webhook->getEntity();

        $order = $this->orderRepository->findByPagarmeId($charge->getOrderId());
        if ($order === null) {
            $orderDecoratorClass = MPSetup::get(
                MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS
            );

            /**
             * @var PlatformOrderInterface $order
             */
            $order = new $orderDecoratorClass();
            $order->loadByIncrementId($charge->getCode());

            $orderFactory = new OrderFactory();
            $order = $orderFactory->createFromPlatformData(
                $order,
                $charge->getOrderId()->getValue()
            );
        }

        $order->setCustomer($webhook->getEntity()->getCustomer());

        $this->order = $order;
    }

    public function prepareHistoryComment(ChargeInterface $charge)
    {
        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $this->moneyService->centsToFloat($charge->getPaidAmount());

            $history = $this->i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            );

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $history .= ". " . $this->i18n->getDashboard(
                    "Extra amount paid: %.2f",
                    $this->moneyService->centsToFloat($extraValue)
                );
            }

            if ($extraValue < 0) {
                $history .= ". " . $this->i18n->getDashboard(
                    "Remaining amount: %.2f",
                    $this->moneyService->centsToFloat(abs($extraValue))
                );
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $history = $this->i18n->getDashboard(
                    'Refunded amount: %.2f',
                    $this->moneyService->centsToFloat($refundedAmount)
                );
                $history .= " (" . $this->i18n->getDashboard('until now') . ")";
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $this->moneyService->centsToFloat($canceledAmount);

                $history .= " ({$this->i18n->getDashboard('Partial Payment')}";
                $history .= ". " .
                    $this->i18n->getDashboard(
                        'Canceled amount: %.2f',
                        $amountCanceledInCurrency
                    ) . ')';
            }

            return $history;
        }

        if ($charge->getStatus()->equals(ChargeStatus::failed())) {
            return $this->i18n->getDashboard('Charge failed.');
        }

        if ($charge->getStatus()->equals(ChargeStatus::chargedback())) {
            return $this->i18n->getDashboard('Charge chargedback.');
        }

        $amountInCurrency = $this->moneyService->centsToFloat($charge->getRefundedAmount());
        $history = $this->i18n->getDashboard(
            'Charge canceled.'
        );

        $history .= ' ' . $this->i18n->getDashboard('Refunded amount: %.2f', $amountInCurrency);
        $history .= " (" . $this->i18n->getDashboard('until now') . ")";

        return $history;
    }

    /**
     * @param ChargeInterface $charge
     * @return string
     * @throws InvalidParamException
     */
    public function prepareReturnMessage(ChargeInterface $charge)
    {
        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $this->moneyService->centsToFloat($charge->getPaidAmount());

            $returnMessage = "Amount Paid: {$amountInCurrency}";

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $returnMessage .= ". Extra value paid: " .
                    $this->moneyService->centsToFloat($extraValue);
            }

            if ($extraValue < 0) {
                $returnMessage .= ". Remaining Amount: " .
                    $this->moneyService->centsToFloat(abs($extraValue));
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $this->moneyService->centsToFloat($canceledAmount);

                $returnMessage .= ". Amount Canceled: {$amountCanceledInCurrency}";
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $returnMessage = "Refunded amount unil now: " .
                    $this->moneyService->centsToFloat($refundedAmount);
            }

            return $returnMessage;
        }

        if ($charge->getStatus()->equals(ChargeStatus::failed())) {
            return "Charge failed at Pagarme";
        }

        $amountInCurrency = $this->moneyService->centsToFloat($charge->getRefundedAmount());

        return "Charge canceled. Refunded amount: {$amountInCurrency}";
    }

    /**
     * @param $orderHandler
     * @return string
     */
    private function treatOrderMessage($orderHandler)
    {
        if ($orderHandler) {
            return "";
        }

        return $orderHandler;
    }

    /**
     * @param string $message
     * @return string
     */
    private function addHistoryComment($message)
    {
        $order = $this->order;
        $history = $this->i18n->getDashboard($message);
        $order->getPlatformOrder()->addHistoryComment($history, false);
        $order->getPlatformOrder()->save();
        return $history;
    }
}
