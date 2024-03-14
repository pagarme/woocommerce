<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Abstractions\AbstractCreditmemoDecorator;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;

class WoocommercePlatformCreditmemoDecorator extends AbstractCreditmemoDecorator
{
    public function save()
    {
        // Not necessary to be implemented on Woocommerce, there is no Creditmemo concept
    }

    public function getIncrementId()
    {
        // Not necessary to be implemented on Woocommerce, there is no Creditmemo concept
    }


    public function prepareFor(PlatformOrderInterface $order)
    {
        // Not necessary to be implemented on Woocommerce, there is no Creditmemo concept
    }

    public function refund()
    {
        // Not necessary to be implemented on Woocommerce, there is no Creditmemo concept
    }
}
