<?php
if (!function_exists('add_action')) {
    exit(0);
}

if (!$model->settings->is_active_billet_and_card()) {
    return;
}

global $woocommerce;

$total = $woocommerce->cart->total;

use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\View\Checkouts;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;

$installments_type = Setting::get_instance()->cc_installment_type;
$billet_and_card   = true;
$ref_billet        = sha1(random_int(1, 1000));
$ref_card          = sha1(random_int(1, 1000));

?>

<li class="wc_payment_method pagarme-method">
    <input id="billet-and-card" type="radio" class="input-radio" name="pagarme_payment_method" value="billet-and-card" data-order_button_text="">
    <label for="billet-and-card"><?php esc_html_e('Credit card and Boleto', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">

        <fieldset id="pagarme-fieldset-billet-and-card" class="wc-credit-card-form wc-payment-form">

            <?php Utils::get_template('templates/checkout/choose-credit-card', ['suffix' => 4, 'cardType' => [TransactionType::CREDIT_CARD,TransactionType::DEBIT_CARD]]); ?>

            <div class="form-row form-row-wide">
                <div class="form-row form-row-first">
                    <label for="billet-value">
                        <?php esc_html_e('Value (Boleto)', 'woo-pagarme-payments'); ?><span class="required">*</span>
                    </label>
                    <input id="billet-value" name="billet_value" data-element="billet-value" data-value="1" data-required="true" class="input-text" style="font-size: 1.41575em">
                    <?php
                    Utils::get_template(
                        'templates/checkout/field-enable-multicustomers',
                        array(
                            'ref'               => $ref_billet,
                            'type'              => 'billet',
                            'without_container' => true,
                        )
                    );
                    ?>
                </div>

                <div class="form-row form-row-last">
                    <label for="card-billet-order-value">
                        <?php esc_html_e('Value (Credit Card)', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                    </label>
                    <input id="card-billet-order-value" name="card_billet_order_value" data-element="card-order-value" data-value="2" data-required="true" data-mask="#.##0,00" data-mask-reverse="true" class="input-text" style="font-size: 1.41575em">
                    <?php
                    Utils::get_template(
                        'templates/checkout/field-enable-multicustomers',
                        array(
                            'ref'               => $ref_card,
                            'type'              => 'card',
                            'without_container' => true,
                        )
                    );
                    ?>
                </div>
            </div>

            <div class="wc-credit-card-info" data-element="fields-cc-data">
                <?php
                Utils::get_template(
                    'templates/checkout/common-card-item',
                    [
                        'suffix' => 4,
                    ]
                );
                ?>
            </div>

            <p class="form-row form-row-wide">

                <label for="installments3">
                    <?php esc_html_e('Installments quantity', 'woo-pagarme-payments'); ?><span class="required">*</span>
                </label>

                <select id="installments3" <?php /*phpcs:ignore*/ echo Utils::get_component('installments'); ?> data-total="<?php echo esc_html($total); ?>" data-type="<?php echo esc_attr(intval($installments_type)); ?>" data-action="select2" data-required="true" data-element="installments" name="installments3" style="font-size: 1.41575em">

                    <?php
                    if ($installments_type != 2) {
                        Checkouts::render_installments($total);
                    } else {
                        echo wp_kses('<option value="">...</option>', array('option' => array('value' => true)));;
                    };
                    ?>

                </select>
            </p>

            <?php Utils::get_template('templates/checkout/field-save-card', ['suffix' => 4]); ?>

        </fieldset>

        <?php
        Utils::get_template(
            'templates/checkout/multicustomers-form',
            array(
                'ref'   => $ref_billet,
                'type'  => 'billet',
                'title' => 'Dados comprador (Boleto)',
                'suffix' => '_card'
            )
        );

        Utils::get_template(
            'templates/checkout/multicustomers-form',
            array(
                'ref'   => $ref_card,
                'type'  => 'card',
                'title' => 'Dados comprador (Cartão)',
                'suffix' => '_billet'
            )
        );
        ?>

    </div>
</li>
