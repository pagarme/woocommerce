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
 * Class BilletAndCard
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class BilletAndCard extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/billet-and-card';

    /**
     * @return string|null
     */
    public function getCharges()
    {
        try {
            if ($response = $this->getResponseData()) {
                return $response->charges;
            }
        } catch (\Exception $e) {}
        return null;
    }

    public function getTransactionType($charge)
    {
        try {
            return $this->getTransacion($charge)->type;
        } catch (\Exception $e) {}
        return null;
    }

    public function getTransacion($charge)
    {
        try {
            return current($charge->transactions);
        } catch (\Exception $e) {}
        return null;
    }
}
