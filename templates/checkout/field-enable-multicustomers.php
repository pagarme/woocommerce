<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Customer;
use Woocommerce\Mundipagg\Model\Setting;

if ( ! is_user_logged_in() ) {
	return;
}

$setting = Setting::get_instance();

if ( ! $setting->is_active_multicustomers() ) {
	return;
}

$p = isset( $without_container ) && $without_container ? false : true;

echo $p ? '<p class="form-row form-row-first">' : '';
?>
	<label>
		<input type="checkbox"
		       name="enable_multicustomers_<?php echo $type; ?>"
			   data-element="enable-multicustomers"
			   data-target="<?php echo $ref; ?>"
		       value="1">

		<?php _e( 'Fill other buyer data', 'woo-mundipagg-payments' ); ?>
	</label>
<?php echo $p ? '</p>' : ''; ?>
