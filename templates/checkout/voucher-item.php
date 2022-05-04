<?php
if (!function_exists('add_action')) {
    exit(0);
}
/** phpcs:disable */
if (!$model->settings->is_active_voucher()) {
    return;
}

use Woocommerce\Pagarme\Helper\Utils;

$ref               = sha1(random_int(1, 1000));
$type              = 'voucher';

?>

<li class="wc_payment_method pagarme-method">
    <input id="voucher" type="radio" class="input-radio" name="pagarme_payment_method" value="voucher" data-order_button_text="">
    <label for="voucher"><?php esc_html_e('Voucher', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">

        <fieldset id="pagarme-fieldset-voucher" class="wc-voucher-form wc-payment-form">
            <label>
                <div class="wc-voucher-info" data-element="fields-voucher-data">
                    <?php
                    Utils::get_template(
                        'templates/checkout/common-voucher-item',
                        [
                            'suffix'            => 6
                        ]
                    );
                    ?>
                </div>
            </label>
        </fieldset>
    </div>
</li>