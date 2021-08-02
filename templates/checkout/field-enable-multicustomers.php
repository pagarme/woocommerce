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

$setting = Setting::get_instance();

if (!$setting->is_active_multicustomers()) {
    return;
}

$p = isset($without_container) && $without_container ? false : true;

echo $p ? '<p class="form-row form-row-wide" data-element="enable-multicustomers-check">' : '';
?>
<label data-element="enable-multicustomers-label-<?php echo esc_attr($type); ?>">
    <input type="checkbox" name="enable_multicustomers_<?php echo esc_attr($type); ?>" data-element="enable-multicustomers" data-target="<?php echo esc_attr($ref); ?>" value="1">

    <?php esc_html_e('Fill other buyer data', 'woo-pagarme-payments'); ?>
</label>
<?php echo $p ? '</p>' : ''; ?>
