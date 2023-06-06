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
 * Class Voucher
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class Voucher extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/voucher';

    /**
     * @return string|null
     */
    public function getChargeStatus()
    {
        try {
            if ($response = $this->getResponseData()) {
                $charges = $response->charges;
                $charge = array_shift($charges);
                return $charge->status;
            }
        } catch (\Exception $e) {}
        return null;
    }
}
