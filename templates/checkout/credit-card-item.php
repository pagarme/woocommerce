<?php
if (!function_exists('add_action')) {
    exit(0);
}

global $woocommerce;

$total = $woocommerce->cart->total;

if (!$model->settings->is_active_credit_card()) {
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
    <input id="credit-card" type="radio" class="input-radio" name="method" value="credit_card" data-order_button_text>
    <label for="credit-card"><?php esc_html_e('Pay with credit card', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">

        <fieldset class="wc-credit-card-form wc-payment-form">

            <?php require_once dirname(__FILE__) . '/choose-credit-card.php'; ?>

            <div class="wc-credit-card-info" data-element="fields-cc-data">
                <?php
                Utils::get_template(
                    'templates/checkout/common-card-item',
                    compact('wc_order', 'installments_type')
                );
                ?>
            </div>

            <p class="form-row form-row-wide">

                <label for="installments">
                    <?php esc_html_e('Installments quantity', 'woo-pagarme-payments'); ?><span class="required">*</span>
                </label>

                <select id="installments" <?php echo
                                            /** phpcs:ignore */
                                            Utils::get_component('installments'); ?> data-total="<?php echo esc_html($total); ?>" data-type="<?php echo intval($installments_type); ?>" data-action="select2" data-required="true" data-element="installments" name="installments">

                    <?php
                    if ($installments_type != 2) {
                        Checkouts::render_installments($total);
                    } else {
                        echo '<option value="">...</option>';
                    };
                    ?>

                </select>
            </p>

            <?php Utils::get_template('templates/checkout/field-save-card'); ?>
            <?php Utils::get_template('templates/checkout/field-enable-multicustomers', compact('ref', 'type')); ?>

        </fieldset>

        <?php Utils::get_template('templates/checkout/multicustomers-form', compact('ref', 'type')); ?>

        <input style="display:none;" data-element="credit-card" data-action="choose-payment" type="radio" name="payment_method" checked="checked" value="credit_card">
    </div>
</li>
