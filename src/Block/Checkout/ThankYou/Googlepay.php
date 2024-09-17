<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\ThankYou;

use Woocommerce\Pagarme\Block\Checkout\ThankYou;

defined( 'ABSPATH' ) || exit;

/**
 * Class Googlepay
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class Googlepay extends CreditCard
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/credit-card';
}
