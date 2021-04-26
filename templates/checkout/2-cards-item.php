<?php
if (!function_exists('add_action')) {
    exit(0);
}

if (!$model->settings->is_active_2_cards()) {
    return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\View\Checkouts;

$installments_type = Setting::get_instance()->cc_installment_type;
$ref1              = md5(rand(1, 1000));
$ref2              = md5(rand(1, 1000));

?>

<li>
    <div id="tab-2-cards" class="payment_box panel entry-content">

        <fieldset class="wc-credit-card-form wc-payment-form">

            <h4>1º Cartão</h4>

            <?php Utils::get_template('templates/checkout/choose-credit-card'); ?>

            <p class="form-row form-row-first">

                <label for="card-order-value"><?php esc_html_e('Value (Credit Card)', 'woo-pagarme-payments'); ?> <span class="required">*</span></label>

                <input id="card-order-value" name="card_order_value" data-element="card-order-value" data-required="true" data-value="1" data-mask="#.##0,00" data-mask-reverse="true" class="input-text wc-credit-card-form-card-expiry">
            </p>

            <div class="wc-credit-card-info" data-element="fields-cc-data">

                <?php
                Utils::get_template(
                    'templates/checkout/common-card-item',
                    compact('wc_order', 'installments_type')
                );
                ?>
            </div>

            <p class="form-row form-row-first">

                <label for="installments">
                    <?php esc_html_e('Installments quantity', 'woo-pagarme-payments'); ?><span class="required">*</span>
                </label>

                <select id="installments" <?php echo
                                            /** phpcs:ignore */
                                            Utils::get_component('installments'); ?> data-total="<?php echo esc_html($wc_order->get_total()); ?>" data-type="<?php echo intval($installments_type); ?>" data-action="select2" data-required="true" data-element="installments" name="installments">

                    <?php
                    if ($installments_type != 2) {
                        Checkouts::render_installments($wc_order);
                    } else {
                        echo '<option value="">...</option>';
                    };
                    ?>

                </select>
            </p>

            <?php
            Utils::get_template('templates/checkout/field-save-card');
            Utils::get_template(
                'templates/checkout/field-enable-multicustomers',
                array(
                    'ref'  => $ref1,
                    'type' => 'card1',
                )
            );
            ?>

        </fieldset>

        <?php
        Utils::get_template(
            'templates/checkout/multicustomers-form',
            array(
                'ref'  => $ref1,
                'type' => 'card1',
            )
        );
        ?>

        <fieldset class="wc-credit-card-form wc-payment-form">

            <h4>2º Cartão</h4>

            <?php Utils::get_template('templates/checkout/choose-credit-card', ['suffix' => 2]); ?>

            <p class="form-row form-row-first">

                <label for="card-order-value2"><?php esc_html_e('Value (Credit Card)', 'woo-pagarme-payments'); ?> <span class="required">*</span></label>

                <input id="card-order-value2" name="card_order_value2" data-element="card-order-value" data-value="2" data-required="true" data-mask="#.##0,00" data-mask-reverse="true" class="input-text wc-credit-card-form-card-expiry">
            </p>

            <div class="wc-credit-card-info" data-element="fields-cc-data">

                <?php
                Utils::get_template(
                    'templates/checkout/common-card-item',
                    array(
                        'wc_order'          => $wc_order,
                        'installments_type' => $installments_type,
                        'suffix'            => 2,
                    )
                );
                ?>
            </div>

            <p class="form-row form-row-first">

                <label for="installments2">
                    <?php esc_html_e('Installments quantity', 'woo-pagarme-payments'); ?><span class="required">*</span>
                </label>

                <select id="installments2" <?php echo
                                            /** phpcs:ignore */
                                            Utils::get_component('installments'); ?> data-total="<?php echo esc_html($wc_order->get_total()); ?>" data-type="<?php echo intval($installments_type); ?>" data-action="select2" data-required="true" data-element="installments" name="installments2">

                    <?php
                    if ($installments_type != 2) {
                        Checkouts::render_installments($wc_order);
                    } else {
                        echo '<option value="">...</option>';
                    };
                    ?>

                </select>
            </p>

            <?php
            Utils::get_template('templates/checkout/field-save-card', ['suffix' => 2]);
            Utils::get_template(
                'templates/checkout/field-enable-multicustomers',
                array(
                    'ref'  => $ref2,
                    'type' => 'card2',
                )
            );
            ?>

        </fieldset>

        <?php
        Utils::get_template(
            'templates/checkout/multicustomers-form',
            array(
                'ref'  => $ref2,
                'type' => 'card2',
            )
        );
        ?>

        <input style="display:none;" data-action="choose-payment" data-element="2cards" type="radio" name="payment_method" value="2_cards">
    </div>
</li>
