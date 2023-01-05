<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

/** @var \Woocommerce\Pagarme\Model\Gateway $model */

declare( strict_types=1 );

if (!function_exists('add_action')) {
    exit(0);
}

global $woocommerce;

use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;

$wc_api      = get_home_url(null, '/wc-api/' . Checkout::API_REQUEST);
?>
<div id="wcmp-checkout-errors">
    <ul class="woocommerce-error"></ul>
</div>
<?php Utils::get_template(
    'templates/checkout/environment',
    array('model' => $model)
); ?>
<?php Utils::get_template(
    'templates/checkout/payment/' . $model->payment,
    array('model' => $model)
); ?>
<script type="application/javascript">
    var ajaxUrl = "<?= admin_url('admin-ajax.php'); ?>";
    var cartTotal = <?= WC()->cart->total ?>;
</script>
<script src="<?= esc_url(Core::plugins_url('assets/javascripts/front/main.js')); ?>" type="application/javascript"></script>
