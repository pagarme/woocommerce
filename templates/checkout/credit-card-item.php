<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

if ( ! $model->settings->is_active_credit_card() ) {
	return;
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\View\Checkouts;
use Woocommerce\Mundipagg\Model\Customer;
use Woocommerce\Mundipagg\Model\Setting;
use Woocommerce\Mundipagg\Helper\Utils;

$installments_type = Setting::get_instance()->cc_installment_type;

?>

<li>
	<div id="tab-credit-card" class="payment_box panel entry-content">

		<fieldset class="wc-credit-card-form wc-payment-form">

			<?php require_once dirname( __FILE__ ) .  '/choose-credit-card.php'; ?>

			<div class="wc-credit-card-info" data-element="fields-cc-data">
			<?php	
				Utils::get_template(
					'templates/checkout/common-card-item',
					compact( 'wc_order', 'installments_type' )
				);
			?>
			</div>

			<p class="form-row form-row-first">

				<label for="installments">
					<?php _e( 'Installments quantity', Core::TEXTDOMAIN ); ?><span class="required">*</span>
				</label>

				<select id="installments"
						<?php echo Utils::get_component( 'installments' ); ?>
						data-total="<?php echo $wc_order->get_total(); ?>"
						data-type="<?php echo $installments_type; ?>"
						data-action="select2"
						data-required="true"
						data-element="installments"
						name="installments">

					<?php
						if ( $installments_type != 2 ) {
							Checkouts::render_installments( $wc_order );
						} else {
							echo '<option value="">...</option>';
						};
					?>

				</select>
			</p>

			<?php require dirname( __FILE__ ) .  '/field-save-card.php'; ?>
			
		</fieldset>	

		<input style="display:none;"
		       data-element="credit-card"
		       data-action="choose-payment"
		       type="radio"
		       name="payment_method"
		       value="credit_card">
	</div>
</li>
