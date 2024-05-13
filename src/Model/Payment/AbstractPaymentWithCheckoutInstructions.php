<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

use Woocommerce\Pagarme\Core;

defined( 'ABSPATH' ) || exit;


abstract class AbstractPaymentWithCheckoutInstructions extends AbstractPayment
{
    /** @var string */
    const PAYMENT_CODE = '';

    /** @var string */
    const IMAGE_FILE_NAME = '';

    public function getImage()
    {
        $imagePath = sprintf('assets/images/%s', static::IMAGE_FILE_NAME);
        return esc_url(Core::plugins_url($imagePath));
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        $message = static::getDefaultCheckoutInstructions();

        $settings = $this->getSettings();
        $checkoutInstructionsKey = static::getCheckoutInstructionsKey();
        if (!empty($settings[$checkoutInstructionsKey])) {
            $message = $settings[$checkoutInstructionsKey];
        
        }
        return $message;
    }

    public static function getCheckoutInstructionsKey()
    {
        return sprintf('%s_checkout_instructions', static::PAYMENT_CODE);
    }

    public static function getCheckoutInstructionsTitle()
    {
        return __('Checkout instructions', 'woo-pagarme-payments');
    }

    public static function getCheckoutInstructionsDescription()
    {
        return __('Instructions text that appears in checkout.', 'woo-pagarme-payments');
    }

    /**
     * @return string
     */
    public static function getDefaultCheckoutInstructions()
    {
        return '';
    }
}