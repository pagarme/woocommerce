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
    * @var string
    */
    protected $scripts = 'checkout/model/payment/billet';

    public function getCharge()
    {
        if ($charge = $this->getData('charge')) {
            return $charge;
        }
        if ($response = $this->getResponseData()) {
            if (property_exists($response, 'charges')) {
                return current($response->charges);
            }
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getBilletUrl()
    {
        try {
            $charge = $this->getCharge();
            if ($charge) {
                $transaction = current($charge->transactions);
                return $transaction->boletoUrl;
            }
        } catch (\Exception $e) {}
        return null;
    }
}
