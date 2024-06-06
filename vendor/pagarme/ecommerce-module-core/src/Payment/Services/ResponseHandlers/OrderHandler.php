<?php

namespace Pagarme\Core\Payment\Services\ResponseHandlers;

use Pagarme\Core\Kernel\Abstractions\AbstractDataService;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
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
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Payment\Services\CardService;
use Pagarme\Core\Payment\Services\CustomerService;

/** For possible order states, see https://docs.mundipagg.com/v1/reference#pedidos */
final class OrderHandler extends AbstractResponseHandler
{
    /**
     * @param Order $createdOrder
     * @return mixed
     */
    public function handle($createdOrder, PaymentOrder $paymentOrder = null)
    {
        $orderStatus = ucfirst($createdOrder->getStatus()->getStatus());
        $statusHandler = 'handleOrderStatus' . $orderStatus;

        $this->logService->orderInfo(
            $createdOrder->getCode(),
            "Handling order status: $orderStatus"
        );

        $orderRepository = new OrderRepository();
        $orderRepository->save($createdOrder);

        $customerService = new CustomerService();
        if (!empty($createdOrder->getCustomer())) {
            $customerService->saveCustomer(
                $createdOrder->getCustomer()
            );
        }

        return $this->$statusHandler($createdOrder);
    }

    private function handleOrderStatusProcessing(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        $i18n = new LocalizationService();

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $platformOrder->getStatus()
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order waiting for online retries at Pagarme.' .
                    ' PagarmeId: ' . $order->getPagarmeId()->getValue()
            ),
            $sender
        );

        return $this->handleOrderStatusPending($order);
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function handleOrderStatusPending(Order $order)
    {
        $this->createAuthorizationTransaction($order);

        $order->setStatus(OrderStatus::pending());
        $platformOrder = $order->getPlatformOrder();

        $i18n = new LocalizationService();

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $statusOrderLabel = $platformOrder->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $platformOrder->addAdditionalInformation($order->getCharges());

        $platformOrder->addHistoryComment(
            $i18n->getDashboard(
                'Order pending at Pagarme. Id: %s',
                $order->getPagarmeId()->getValue()
            ),
            $sender
        );

        return true;
    }

    /**
     * @param Order $order
     * @return bool|string|null
     */
    private function handleOrderStatusPaid(Order $order)
    {
        $invoiceService = new InvoiceService();
        $cardService = new CardService();

        $cantCreateReason = $invoiceService->getInvoiceCantBeCreatedReason($order);
        $invoice = $invoiceService->createInvoiceFor($order);
        if ($invoice !== null) {
            // create payment service to complete payment
            $this->completePayment($order, $invoice);

            $cardService->saveCards($order);

            return true;
        }
        return $cantCreateReason;
    }

    /**
     * @param Order $order
     * @param $invoice
     */
    private function completePayment(Order $order, $invoice)
    {
        $invoice->setState(InvoiceState::paid());
        $invoice->save();
        $platformOrder = $order->getPlatformOrder();

        $this->createCaptureTransaction($order);

        $order->setStatus(OrderStatus::processing());
        //@todo maybe an Order Aggregate should have a State too.
        $platformOrder->setState(OrderState::processing());

        $i18n = new LocalizationService();

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $statusOrderLabel = $platformOrder->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $platformOrder->addAdditionalInformation($order->getCharges());

        $platformOrder->addHistoryComment(
            $i18n->getDashboard('Order paid.') .
                ' PagarmeId: ' . $order->getPagarmeId()->getValue(),
            $sender
        );
    }

    private function createCaptureTransaction(Order $order)
    {
        $dataServiceClass =
            MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        $this->logService->orderInfo(
            $order->getCode(),
            "Creating Capture Transaction..."
        );

        /**
         *
         * @var AbstractDataService $dataService
         */
        $dataService = new $dataServiceClass();
        $dataService->createCaptureTransaction($order);

        $this->logService->orderInfo(
            $order->getCode(),
            "Capture Transaction created."
        );
    }

    private function createAuthorizationTransaction(Order $order)
    {
        $dataServiceClass =
            MPSetup::get(MPSetup::CONCRETE_DATA_SERVICE);

        $this->logService->orderInfo(
            $order->getCode(),
            "Creating Authorization Transaction..."
        );

        /**
         *
         * @var AbstractDataService $dataService
         */
        $dataService = new $dataServiceClass();
        $dataService->createAuthorizationTransaction($order);

        $this->logService->orderInfo(
            $order->getCode(),
            "Authorization Transaction created."
        );
    }

    private function handleOrderStatusCanceled(Order $order)
    {
        return $this->handleOrderStatusFailed($order);
    }

    private function handleOrderStatusFailed(Order $order)
    {
        $charges = $order->getCharges();

        $acquirerMessages = '';
        $historyData = [];
        foreach ($charges as $charge) {
            $lastTransaction = $charge->getLastTransaction();
            $acquirerMessages .=
                "{$charge->getPagarmeId()->getValue()} => '{$lastTransaction->getAcquirerMessage()}', ";
            $historyData[$charge->getPagarmeId()->getValue()] = $lastTransaction->getAcquirerMessage();
        }
        $acquirerMessages = rtrim($acquirerMessages, ', ');

        $this->logService->orderInfo(
            $order->getCode(),
            "Order creation Failed: $acquirerMessages"
        );

        $i18n = new LocalizationService();
        $historyComment = $i18n->getDashboard('Order payment failed');
        $historyComment .= ' (' . $order->getPagarmeId()->getValue() . ') : ';

        foreach ($historyData as $chargeId => $acquirerMessage) {
            $historyComment .= "$chargeId => $acquirerMessage; ";
        }
        $historyComment = rtrim($historyComment, '; ');
        $order->getPlatformOrder()->addHistoryComment(
            $historyComment
        );

        $order->setStatus(OrderStatus::failed());
        $order->getPlatformOrder()->setState(OrderState::canceled());
        $order->getPlatformOrder()->save();

        $orderRepository = new OrderRepository();
        $orderRepository->save($order);

        $orderService = new OrderService();
        $orderService->syncPlatformWith($order);

        $platformOrder = $order->getPlatformOrder();

        $statusOrderLabel = $platformOrder->getStatusLabel(
            $order->getStatus()
        );

        $messageComplementEmail = $i18n->getDashboard(
            'New order status: %s',
            $statusOrderLabel
        );

        $sender = $platformOrder->sendEmail($messageComplementEmail);

        $order->getPlatformOrder()->addHistoryComment(
            $i18n->getDashboard('Order payment failed.'),
            $sender
        );

        return "One or more charges weren't authorized. Please try again.";
    }
}
