<?php
if (!defined('ABSPATH')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Model\Setting;

if (!is_user_logged_in()) {
    return;
}

$customer = new Customer(get_current_user_id());
$suffix   = isset($suffix) ? $suffix : '';
$type     = isset($type) ? $type : '';
$setting  = Setting::get_instance();

switch ($type) {
    case 'voucher':
        if (!$setting->is_allowed_save_voucher_card()) {
            return;
        }
        break;
    default:
        if (!$setting->is_allowed_save_credit_card()) {
            return;
        }
        break;
}

?>
<p class="form-row form-row-wide" data-element="save-cc-check">
    <label for="save-credit-card<?php echo esc_html($suffix); ?>">

        <input type="checkbox" id="save-credit-card<?php echo esc_html($suffix); ?>" name="save_credit_card<?php echo esc_html($suffix); ?>" value="1" <?php checked($customer->save_credit_card, true); ?>>

        <?php esc_html_e('Save this card for future purchases', 'woo-pagarme-payments'); ?>
    </label>
</p>
