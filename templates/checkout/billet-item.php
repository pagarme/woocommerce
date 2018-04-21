<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

if ( ! $model->settings->is_active_billet() ) {
	return;
}

use Woocommerce\Mundipagg\Core;

?>
<li>
	<div id="tab-billet" class="panel entry-content">
		<ul>
			<li>
				<label>
					<?php
						printf( '<img class="logo" src="%1$s" alt="%2$s" title="%2$s" />',
							Core::plugins_url( 'assets/images/barcode.svg' ),
							__( 'Boleto', Core::TEXTDOMAIN )
						);
					?>
					<input data-element="boleto"
					       type="radio"
					       name="payment_method"
					       value="billet">
				</label>
			</li>
		</ul>
	</div>
</li>
