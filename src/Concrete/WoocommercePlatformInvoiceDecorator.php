<?php

namespace Woocommerce\Pagarme\Concrete;

use JsonSerializable;
use Pagarme\Core\Kernel\Abstractions\AbstractInvoiceDecorator;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\ValueObjects\InvoiceState;

class WoocommercePlatformInvoiceDecorator extends AbstractInvoiceDecorator implements
    JsonSerializable
{
    public function save()
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    public function loadByIncrementId($incrementId)
    {
        // TODO: Implement loadByIncrementId() method.
    }

    public function getIncrementId()
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    public function prepareFor(PlatformOrderInterface $order)
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    public function createFor(PlatformOrderInterface $order)
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    public function setState(InvoiceState $state)
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    public function canRefund()
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    public function isCanceled()
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    private function createInvoice($order)
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }

    protected function addMPComment($comment)
    {
        // Not necessary to be implemented on Woocommerce, there is no Invoice concept
    }
}
