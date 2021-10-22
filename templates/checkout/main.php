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

<ul class="wc_payment_methods payment_methods methods" <?php echo
                                                        /** phpcs:ignore */
                                                        Utils::get_component('checkout-transparent'); ?>>
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
<script>
    jQuery('input[name=method]').change(function(e) {
        e.stopPropagation();
        var li = e.target.closest('li');
        jQuery('.pagarme_methods').slideUp('slow');
        jQuery(li).find('.payment_box').slideDown('slow');
    });

    jQuery('input[data-element=enable-multicustomers]').click(function(e) {
        var input = jQuery(e.currentTarget);
        var method = input.is(':checked') ? 'slideDown' : 'slideUp';
        var target = '[data-ref="' + input.data('target') + '"]';
        jQuery(target)[method]();
    });

    jQuery(function($){
        $( '#card-number' ).mask( '0000000000000000000' );
        $( '#card-expiry' ).mask( '00/00' );
        $( '#card-cvc' ).mask( '0000' );
    });
</script>
