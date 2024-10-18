<?php

namespace Woocommerce\Pagarme\Model;

class SubscriptionMeta
{
    protected const PAYMENT_DATA_KEY = '_pagarme_payment_subscription';

    /**
     * @param int $orderId
     * @param \Pagarme\Core\Kernel\Aggregates\Order $response
     * @return void
     */
    protected function saveCardInSubscriptionUsingOrderResponse($response)
    {
        $platformOrderId = $response->getPlatformOrder()->getId();

    }
    
    
}