<?php
if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\View\Checkouts;

$model = new Order($order_id);
$class = 'message';

if (in_array($model->pagarme_status, ['failed', 'canceled'])) {
    $class = 'error';
}

?>

<div class="woocommerce-<?php echo esc_attr($class); ?>">
    <div class="pagarme-response">
        <?php echo
        /** phpcs:ignore */
        Checkouts::handle_messages($model); ?>
    </div>
</div>
