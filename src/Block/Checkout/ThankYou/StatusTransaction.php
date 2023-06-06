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
 * Class StatusTransaction
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class StatusTransaction extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/status-transaction';

    /**
     * @return array|mixed|string|null
     */
    public function getMessage()
    {
        return $this->getData('message') ? __($this->getData('message'), 'woo-pagarme-payments') : __('Failed', 'woo-pagarme-payments');
    }
}
