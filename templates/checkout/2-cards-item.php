<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

if ( ! $model->settings->is_active_2_cards() ) {
	return;
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Setting;

$installments_type = Setting::get_instance()->cc_installment_type;

?>

<li>
	<div id="tab-2-cards" class="payment_box panel entry-content">

		<fieldset class="wc-credit-card-form wc-payment-form">

			<h4>1º Cartão</h4>

			<?php Utils::get_template( 'templates/checkout/choose-credit-card' ); ?>

			<p class="form-row form-row-first">

				<label for="card-order-value"><?php _e( 'Value (Credit Card)', Core::TEXTDOMAIN ); ?> <span class="required">*</span></label>

				<input id="card-order-value" name="card_order_value"
						data-element="card-order-value"
						data-required="true"
						data-mask="#.##0,00" data-mask-reverse="true"
						class="input-text wc-credit-card-form-card-expiry">
			</p>

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

			<?php Utils::get_template( 'templates/checkout/field-save-card' ); ?>

		</fieldset>

		<fieldset class="wc-credit-card-form wc-payment-form">	

			<h4>2º Cartão</h4>

			<?php Utils::get_template( 'templates/checkout/choose-credit-card', [ 'suffix' => 2 ] ); ?>

			<p class="form-row form-row-first">

				<label for="card-order-value2"><?php _e( 'Value (Credit Card)', Core::TEXTDOMAIN ); ?> <span class="required">*</span></label>

				<input id="card-order-value2" name="card_order_value2"
						data-element="card-order-value"
						data-required="true"
						data-mask="#.##0,00" data-mask-reverse="true"
						class="input-text wc-credit-card-form-card-expiry">
			</p>

			<div class="wc-credit-card-info" data-element="fields-cc-data">
				
				<?php	
					Utils::get_template(
						'templates/checkout/common-card-item',
						array(
							'wc_order'          => $wc_order,
							'installments_type' => $installments_type,
							'suffix'            => 2
						)
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
						name="installments2">

					<?php
						if ( $installments_type != 2 ) {
							Checkouts::render_installments( $wc_order );
						} else {
							echo '<option value="">...</option>';
						};
					?>

				</select>
			</p>

			<?php Utils::get_template( 'templates/checkout/field-save-card', [ 'suffix' => 2 ] ); ?>

		</fieldset>

		<input style="display:none;"
			   data-action="choose-payment"
			   data-element="2cards"
		       type="radio"
		       name="payment_method"
		       value="2_cards">
	</div>
</li>
