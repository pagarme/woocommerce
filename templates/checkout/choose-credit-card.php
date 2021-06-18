<?php
if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Helper\Utils;

if (!is_user_logged_in()) {
    return;
}

// TODO: Get this configuration from core.
$setting = Setting::get_instance();
if (!$setting->is_allowed_save_credit_card()) {
    return;
}

$customer = new Customer(get_current_user_id());
$suffix   = isset($suffix) ? $suffix : '';

$cards = $customer->get_cards();
if (!$cards) {
    return;
}

?>

<p class="form-row form-row-wide">

    <?php esc_html_e('Credit cards save', 'woo-pagarme-payments'); ?><br>

    <select name="card_id<?php echo esc_html($suffix); ?>" id="field-choose-card" data-action="select2" data-installments-type="<?php echo intval(Setting::get_instance()->cc_installment_type); ?>" data-element="choose-credit-card">
        <option value="">
            <?php esc_html_e('Saved credit card', 'woo-pagarme-payments'); ?>
        </option>

        <?php
        foreach ($cards as $card) :
            printf(
                '<option data-brand="%3$s" value="%2$s">(%1$s) •••• •••• •••• %4$s</option>',
                esc_html(strtoupper($card->getBrand()->getName())),
                esc_attr($card->getPagarmeId()->getValue()),
                esc_html(strtolower($card->getBrand()->getName())),
                esc_html($card->getLastFourDigits()->getValue())
            );
        endforeach;
        ?>
    </select>
</p>
