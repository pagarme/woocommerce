<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\Payment;

use Woocommerce\Pagarme\Block\Checkout\Gateway;
use Woocommerce\Pagarme\Model\Subscription;

defined( 'ABSPATH' ) || exit;

/**
 * Class CreditCard
 * @package Woocommerce\Pagarme\Block\Checkout\Payment
 */
class CreditCard extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/payment/credit-card';
    protected $scripts = ['checkout/model/payment/googlepay', 'https://pay.google.com/gp/p/js/pay.js'];
    /**
     * @return int
     */
    public function getQtyCards()
    {
        return 1;
    }

    public function hasSubscriptionProductInCart()
    {
        return Subscription::hasSubscriptionProductInCart();
    }
}
