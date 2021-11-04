<?php
if (!function_exists('add_action')) {
    exit(0);
}

if (!$model->settings->is_active_billet()) {
    return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;

$ref  = sha1(random_int(1, 1000));
$type = 'billet';

?>
<li class="wc_payment_method pagarme-method">
    <input id="billet" type="radio" class="input-radio" name="pagarme_payment_method" value="billet" data-order_button_text="" />
    <label for="billet"><?php esc_html_e('Boleto', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">
        <fieldset class="wc-credit-card-form wc-payment-form">
            <p>
                O Boleto bancário será exibido após a confirmação da compra e poderá ser pago em qualquer agência bancária, pelo seu smartphone ou computador através de serviços digitais de bancos.
            </p>
            <label>
                <?php
                printf(
                    '<img class="logo" src="%1$s" alt="%2$s" title="%2$s" />',
                    esc_url(Core::plugins_url('assets/images/barcode.svg')),
                    esc_html__('Boleto', 'woo-pagarme-payments')
                );
                ?>
            </label>
            <?php Utils::get_template('templates/checkout/field-enable-multicustomers', compact('ref', 'type')); ?>
        </fieldset>
        <?php Utils::get_template('templates/checkout/multicustomers-form', compact('ref', 'type')); ?>
    </div>
</li>
