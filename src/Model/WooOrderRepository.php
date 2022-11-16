<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model;

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

use WC_Order;

/**
 * Class WooOrderRepository
 * @package Woocommerce\Pagarme\Model
 */
class WooOrderRepository
{
    /**
     * @param $orderId
     * @return WC_Order
     */
    public function getById($orderId)
    {
        return new WC_Order($orderId);
    }

    /**
     * @param WC_Order $order
     * @return int
     * @throws \Exception
     */
    public function save($order)
    {
        if ($order instanceof WC_Order) {
            return $order->save();
        }
        throw new \Exception(__('$order need be instance off WC_Order', 'woo-pagarme-payments'));
    }
}
