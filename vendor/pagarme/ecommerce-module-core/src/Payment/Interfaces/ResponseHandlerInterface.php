<?php

namespace Pagarme\Core\Payment\Interfaces;

use Pagarme\Core\Payment\Aggregates\Order as PaymentOrder;

interface ResponseHandlerInterface
{
    public function handle($response, PaymentOrder $paymentOrder = null);
}