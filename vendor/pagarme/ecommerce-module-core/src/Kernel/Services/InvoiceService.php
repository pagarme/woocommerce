<?php

namespace Pagarme\Core\Kernel\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Interfaces\PlatformCreditmemoInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformInvoiceInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\ValueObjects\InvoiceState;
use Pagarme\Core\Kernel\ValueObjects\OrderState;

class InvoiceService
{
    /**
     * @var LogService
     */
    private $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'Invoice',
            true
        );
    }
    /**
     *
     * @param  Order $platformOrder
     * @return null|PlatformInvoiceInterface
     */
    public function createInvoiceFor(Order $order)
    {
        $this->logService->info("Creating invoice.");
        $platformOrder = $order->getPlatformOrder();

        if (!$platformOrder->canInvoice()) {
            $this->logService->info("Can not create invoice.");
            return null;
        }

        $localizationService = new LocalizationService();
        $platformInvoiceDecoratorClass =
            MPSetup::get(
                MPSetup::CONCRETE_PLATFORM_INVOICE_DECORATOR_CLASS
            );
        /**
         *
         * @var PlatformInvoiceInterface $invoice
        */
        $invoice = new $platformInvoiceDecoratorClass();
        $invoice->createFor($platformOrder);

        $message = $localizationService->getDashboard(
            'Invoice created: #%s.',
            $invoice->getIncrementId()
        );
        $platformOrder->addHistoryComment($message);
        $platformOrder->save();

        return $invoice;
    }

    /**
     * This method is based on the original canInvoice method of Magento2.
     *
     * @see    Magento\Sales\Model\Order::canInvoice;
     * @param  Order $order
     * @return null|string
     */
    public function getInvoiceCantBeCreatedReason(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();

        if ($platformOrder->canUnhold()) {
            return 'canUnhold';
        }

        if ($platformOrder->isPaymentReview()) {
            return 'isPaymentReview';
        }

        $state = $platformOrder->getState();
        if ($platformOrder->isCanceled()) {
            return 'Order is Canceled';
        }

        if ($state->equals(OrderState::complete())) {
            return 'Order is Complete';
        }

        if ($state->equals(OrderState::closed())) {
            return 'Order is Closed';
        }

        /**
         *
 * @todo How can we do this conditions decoupled of the platform?

        if ($platformOrder->getActionFlag(self::ACTION_FLAG_INVOICE) === false) {
            return false;
        }

        foreach ($platformOrder->getAllItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }
        return false;
         */

        return 'No items to be invoiced or M2 Action Flag Invoice is false';
    }

    /**
     *
     * @param Order $order
     */
    public function cancelInvoicesFor(Order $order)
    {
        $cancelableInvoices = $this->getCancelableInvoicesFor($order);

        $invoice = null;
        foreach ($cancelableInvoices as $invoice) {
            $this->cancelInvoice($order->getPlatformOrder(), $invoice);
        }

        //todo all the returns of the concrete decorators should be in cents or in
        // classes defined on core.
        if ($order->getPlatformOrder()->getTotalRefunded() > 0) {
            $this->createCreditMemo($order->getPlatformOrder());
        }
    }

    private function createCreditMemo(
        PlatformOrderInterface $plaformOrder
    ) {
        $creditmemoClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_CREDITMEMO_DECORATOR_CLASS);
        /**
         *
         * @var PlatformCreditmemoInterface $creditmemo
        */
        $creditmemo = new $creditmemoClass();

        /**
         *
         * @fixme to refund an order we have to set the total refunded to 0, since
         *        the refund process will set the correct refund value by itself.
         *        So, setting this value is not a charge handling responsibility.
         */
        $plaformOrder->setBaseTotalRefunded(0);

        //refund order.
        $creditmemo->prepareFor($plaformOrder);
        $creditmemo->refund();
        $creditmemo->save();

        $i18n = new LocalizationService();

        $history = $i18n->getDashboard(
            'Creditmemo created: #%s.',
            $creditmemo->getIncrementId()
        );
        $plaformOrder->addHistoryComment($history);
    }

    private function cancelInvoice(
        PlatformOrderInterface $plaformOrder,
        PlatformInvoiceInterface $invoice
    ) {
        $i18n = new LocalizationService();

        $invoice->setState(InvoiceState::canceled());

        $history = $i18n->getDashboard(
            'Invoice canceled: #%s.',
            $invoice->getIncrementId()
        );

        $plaformOrder->addHistoryComment($history);

        $invoice->save();
    }

    /**
     *
     * @param  Order $order
     * @return PlatformInvoiceInterface[]
     */
    private function getCancelableInvoicesFor(Order $order)
    {
        $platformOrder = $order->getPlatformOrder();
        $invoiceCollection = $platformOrder->getInvoiceCollection();

        $cancelableInvoices = [];

        foreach ($invoiceCollection as $invoice) {
            if ($invoice->canRefund() && !$invoice->isCanceled()) {
                $cancelableInvoices[] = $invoice;
            }
        }

        return $cancelableInvoices;
    }
}