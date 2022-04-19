<?php
if (!function_exists('add_action')) {
    exit(0);
}

global $woocommerce;

$total = $woocommerce->cart->total;

if (!$model->settings->is_active_voucher()) {
    return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\View\Checkouts;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Helper\Utils;

$installments_type = Setting::get_instance()->cc_installment_type;
$ref               = sha1(random_int(1, 1000));
$type              = 'card';

?>

<li class="wc_payment_method pagarme-method">
    <input id="voucher" type="radio" class="input-radio" name="pagarme_payment_method" value="voucher" data-order_button_text>
    <label for="voucher-item"><?php esc_html_e('Voucher', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">

        <fieldset id="pagarme-fieldset-voucher" class="wc-voucher-form wc-payment-form">

            <?php require_once dirname(__FILE__) . '/choose-voucher.php'; ?>

            <div class="wc-voucher-info" data-element="fields-cc-data">
                <?php
                Utils::get_template(
                    'templates/checkout/common-voucher-item',
                    [
                        'suffix'            => 1,
                        'installments_type' => $installments_type
                    ]
                );
                ?>
            </div>

            <?php Utils::get_template('templates/checkout/field-save-card', ['suffix' => 1]); ?>

        </fieldset>
    </div>
</li>
