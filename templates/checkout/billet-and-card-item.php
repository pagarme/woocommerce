<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

if ( ! $model->settings->is_active_billet_and_card() ) {
	return;
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\View\Checkouts;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Setting;

$installments_type = Setting::get_instance()->cc_installment_type;
$billet_and_card   = true;

?>
<li>
	<div id="tab-billet-and-card" class="payment_box panel entry-content">

		<fieldset class="wc-credit-card-form wc-payment-form">

			<?php require_once dirname( __FILE__ ) .  '/choose-credit-card.php'; ?>

			<div class="form-row form-row-wide">
				<p class="form-row form-row-first">
					<label for="billet-value">
						<?php _e( 'Value (Billet)', Core::TEXTDOMAIN ); ?><span class="required">*</span>
					</label>
					<input id="billet-value"
							name="billet_value"
							data-mask="#.##0,00"
							data-mask-reverse="true"
							data-element="billet-value"
							data-required="true"
							class="input-text wc-credit-card-form-card-expiry">
				</p>

				<p class="form-row form-row-last">
					<label for="card-order-value">
						<?php _e( 'Value (Credit Card)', Core::TEXTDOMAIN ); ?> <span class="required">*</span>
					</label>
					<input id="card-order-value"
							name="card_order_value"
							data-element="card-order-value"
							data-required="true"
							data-mask="#.##0,00"
							data-mask-reverse="true"
							class="input-text wc-credit-card-form-card-expiry">
				</p>
			</div>

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
						name="installments<?php echo $suffix; ?>">

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
			   data-action="choose-payment"
			   data-element="billet-and-card"
		       type="radio"
		       name="payment_method"
		       value="billet_and_card">
	</div>
</li>
