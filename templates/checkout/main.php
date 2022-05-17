<?php
if (!function_exists('add_action')) {
    exit(0);
}
global $woocommerce;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Model\Setting;

$tab_num     = Setting::get_instance()->get_active_tab();
$wc_api      = get_home_url(null, '/wc-api/' . Checkout::API_REQUEST);
$swal_data   = array(
    'title'        => __('Waiting...', 'woo-pagarme-payments'),
    'text'         => __('We are processing your request.', 'woo-pagarme-payments'),
    'text_default' => __('An error occurred while processing.', 'woo-pagarme-payments'),
    'text_success' => __('Your transaction has been processed successfully.', 'woo-pagarme-payments'),
);

?>

<div id="wcmp-checkout-errors">
    <ul class="woocommerce-error"></ul>
</div>
<?php if ($model->is_sandbox_mode()) : ?>
    <div class="pagarme-message-warning">
        <span>
            <?= __('Important! This store is in the testing phase. Orders placed in this environment will not be carried out.', 'woo-pagarme-payments'); ?>
        </span>
    </div>
<?php endif; ?>
<ul class="wc_payment_methods payment_methods methods" <?php echo
                                                        /** phpcs:ignore */
                                                        Utils::get_component('checkout-transparent'); ?>>
    <?php

    Utils::get_template(
        'templates/checkout/credit-card-item',
        array('model' => $model)
    );

    Utils::get_template(
        'templates/checkout/2-cards-item',
        array('model' => $model)
    );

    Utils::get_template(
        'templates/checkout/billet-and-card-item',
        array('model' => $model)
    );

    Utils::get_template(
        'templates/checkout/billet-item',
        array('model' => $model)
    );

    Utils::get_template(
        'templates/checkout/pix-item',
        array('model' => $model)
    );

    Utils::get_template(
        'templates/checkout/voucher-item',
        array('model' => $model)
    );

    ?>
</ul>

<script type="application/javascript">
    var ajaxUrl = "<?= admin_url('admin-ajax.php'); ?>";
    var cartTotal = <?= WC()->cart->total ?>;
</script>
<script src="<?= esc_url(Core::plugins_url('assets/javascripts/front/main.js')); ?>" type="application/javascript"> </script>
