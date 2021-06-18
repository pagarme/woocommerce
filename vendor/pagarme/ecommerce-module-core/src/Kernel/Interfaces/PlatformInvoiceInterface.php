<?php

namespace Pagarme\Core\Kernel\Interfaces;

use Pagarme\Core\Kernel\ValueObjects\InvoiceState;

interface PlatformInvoiceInterface
{
    public function save();
    public function setState(InvoiceState $state);
    public function loadByIncrementId($incrementId);
    public function getIncrementId();
    public function prepareFor(PlatformOrderInterface $order);
    public function createFor(PlatformOrderInterface $order);
    public function getPlatformInvoice();
    public function canRefund();
    public function isCanceled();

    /**
     * @since 1.7.2
     */
    public function addComment($comment);

}