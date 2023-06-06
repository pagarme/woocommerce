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
 * Class TwoCards
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class TwoCards extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/2-cards';

    /**
     * @return array|mixed|null
     */
    public function getCharges()
    {
        if ($charges = $this->getData('charges')) {
            return $charges;
        }
        if ($response = $this->getResponseData()) {
            if (property_exists($response, 'charges')) {
                return $response->charges;
            }
        }
        return [null, null];
    }
}
