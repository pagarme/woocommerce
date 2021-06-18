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
use Pagarme\Core\Recurrence\Aggregates\Invoice;
use Pagarme\Core\Recurrence\Aggregates\Subscription;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;
use Pagarme\Core\Recurrence\Services\SubscriptionItemService;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;

class InvoiceRecurrenceService extends AbstractHandlerService
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
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    public function handlePaid(Webhook $webhook)
    {
        $config = AbstractModuleCoreSetup::getModuleConfiguration();
        $isDecreaseStock = $config->getRecurrenceConfig()->isDecreaseStock();

        /**
         * @var Subscription $subscription
         */
        $subscription = $this->order;

        if (!$this->isFirstCycle() && $isDecreaseStock) {
            $subscriptionItemService = new SubscriptionItemService();
            $subscriptionItemService->updateStock($subscription->getItems());
        }

        return [
            "message" => "Invoice Paid",
            "code" => 200
        ];
    }

    /**
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handlePartialCanceled_TODO(Webhook $webhook)
    {
        //@todo
    }

    protected function handleOverpaid_TODO(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    protected function handleUnderpaid_TODO(Webhook $webhook)
    {
        return $this->handlePaid($webhook);
    }

    protected function handleRefunded_TODO(Webhook $webhook)
    {
        //@todo
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
     * @param Webhook $webhook
     * @return array
     * @throws InvalidParamException
     */
    protected function handleCreated_TODO(Webhook $webhook)
    {
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

        /** @var Invoice $invoice */
        $invoice = $webhook->getEntity();

        $subscription = $apiService->getSubscription(
            new SubscriptionId($invoice->getSubscriptionId())
        );

        if (is_null($subscription)) {
            throw new Exception('Code não foi encontrado', 400);
        }

        $orderCode = $subscription->getPlatformOrder()->getCode();
        $order = $subscriptionRepository->findByCode($orderCode);
        if ($order === null) {
            throw new NotFoundException("Order #{$orderCode} not found.");
        }

        $order->setCurrentCycle($invoice->getCycle());

        $this->order = $order;
    }

    public function isFirstCycle()
    {
        $currentCycle = $this->order->getCurrentCycle();
        if($currentCycle->getCycle() == 1) {
            return true;
        }

        return false;
    }
}
