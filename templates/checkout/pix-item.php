<?php
if (!function_exists('add_action')) {
    exit(0);
}

if (!$model->settings->is_active_pix()) {
    return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;

$ref  = sha1(random_int(1, 1000));
$type = 'pix';

?>
<li class="wc_payment_method pagarme-method">
    <input id="pix" type="radio" class="input-radio" name="pagarme_payment_method" value="pix" data-order_button_text="" />
    <label for="pix"><?php esc_html_e('PIX', 'woo-pagarme-payments'); ?></label>
    <div class="payment_box panel entry-content pagarme_methods" style="display:none;">
        <fieldset class="wc-credit-card-form wc-payment-form">
            <p>
                O QR Code para seu pagamento através de PIX será gerado após a confirmação da compra. Aponte seu celular para a tela para capturar o código ou copie e cole o código em seu aplicativo de pagamentos.
            </p>
            <label>
                <?php
                printf(
                    '<img class="logo" src="%1$s" alt="%2$s" title="%2$s" />',
                    esc_url(Core::plugins_url('assets/images/pix.svg')),
                    esc_html__('Pix', 'woo-pagarme-payments')
                );
                ?>
            </label>
            <?php Utils::get_template('templates/checkout/field-enable-multicustomers', compact('ref', 'type')); ?>
        </fieldset>
        <?php Utils::get_template('templates/checkout/multicustomers-form', compact('ref', 'type')); ?>
    </div>
</li>
