<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Abstractions\AbstractDataService;
use Pagarme\Core\Kernel\Aggregates\Order;

class WoocommerceDataService extends AbstractDataService
{
    public function updateAcquirerData(Order $order)
    {
        // Legacy, not necessary to be implemented on Woocommerce
    }

    private function getChargeBaseKey($transactionAuth, $charge)
    {
        // Legacy, not necessary to be implemented on Woocommerce
    }

    private function OLDcreateCaptureTransaction($order, $transactionAuth, $additionalInformation)
    {
        // Legacy, not necessary to be implemented on Woocommerce
    }

    public function createCaptureTransaction(Order $order)
    {
        $this->createTransaction($order, parent::TRANSACTION_TYPE_CAPTURE);
    }

    public function createAuthorizationTransaction(Order $order)
    {
        $this->createTransaction($order, parent::TRANSACTION_TYPE_AUTHORIZATION);
    }

    private function createTransaction(Order $order, $transactionType)
    {
        // Not implemented on Woocommerce because there is no transaction concept/entity
    }
}
