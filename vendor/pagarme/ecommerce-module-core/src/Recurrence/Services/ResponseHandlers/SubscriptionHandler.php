<?php

namespace Pagarme\Core\Recurrence\Services\ResponseHandlers;

use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Services\CardService;
use Pagarme\Core\Payment\Services\CustomerService;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\Services\ResponseHandlers\AbstractResponseHandler;
use Pagarme\Core\Kernel\Abstractions\AbstractDataService;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Services\InvoiceService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\InvoiceState;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use Pagarme\Core\Payment\Aggregates\Order as PaymentOrder;
use Pagarme\Core\Payment\Factories\SavedCardFactory;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Core\Recurrence\Aggregates\Subscription;
use Pagarme\Core\Recurrence\Factories\SubscriptionFactory;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;

final class SubscriptionHandler extends AbstractResponseHandler
{
    private $order;

    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle(Subscription $subscription)
    {
        $status = $this->getSubscriptionStatusFromCharge($subscription);
        $statusHandler = 'handleSubscriptionStatus' . $status;

        $platformOrderStatus = $status;

        $this->logService->orderInfo(
            $subscription->getCode(),
            "Handling subscription status: " . $status
        );
        $charge = $subscription->getCurrentCharge();
        $chargeRepository = new ChargeRepository();
        $chargeRepository->save($charge);

        $orderFactory = new OrderFactory();
        $this->order =
            $orderFactory->createFromSubscriptionData(
                $subscription,
                $platformOrderStatus
            );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $customerService = new CustomerService();
        $customerService->saveCustomer($subscription->getCustomer());

        return $this->$statusHandler($subscription);
    }

    private function handleSubscriptionStatusPaid(Subscription $subscription)
    {
        $invoiceService = new InvoiceService();
        $cardService = new CardService();

        $order = $this->order;

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $platformInvoice = $invoiceService->createInvoiceFor($order);
        if ($platformInvoice !== null) {
            // create payment service to complete payment
            $this->completePayment($order, $subscription, $platformInvoice);

            $cardService->saveCards($order);

            return true;
        }
        return $cantCreateReason;
    }

    private function handleSubscriptionStatusPending(Subscription $subscription)
    {
        $order = $this->order;

        $order->setStatus(OrderStatus::pending());
        $platformOrder = $subscription->getPlatformOrder();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Subscription created at Pagarme. Id: %s',
                $subscription->getPagarmeId()->getValue()
            )
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
        return true;
    }

    private function handleSubscriptionStatusFailed(Subscription $subscription)
    {
        $order = $this->order;

        $order->setStatus(OrderStatus::canceled());

        $platformOrder = $subscription->getPlatformOrder();
        $platformOrder->setState(OrderState::canceled());
        $platformOrder->save();

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Subscription payment failed at Pagarme. Id: %s',
                $subscription->getPagarmeId()->getValue()
            )
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription canceled.')
        );

        return true;
    }

    private function handleSubscriptionStatus(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();
        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order waiting for online retries at Pagarme.' .
                ' PagarmeId: ' . $order->getPagarmeId()->getValue()
            )
        );

        return $this->handleOrderStatusPending($order);
    }

    /**
     * @param Order $order
     * @param $invoice
     */
    private function completePayment(Order $order, Subscription $subscription, $invoice)
    {
        $invoice->setState(InvoiceState::paid());
        $invoice->save();
        $platformOrder = $order->getPlatformOrder();

        /**
         * @todo Check if we should create transactions
         */
        //$this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        //@todo maybe an Order Aggregate should have a State too.
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();
        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Subscription invoice paid.') . '<br>' .
            ' PagarmeId: ' . $subscription->getPagarmeId()->getValue() . '<br>' .
            $i18n->getDashboard('Invoice') . ': ' .
            $subscription->getInvoice()->getPagarmeId()->getValue()
        );

        $subscriptionRepository = new SubscriptionRepository();
        $subscriptionRepository->save($subscription);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);
    }

    private function getSubscriptionStatusFromCharge(Subscription $subscription)
    {
        $charge = $subscription->getCurrentCharge();
        return ucfirst($charge->getStatus()->getStatus());
    }
}