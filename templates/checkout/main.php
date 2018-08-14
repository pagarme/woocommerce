<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Checkout;
use Woocommerce\Mundipagg\Model\Setting;

$tab_num     = Setting::get_instance()->get_active_tab();
$payment_url = esc_url( $wc_order->get_checkout_payment_url( true ) );
$wc_api      = get_home_url( null, '/wc-api/' . Checkout::API_REQUEST );
$swal_data   = array(
	'title'        => __( 'Waiting...', 'woo-mundipagg-payments' ),
	'text'         => __( 'We are processing your request.', 'woo-mundipagg-payments' ),
	'text_default' => __( 'An error occurred while processing.', 'woo-mundipagg-payments' ),
	'text_success' => __( 'Your transaction has been processed successfully.', 'woo-mundipagg-payments' ),
);

?>

<div id="wcmp-checkout-errors">
	<ul class="woocommerce-error"></ul>
</div>

<form method="post"
	  id="wcmp-checkout-form"
	  data-return-url="<?php echo esc_url( $this->get_return_url( $wc_order ) ); ?>"
	  data-payment-url="<?php echo $payment_url; ?>"
	  data-api-request="<?php echo esc_url( $wc_api ); ?>"
	  data-order="<?php echo $wc_order->get_order_number(); ?>"
	  data-order-total="<?php echo $wc_order->get_total(); ?>"
	  data-swal='<?php echo wp_json_encode( $swal_data, JSON_HEX_APOS ); ?>'
	  data-mundicheckout-form
	  <?php echo Utils::get_component( 'checkout-transparent' ); ?>>

	<div class="product">
		<div class="woocommerce-tabs">
			<ul class="tabs">

			<?php
				if ( $this->model->settings->is_active_credit_card() ) :
					$tab_credit_card = true;
			?>
				<li class="<?php echo ( $tab_num === 0 || $tab_num === 1 ) ? 'active' : ''; ?>">
					<a data-action="tab" data-ref="creditCard" href="#tab-credit-card">
						<?php _e( 'Pay with credit card', 'woo-mundipagg-payments' ); ?>
					</a>
				</li>

			<?php endif; ?>

			<?php
				if ( $this->model->settings->is_active_billet() ) :
					$tab_billet = true;
			?>
				<li class="<?php echo ( $tab_num === 2 ) ? 'active' : ''; ?>">
					<a data-action="tab" data-ref="boleto" href="#tab-billet">
						<?php _e( 'Pay with boleto', 'woo-mundipagg-payments' ); ?>
					</a>
				</li>

			<?php endif; ?>

			<?php
				if ( $this->model->settings->is_active_billet_and_card() ) :
					$tab_billet_and_card = true;
			?>
				<li class="<?php echo ( $tab_num === 3 ) ? 'active' : ''; ?>">
					<a data-action="tab" data-ref="billetAndCard" href="#tab-billet-and-card">
						<?php _e( 'Pay with boleto and credit card', 'woo-mundipagg-payments' ); ?>
					</a>
				</li>

			<?php endif; ?>

			<?php
				if ( $this->model->settings->is_active_2_cards() ) :
					$tab_two_cards = true;
			?>
				<li class="<?php echo ( $tab_num === 4 ) ? 'active' : ''; ?>">
					<a data-action="tab" data-ref="2cards" href="#tab-2-cards">
						<?php _e( 'Pay with 2 cards', 'woo-mundipagg-payments' ); ?>
					</a>
				</li>

			<?php endif; ?>

			</ul>

			<div id="payment">
				<ul class="wc_payment_methods payment_methods methods">
				<?php
				if ( isset( $tab_credit_card ) && ( $tab_num === 1 || ! isset( $active_tab ) && $tab_num === 0 ) ) :
					$active_tab = true;

					Utils::get_template(
						'templates/checkout/credit-card-item',
						array(
							'model'    => $this->model,
							'wc_order' => $wc_order
						)
					);
				endif;

				if ( isset( $tab_billet ) && ( $tab_num === 2 || ! isset( $active_tab ) && $tab_num === 0 ) ) :
					$active_tab = true;

					Utils::get_template(
						'templates/checkout/billet-item',
						array( 'model' => $this->model )
					);
				endif;

				if ( isset( $tab_billet_and_card ) && ( $tab_num === 3 || ! isset( $active_tab ) && $tab_num === 0 ) ) :
					$active_tab = true;

					Utils::get_template(
						'templates/checkout/billet-and-card-item',
						array(
							'model'    => $this->model,
							'wc_order' => $wc_order
						)
					);
				endif;

				if ( isset( $tab_two_cards ) && ( $tab_num === 4 || ! isset( $active_tab ) && $tab_num === 0 ) ) :
					Utils::get_template(
						'templates/checkout/2-cards-item',
						array(
							'model'    => $this->model,
							'wc_order' => $wc_order
						)
					);
				endif;
				?>
				</ul>
			</div>
		</div>
	</div>

	<p>
		<a class="button cancel" href="<?php echo esc_url( $wc_order->get_cancel_order_url() ) ?>">
			<?php _e( 'Cancel order &amp; restore cart', 'woo-mundipagg-payments' ) ?>
		</a>
		<span></span>
		<button type="submit" class="button alt" id="wcmp-submit">
			<?php _e( 'Pay order', 'woo-mundipagg-payments' ); ?>
		</button>
	</p>

</form>
<?php
