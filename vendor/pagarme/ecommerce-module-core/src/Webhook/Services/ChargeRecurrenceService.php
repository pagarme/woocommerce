<?php

namespace Pagarme\Core\Webhook\Services;

use Exception;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Kernel\Interfaces\ChargeInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Kernel\ValueObjects\TransactionStatus;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Core\Recurrence\Services\InvoiceService;
use Pagarme\Core\Webhook\Aggregates\Webhook;

final class ChargeRecurrenceService extends AbstractHandlerService
{
    /**
     * @var LocalizationService
     */
    private $i18n;

    /**
     * @var MoneyService
     */
    private $moneyService;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ChargeRepository
     */
    private $chargeRepository;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * ChargeRecurrenceService constructor.
     */
    public function __construct()
    {
        $this->i18n = new LocalizationService();
        $this->moneyService = new MoneyService();
        $this->orderFactory = new OrderFactory();
        $this->chargeRepository = new ChargeRepository();
        $this->orderService = new OrderService();
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    public function handlePaid(Webhook $webhook)
    {
        $orderFactory = new OrderFactory();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $chargeRepository->findByPagarmeId($charge->getPagarmeId());
        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($charge->getLastTransaction());
            $charge = $outdatedCharge;
        }

        $paidAmount = $transaction->getPaidAmount();
        $platformOrder = $this->order->getPlatformOrder();

        if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
            $charge->pay($paidAmount);
        }

        if ($charge->getPaidAmount() == 0) {
            $charge->setPaidAmount($paidAmount);
        }
        if ($charge->getSubscriptionId() === null) {
            $charge->setSubscriptionId($this->order->getSubscriptionId()->getValue());
        }
        $chargeRepository->save($charge);

        $this->order->setCurrentCharge($charge);

        $history = $this->prepareHistoryComment($charge);
        $platformOrder->addHistoryComment($history);

        $platformOrderStatus = ucfirst(ChargeStatus::paid()->getStatus());
        $realOrder = $orderFactory->createFromSubscriptionData(
            $this->order,
            $platformOrderStatus
        );
        $realOrder->addCharge($charge);

        $orderService->syncPlatformWith($realOrder, false);

        $platformOrder->save();

        $returnMessage = $this->prepareReturnMessage($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePartialCanceled(Webhook $webhook)
    {
        $orderFactory = new OrderFactory();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        $order = $this->order;

        /**
         *
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();
        /**
         *
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        $cancelAmount = $charge->getCanceledAmount();
        if ($transaction !== null) {
            $charge->addTransaction($transaction);
            $cancelAmount = $transaction->getAmount();
        }

        $charge->cancel($cancelAmount);
        $chargeRepository->save($charge);

        $this->order->setCurrentCharge($charge);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history);

        $platformOrderStatus = ucfirst($order->getPlatformOrder()->getPlatformOrder()->getStatus());
        $realOrder = $orderFactory->createFromSubscriptionData(
            $order,
            $platformOrderStatus
        );
        $realOrder->addCharge($charge);

        $orderService->syncPlatformWith($realOrder, false);

        $returnMessage = $this->prepareReturnMessage($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    protected function handleOverpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    protected function handleUnderpaid(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    protected function handleRefunded(Webhook $webhook)
    {
        $orderFactory = new OrderFactory();
        $chargeRepository = new ChargeRepository();
        $orderService = new OrderService();

        $order = $this->order;
        if ($order->getStatus()->equals(OrderStatus::canceled())) {
            $result = [
                "message" => "It is not possible to refund a charge of an order that was canceled.",
                "code" => 200
            ];
            return $result;
        }

        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        $transaction = $charge->getLastTransaction();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $chargeRepository->findByPagarmeId(
            $charge->getPagarmeId()
        );

        if ($outdatedCharge !== null) {
            $charge = $outdatedCharge;
        }

        $cancelAmount = $charge->getAmount();
        if ($transaction !== null) {
            $charge->addTransaction($transaction);
            $cancelAmount = $transaction->getAmount();
        }

        $charge->cancel($cancelAmount);
        $chargeRepository->save($charge);

        $this->order->setCurrentCharge($charge);

        $history = $this->prepareHistoryComment($charge);
        $order->getPlatformOrder()->addHistoryComment($history);

        $platformOrderStatus = ucfirst($order->getPlatformOrder()->getPlatformOrder()->getStatus());
        $realOrder = $orderFactory->createFromSubscriptionData(
            $order,
            $platformOrderStatus
        );
        $realOrder->addCharge($charge);

        $orderService->syncPlatformWith($realOrder, false);

        $returnMessage = $this->prepareReturnMessage($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    /**
     * @param Webhook $webhook
     * @return array
     */
    protected function handleChargedback(Webhook $webhook)
    {

        $order = $this->order;
        $invoiceService = new InvoiceService();
        $subscriptionRepository = new SubscriptionRepository();
        $i18n = new LocalizationService();

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
            $outdatedCharge->addTransaction($transaction);
        }


        $charge->cancel();
        $order->updateCharge($charge);
        $order->applyOrderStatusFromCharges();

        $charge->failed();
        $invoiceService->setChargedbackStatus($charge);

        $history = $i18n->getDashboard('Subscription canceled');
        $order->getPlatformOrder()->addHistoryComment($history);

        $subscriptionRepository->save($order);

        return [
            "message" => 'Subscription cancel registered',
            "code" => 200
        ];

    }

    //@todo handleProcessing
    protected function handleProcessing_TODO(Webhook $webhook)
    {
        //@todo
        //In simulator, Occurs with values between 1.050,01 and 1.051,71, auth
        // only and auth and capture.
        //AcquirerMessage = Simulator|Ocorreu um timeout (transação simulada)
    }

    //@todo handlePaymentFailed
    protected function handlePaymentFailed_TODO(Webhook $webhook)
    {
        //@todo
        //In simulator, Occurs with values between 1.051,72 and 1.262,06, auth
        // only and auth and capture.
        //AcquirerMessage = Simulator|Transação de simulação negada por falta de crédito, utilizado para realizar simulação de autorização parcial
        //ocurrs in the next case of the simulator too.

        //When this webhook is received, the order wasn't created on magento, so
        // no further action is needed.
    }

    /**
     * @param Charge $charge
     * @return string
     * @throws InvalidParamException
     */
    private function prepareHistoryCommentCreated(Charge $charge)
    {
        $history = $this->i18n->getDashboard(
            'Charge created: %.2f',
            $this->moneyService->centsToFloat($charge->getAmount())
        );

        if ($charge->getSubscriptionId() != null) {
            $history = $this->i18n->getDashboard(
                'Subscription invoice created: %.2f',
                $this->moneyService->centsToFloat($charge->getAmount())
            );
        }

        if ($charge->getBoletoUrl() != null) {
            $boletoUrl = $charge->getBoletoUrl();
            $text = $this->i18n->getDashboard('Url boleto');

            $history .= "<br><a href=\"{$boletoUrl}\">{$text}</a>";
        }

        return $history;
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleCreated(Webhook $webhook)
    {
        /**
         * @var Charge $charge
         */
        $charge = $webhook->getEntity();

        /**
         * @var Charge $outdatedCharge
         */
        $outdatedCharge = $this->chargeRepository->findByPagarmeId($charge->getPagarmeId());
        if ($outdatedCharge !== null) {
            $outdatedCharge->addTransaction($charge->getLastTransaction());
            $charge = $outdatedCharge;
        }

        $platformOrder = $this->order->getPlatformOrder();

        if ($charge->getSubscriptionId() === null) {
            $charge->setSubscriptionId(
                $charge->getInvoice()->getSubscriptionId()->getValue()
            );
        }

        $this->chargeRepository->save($charge);

        $this->order->setCurrentCharge($charge);

        $realOrder =  $this->orderFactory->createFromSubscriptionData(
            $this->order,
            $this->order->getPlatformOrder()->getStatus()
        );

        $realOrder->addCharge($charge);
        $this->orderService->syncPlatformWith($realOrder, false);

        $sender = $this->sendBoletoEmail($charge, $realOrder->getCode(), $platformOrder);

        $history = $this->prepareHistoryCommentCreated($charge);
        $platformOrder->addHistoryComment($history, $sender);

        $returnMessage = $this->prepareReturnMessageCreated($charge);
        $result = [
            "message" => $returnMessage,
            "code" => 200
        ];

        return $result;
    }

    /**
     * @param Charge $charge
     * @return string
     */
    private function prepareReturnMessageCreated(Charge $charge)
    {
        $message = 'Charge created';
        if ($charge->getBoletoUrl() != null) {
            $message .= ' url boleto: ' . $charge->getBoletoUrl();
        }

        return $message;
    }

    /**
     * @param Charge $charge
     * @param string $codeOrder
     * @param PlatformOrderInterface $platformOrder
     * @return bool
     */
    private function sendBoletoEmail(
        Charge $charge,
        $codeOrder,
        PlatformOrderInterface $platformOrder
    ) {
        if ($charge->getBoletoUrl() != null) {
            $i18n = new LocalizationService();
            $messageUrlBoletoEmail = $i18n->getDashboard(
                "Charge for your order: %s \n %s",
                $codeOrder,
                $charge->getBoletoUrl()
            );

            return $platformOrder->sendEmail($messageUrlBoletoEmail);
        }

        return false;
    }

    //@todo handlePending
    protected function handlePending_TODO(Webhook $webhook)
    {
        //@todo, but not with priority,
    }

    /**
     * @param Webhook $webhook
     * @throws InvalidParamException
     * @throws Exception
     */
    public function loadOrder(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $apiService = new ApiService();

        /** @var Charge $charge */
        $charge = $webhook->getEntity();

        $subscriptionId = $charge->getInvoice()->getSubscriptionId();
        $subscription = $apiService->getSubscription(new SubscriptionId($subscriptionId));

        if (is_null($subscription)) {
            throw new Exception('Code não foi encontrado', 400);
        }

        $charge->setCycleStart($subscription->getCurrentCycle()->getCycleStart());
        $charge->setCycleEnd($subscription->getCurrentCycle()->getCycleEnd());

        $orderCode = $subscription->getPlatformOrder()->getCode();
        $order = $subscriptionRepository->findByCode($orderCode);
        if ($order === null) {
            throw new NotFoundException("Order #{$orderCode} not found.");
        }

        $this->order = $order;
    }

    /**
     * @param ChargeInterface $charge
     * @return mixed|string
     * @throws InvalidParamException
     */
    public function prepareHistoryComment(ChargeInterface $charge)
    {
        $i18n = new LocalizationService();
        $moneyService = new MoneyService();

        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $history = $i18n->getDashboard(
                'Payment received: %.2f',
                $amountInCurrency
            );

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $history .= ". " . $i18n->getDashboard(
                    "Extra amount paid: %.2f",
                    $moneyService->centsToFloat($extraValue)
                );
            }

            if ($extraValue < 0) {
                $history .= ". " . $i18n->getDashboard(
                    "Remaining amount: %.2f",
                    $moneyService->centsToFloat(abs($extraValue))
                );
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $history = $i18n->getDashboard(
                    'Refunded amount: %.2f',
                    $moneyService->centsToFloat($refundedAmount)
                );
                $history .= " (" . $i18n->getDashboard('until now') . ")";
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

                $history .= " ({$i18n->getDashboard('Partial Payment')}";
                $history .= ". " .
                    $i18n->getDashboard(
                        'Canceled amount: %.2f',
                        $amountCanceledInCurrency
                    ) . ')';
            }

            return $history;
        }

        $amountInCurrency = $moneyService->centsToFloat($charge->getRefundedAmount());
        $history = $i18n->getDashboard(
            'Charge canceled.'
        );

        $history .= ' ' . $i18n->getDashboard(
            'Refunded amount: %.2f',
            $amountInCurrency
        );

        $history .= " (" . $i18n->getDashboard('until now') . ")";

        return $history;
    }

    /**
     * @param ChargeInterface $charge
     * @return string
     * @throws InvalidParamException
     */
    public function prepareReturnMessage(ChargeInterface $charge)
    {
        $moneyService = new MoneyService();

        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $moneyService->centsToFloat($charge->getPaidAmount());

            $returnMessage = "Amount Paid: $amountInCurrency";

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $returnMessage .= ". Extra value paid: " .
                    $moneyService->centsToFloat($extraValue);
            }

            if ($extraValue < 0) {
                $returnMessage .= ". Remaining Amount: " .
                    $moneyService->centsToFloat(abs($extraValue));
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $moneyService->centsToFloat($canceledAmount);

                $returnMessage .= ". Amount Canceled: $amountCanceledInCurrency";
            }


            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $returnMessage = "Refunded amount unil now: " .
                    $moneyService->centsToFloat($refundedAmount);
            }

            return $returnMessage;
        }

        $amountInCurrency = $moneyService->centsToFloat($charge->getRefundedAmount());
        $returnMessage = "Charge canceled. Refunded amount: $amountInCurrency";

        return $returnMessage;
    }
}
