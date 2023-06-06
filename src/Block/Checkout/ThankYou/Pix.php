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
 * Class Pix
 * @package Woocommerce\Pagarme\Block\Checkout\ThankYou
 */
class Pix extends ThankYou
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thankyou/pix';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/payment/pix'];

    /**
     * @return string|null
     */
    public function getQrCodeUrl()
    {
        try {
            if ($response = $this->getResponseData()) {
                $charges = $response->charges;
                $charge = array_shift($charges);
                $transaction = array_shift($charge->transactions);
                return $transaction->postData->qr_code_url;
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * @return string|null
     */
    public function getRawQrCode()
    {
        try {
            if ($response = $this->getResponseData()) {
                $charges = $response->charges;
                $charge = array_shift($charges);
                $transaction = array_shift($charge->transactions);
                return $transaction->postData->qr_code;
            }
        } catch (\Exception $e) {}
        return null;
    }

    /**
     * @return array
     */
    public function getInstructions()
    {
        return [
            '<span>1.</span> Point your phone at this screen to capture the code.',
            '<span>2.</span> Open your payments app.',
            '<span>3.</span> Confirm the information and complete the payment on the app.',
            '<span>4.</span> We will send you a purchase confirmation.'
        ];
    }
}
