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
}
