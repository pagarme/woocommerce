<?php

namespace Woocommerce\Pagarme\Block\NewCheckout\Payments;

use Woocommerce\Pagarme\Core;

class CreditCard extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType
{
    protected $name = 'woo-pagarme-payments-credit_card';

    /**
	 * Initializes the payment method type.
	 */
    public function initialize() {
        $this->settings = get_option( 'woocommerce_woo-pagarme-payments-credit_card_settings', [] );
    }


    public function get_payment_method_script_handles()
    {
        wp_register_script('pagarme_payments_blocks_integration', $this->jsUrl('creditCard'), [], false, true);
        
        return [
            'pagarme_payments_blocks_integration'
        ];
    }

    public function jsUrl($jsFileName)
    {
        return Core::plugins_url('build/' . $jsFileName . '.js');
    }

    public function get_payment_method_data()
    {
        return [
            'testConfig' => 'testValue'
        ];
    }
}