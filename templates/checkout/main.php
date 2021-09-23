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
//$payment_url = esc_url($wc_order->get_checkout_payment_url(true));
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

    <div class="product">
        <div class="">

            <div id="payment">
                <ul class="wc_payment_methods payment_methods methods">
                    <?php

                        Utils::get_template(
                            'templates/checkout/credit-card-item',
                            array(
                                'model'    => $model,
//                                'wc_order' => $wc_order,
                            )
                        );

                        Utils::get_template(
                            'templates/checkout/billet-item',
                            array('model' => $model)
                        );

                        Utils::get_template(
                            'templates/checkout/pix-item',
                            array('model' => $model)
                        );

                        Utils::get_template(
                            'templates/checkout/billet-and-card-item',
                            array(
                                'model'    => $model,
                                'wc_order' => $wc_order,
                            )
                        );

                        Utils::get_template(
                            'templates/checkout/2-cards-item',
                            array(
                                'model'    => $model,
                                'wc_order' => $wc_order,
                            )
                        );
                    ?>


                </ul>
            </div>
        </div>
    </div>

<script !src="">
    jQuery( function( $ ) {
        $('input[name=method]').click(e => {
            e.stopPropagation();
            let li = e.target.closest('li');
            $('.pagarme_methods').slideUp();
            $(li).find('.payment_box').slideDown();
        });
    });
</script>
