<?php

namespace Woocommerce\Pagarme\Helper;

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;

class PaymentHelper
{
    public static function isPagarmePaymentMethod($orderId)
    {
        $order = new Order($orderId);
        if (property_exists($order, 'wc_order')) {
            $paymentMethod = $order->wc_order->get_payment_method();
            return $paymentMethod === AbstractGateway::PAGARME
                || 0 === strpos($paymentMethod, AbstractGateway::WC_PAYMENT_PAGARME);
        }
        return false;
    }
}
