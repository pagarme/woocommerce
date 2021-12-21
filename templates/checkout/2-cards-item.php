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

global $woocommerce;

$total = $woocommerce->cart->total;

$installments_type = Setting::get_instance()->cc_installment_type;
$ref1              = sha1(random_int(1, 1000));
$ref2              = sha1(random_int(1, 1000));

?>

<li class="wc_payment_method pagarme-method">
    <input id="2-credit-card" type="radio" class="input-radio" name="pagarme_payment_method" value="2_cards" data-order_button_text>
    <label for="2-credit-card"><?php esc_html_e('2 credit cards', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">

        <fieldset id="pagarme-fieldset-2_cards" class="wc-credit-card-form wc-payment-form">

            <h4>1º Cartão</h4>

            <?php Utils::get_template('templates/checkout/choose-credit-card', ['suffix' => 2]); ?>

            <p class="form-row form-row-first">

                <label for="card-order-value"><?php esc_html_e('Value (Credit Card)', 'woo-pagarme-payments'); ?> <span class="required">*</span></label>

                <input id="card-order-value" name="card_order_value" data-element="card-order-value" data-required="true" data-value="1" class="input-text">
            </p>

            <div class="wc-credit-card-info" data-element="fields-cc-data">

                <?php
                Utils::get_template(
                    'templates/checkout/common-card-item',
                    [
                        'suffix' => 2,
                    ]
                );
                ?>
            </div>

            <p class="form-row form-row-wide">

                <label for="installments">
                    <?php esc_html_e('Installments quantity', 'woo-pagarme-payments'); ?><span class="required">*</span>
                </label>

                <select id="installments" <?php echo
                                            /** phpcs:ignore */
                                            Utils::get_component('installments'); ?> data-total="<?php echo esc_html($total); ?>" data-type="<?php echo esc_attr(intval($installments_type)); ?>" data-action="select2" data-required="true" data-element="installments" name="installments" style="font-size: 1.41575em">

                    <?php
                    if ($installments_type != 2) {
                        Checkouts::render_installments($total);
                    } else {
                        echo wp_kses('<option value="">...</option>', array('option' => array('value' => true)));
                    };
                    ?>

                </select>
            </p>

            <?php
            Utils::get_template('templates/checkout/field-save-card', ['suffix' => 2]);
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

        <fieldset id="pagarme-fieldset-2_cards" class="wc-credit-card-form wc-payment-form">

            <h4>2º Cartão</h4>

            <?php Utils::get_template('templates/checkout/choose-credit-card', ['suffix' => 3]); ?>

            <p class="form-row form-row-first">

                <label for="card-order-value2"><?php esc_html_e('Value (Credit Card)', 'woo-pagarme-payments'); ?> <span class="required">*</span></label>

                <input id="card-order-value2" name="card_order_value2" data-element="card-order-value" data-value="2" data-required="true" class="input-text">
            </p>

            <div class="wc-credit-card-info" data-element="fields-cc-data">

                <?php
                Utils::get_template(
                    'templates/checkout/common-card-item',
                    array(
                        'installments_type' => $installments_type,
                        'suffix'            => 3,
                    )
                );
                ?>
            </div>

            <p class="form-row form-row-wide">

                <label for="installments2">
                    <?php esc_html_e('Installments quantity', 'woo-pagarme-payments'); ?><span class="required">*</span>
                </label>

                <select id="installments2" <?php echo
                                            /** phpcs:ignore */
                                            Utils::get_component('installments'); ?> data-total="<?php echo esc_html($total); ?>" data-type="<?php echo esc_attr(intval($installments_type)); ?>" data-action="select2" data-required="true" data-element="installments" name="installments2" style="font-size: 1.41575em">

                    <?php
                    if ($installments_type != 2) {
                        Checkouts::render_installments($total);
                    } else {
                        echo wp_kses('<option value="">...</option>', array('option' => array('value' => true)));;
                    };
                    ?>

                </select>
            </p>

            <?php
            Utils::get_template('templates/checkout/field-save-card', ['suffix' => 3]);
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

    </div>
</li>
