<?php

if ( ! function_exists( 'add_action' ) ) {
	return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;

$suffix  = isset( $suffix ) ? $suffix : '';
$setting = Setting::get_instance();

?>

<div <?php echo /** phpcs:ignore */ Utils::get_component( 'pagarme-checkout' ); ?>
	data-mundicheckout-app-id="<?php echo esc_attr( $setting->get_public_key() ); ?>"
	data-mundicheckout-suffix="<?php echo ! $suffix ? 1 : esc_html( $suffix ); ?>">

	<p class="form-row form-row-wide">

		<label for="card-holder-name">
			<?php esc_html_e( 'Card Holder Name', 'woo-pagarme-payments' ); ?> <span class="required">*</span>
		</label>

		<input id="card-holder-name"
				data-element="card-holder-name"
				data-required="true"
				class="input-text wc-credit-card-form-card-expiry"
				data-mundicheckout-element="holder_name">
	</p>

	<p class="form-row form-row-wide">

		<label for="card-number"><?php esc_html_e( 'Card number', 'woo-pagarme-payments' ); ?> <span class="required">*</span></label>

		<input id="card-number"
				data-element="card-number"
				class="input-text wc-credit-card-form-card-expiry"
				data-mask="0000000000000000000"
				placeholder="•••• •••• •••• ••••"
				data-required="true"
				data-mundicheckout-element="number">
		<input type="hidden" name="brand<?php echo esc_html( $suffix ); ?>" data-mundicheckout-element="brand-input"/>
		<span data-mundicheckout-element="brand"
				data-mundicheckout-brand-image
				data-mundicheckout-brand></span>
	</p>

	<p class="form-row form-row-first">

		<label for="card-expiry">
			<?php esc_html_e( 'Expiry (MM/YY)', 'woo-pagarme-payments' ); ?>
			<span class="required">*</span>
		</label>

		<input id="card-expiry" data-element="card-expiry"
				class="input-text wc-credit-card-form-card-expiry"
				data-mask="00/00"
				data-required="true"
				placeholder="<?php esc_html_e( 'MM / YY', 'woo-pagarme-payments' ); ?>"
				data-mundicheckout-element="exp_date">
	</p>

	<p class="form-row form-row-last">

		<label for="card-cvc">
			<?php esc_html_e( 'Card code', 'woo-pagarme-payments' ); ?> <span class="required">*</span>
		</label>

		<input id="card-cvc"
				data-element="card-cvc"
				data-mask="0000"
				class="input-text wc-credit-card-form-card-cvc"
				maxlength="4"
				placeholder="CVC"
				style="width:100px"
				data-required="true"
				data-mundicheckout-element="cvv">
	</p>

</div>
