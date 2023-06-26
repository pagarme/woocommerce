<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order;

use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;
use Woocommerce\Pagarme\Model\Order;

defined('ABSPATH') || exit;

/**
 * Class EmailPaymentDetails
 * @package Woocommerce\Pagarme\Block\Order
 */
class EmailPaymentDetails extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/email-payment-details';

    /**
     * @param int|null $orderId
     * @return void
     */
    public function render(int $orderId = null)
    {
        $this->setOrderId($orderId)
            ->setOrder(new Order($orderId))->toHtml();
    }

    /**
     * @return mixed
     */
    public function getCharges()
    {
        if ($this->getOrder() && $this->getOrder() instanceof Order) {
            return $this->getOrder()->get_charges();
        }
        return null;
    }

    /**
     * @param \Woocommerce\Pagarme\Model\Order $order
     * @return bool
     */
    public function isPagarmePaymentMethod(Order $order)
    {
        if (property_exists($order, 'wc_order')) {
            $paymentMethod = $order->wc_order->get_payment_method();
            return $paymentMethod === AbstractGateway::PAGARME ||
                str_starts_with($paymentMethod, AbstractGateway::WC_PAYMENT_PAGARME);
        }
        return false;
    }
}
