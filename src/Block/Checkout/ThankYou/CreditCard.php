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
 * Class CreditCard
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class CreditCard extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/credit-card';

    public function getCharge()
    {
        if ($charge = $this->getData('charge')) {
            return $charge;
        }
        if ($response = $this->getResponseData()) {
            if (property_exists($response, 'charges')) {
                return array_shift($response->charges);
            }
        }
        return null;
    }
}
