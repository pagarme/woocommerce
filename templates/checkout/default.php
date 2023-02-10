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

wp_enqueue_script('pagarme-checkout-card', Core::plugins_url('assets/javascripts/front/checkout/model/payment.js'));
wp_localize_script(
    'pagarme-checkout-card',
    'wc_pagarme_checkout',
    ['config' => $model->getConfigDataProvider()]
);

$wc_api = get_home_url(null, '/wc-api/' . Checkout::API_REQUEST);
?>
<div id="wcmp-checkout-errors">
    <ul class="woocommerce-error"></ul>
</div>
<?php Utils::get_template(
    'templates/checkout/environment',
    array('model' => $model)
); ?>
<?php Utils::get_template(
    'templates/checkout/payment/' . str_replace('_', '-', $model->payment),
    array('model' => $model)
); ?>
<script type="application/javascript">
    var ajaxUrl = "<?= admin_url('admin-ajax.php'); ?>";
    var cartTotal = <?= WC()->cart->total ?>;
</script>
