<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

if ( ! $model->settings->is_active_billet() ) {
	return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;

$ref  = md5( rand( 1, 1000 ) );
$type = 'billet';

?>
<li>
	<div id="tab-billet" class="panel entry-content">
		<fieldset class="wc-credit-card-form wc-payment-form">
			<label>
				<?php
					printf( '<img class="logo" src="%1$s" alt="%2$s" title="%2$s" />',
						esc_url( Core::plugins_url( 'assets/images/barcode.svg' ) ),
						esc_html__( 'Boleto', 'woo-pagarme-payments' )
					);
				?>
				<input data-element="boleto"
					type="radio"
					name="payment_method"
					value="billet">
			</label>
		<?php Utils::get_template( 'templates/checkout/field-enable-multicustomers', compact( 'ref', 'type' ) ); ?>
		</fieldset>
		<?php Utils::get_template( 'templates/checkout/multicustomers-form', compact( 'ref', 'type' ) ); ?>
	</div>
</li>
