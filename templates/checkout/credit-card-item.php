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
$ref               = md5( rand( 1, 1000 ) );
$type              = 'card';

?>

<li>
	<div id="tab-credit-card" class="payment_box panel entry-content">

		<fieldset class="wc-credit-card-form wc-payment-form">

			<?php require_once dirname( __FILE__ ) . '/choose-credit-card.php'; ?>

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
					<?php esc_html_e( 'Installments quantity', 'woo-mundipagg-payments' ); ?><span class="required">*</span>
				</label>

				<select id="installments"
						<?php echo /** phpcs:ignore */ Utils::get_component( 'installments' ); ?>
						data-total="<?php echo esc_html( $wc_order->get_total() ); ?>"
						data-type="<?php echo intval( $installments_type ); ?>"
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
			<?php Utils::get_template( 'templates/checkout/field-enable-multicustomers', compact( 'ref', 'type' ) ); ?>

		</fieldset>

		<?php Utils::get_template( 'templates/checkout/multicustomers-form', compact( 'ref', 'type' ) ); ?>

		<input style="display:none;"
			data-element="credit-card"
			data-action="choose-payment"
			type="radio"
			name="payment_method"
            checked="checked"
			value="credit_card">
	</div>
</li>
