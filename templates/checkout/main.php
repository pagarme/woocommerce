<?php
if (!function_exists('add_action')) {
    exit(0);
}

global $woocommerce;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Model\Setting;

$tab_num     = Setting::get_instance()->get_active_tab();
$wc_api      = get_home_url(null, '/wc-api/' . Checkout::API_REQUEST);
$swal_data   = array(
    'title'        => __('Waiting...', 'woo-pagarme-payments'),
    'text'         => __('We are processing your request.', 'woo-pagarme-payments'),
    'text_default' => __('An error occurred while processing.', 'woo-pagarme-payments'),
    'text_success' => __('Your transaction has been processed successfully.', 'woo-pagarme-payments'),
);

?>

<div id="wcmp-checkout-errors">
    <ul class="woocommerce-error"></ul>
</div>

<div id="woo-pagarme-payment-methods"  <?php echo
    /** phpcs:ignore */
Utils::get_component('checkout-transparent'); ?> >
    <ul class="wc_payment_methods payment_methods methods">
        <?php

            Utils::get_template(
                'templates/checkout/credit-card-item',
                array('model' => $model)
            );

            Utils::get_template(
                'templates/checkout/2-cards-item',
                array('model' => $model)
            );

            Utils::get_template(
                'templates/checkout/billet-and-card-item',
                array('model' => $model)
            );

            Utils::get_template(
                'templates/checkout/billet-item',
                array('model' => $model)
            );

            Utils::get_template(
                'templates/checkout/pix-item',
                array('model' => $model)
            );
        ?>
    </ul>
</div>
