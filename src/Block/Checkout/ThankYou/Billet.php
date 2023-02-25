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
 * Class Billet
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class Billet extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/billet';

    /**
     * @return string|null
     */
    public function getBilletUrl()
    {
        if ($response = $this->getResponseData()) {
            $charges = $response->charges;
            $charge = array_shift($charges);
            $transaction = array_shift($charge->transactions);
            return $transaction->boletoUrl;
        }
        return null;
    }
}
