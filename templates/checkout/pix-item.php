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
<li>
    <div id="tab-pix" class="panel entry-content">
        <fieldset class="wc-credit-card-form wc-payment-form">
            <label>
                <?php
                printf(
                    '<img class="logo" src="%1$s" alt="%2$s" title="%2$s" />',
                    esc_url(Core::plugins_url('assets/images/pix.svg')),
                    esc_html__('Pix', 'woo-pagarme-payments')
                );
                ?>
                <input data-element="pix" type="radio" name="payment_method" value="pix">
            </label>
            <?php Utils::get_template('templates/checkout/field-enable-multicustomers', compact('ref', 'type')); ?>
        </fieldset>
        <?php Utils::get_template('templates/checkout/multicustomers-form', compact('ref', 'type')); ?>
    </div>
</li>
