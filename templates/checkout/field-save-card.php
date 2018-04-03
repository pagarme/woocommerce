<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Customer;

if ( ! is_user_logged_in() ) {
	return;
}

$customer = new Customer( get_current_user_id() );
$suffix   = isset( $suffix ) ? $suffix : '';

?>
<p class="form-row form-row-first" data-element="save-cc-check">
	<label for="save-credit-card<?php echo $suffix; ?>">

		<input type="checkbox"
		       id="save-credit-card<?php echo $suffix; ?>"
		       name="save_credit_card<?php echo $suffix; ?>"
		       value="1"
		       <?php checked( $customer->save_credit_card, true ); ?>>

		<?php _e( 'Save this card for future purchases', Core::TEXTDOMAIN ); ?>
	</label>
</p>
