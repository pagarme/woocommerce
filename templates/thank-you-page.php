<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Order;
use Woocommerce\Mundipagg\View\Checkouts;

$model = new Order( $order_id );
$class = 'message';

if ( in_array( $model->mundipagg_status, ['failed', 'canceled'] ) ) {
	$class = 'error'; 
}

?>

<div class="woocommerce-<?php echo $class; ?>">
	<div class="mundipagg-response">
	<?php echo Checkouts::handle_messages( $model ); ?>
	</div>
</div>

