<?php
namespace Woocommerce\Mundipagg\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Order;
use Woocommerce\Mundipagg\Model\Setting;
use Woocommerce\Mundipagg\Model\Gateway;
use Woocommerce\Mundipagg\Model\Charge;

class Checkouts
{
	protected static function message_before()
	{
		echo '<p class="title">' . __( 'Your transaction has been processed.', Core::TEXTDOMAIN ) . '</p>';
	}

	protected static function message_after()
	{
		echo '<p>' . __( 'If you have any questions regarding the transaction, please contact us.', Core::TEXTDOMAIN ) . '</p>';
	}

	public static function handle_messages( Order $order )
	{
		switch ( $order->payment_method ) {
			case 'billet' :
				return self::billet_message( $order );

			case 'credit_card' :
				return self::credit_card_message( $order );

			case 'billet_and_card' :
				return self::billet_and_card_message( $order );

			case '2_cards' :
				return self::credit_card_message( $order );
		}
	}

	public static function billet_message( $order )
	{
		$charges     = $order->response_data->charges;
		$charge      = array_shift( $charges );
		$transaction = $charge->last_transaction;

		ob_start();

		self::message_before();

		?>
		<p>
			<?php _e( 'If you have not yet received the boleto, please click the button below to print.', Core::TEXTDOMAIN ); ?>
		</p>

		<a href="<?php echo esc_url( $transaction->pdf ); ?>" target="_blank" class="payment-link">
			<?php _e( 'Print', Core::TEXTDOMAIN ); ?>
		</a>

		<?php

		echo self::message_after();

		$message = ob_get_contents();

		ob_end_clean();

		return $message;
	}

	public static function credit_card_message( $order )
	{

		ob_start();

		self::message_before();

		?>
		<p>
		<?php
			printf(
				__( 'The status of your transaction is %s.', Core::TEXTDOMAIN ),
				'<strong>' . strtoupper( $order->get_status_translate() ) . '</strong>'
			);
		?>
		</p>
		<?php

		self::message_after();

		$message = ob_get_contents();

		ob_end_clean();

		return $message;
	}

	public static function billet_and_card_message( $order )
	{
		$charges = $order->response_data->charges;

		ob_start();

		self::message_before();

		foreach ( $charges as $charge ) :

			if ( $charge->payment_method == 'credit_card' ) :
				echo '<p>';
					printf(
						__( 'CREDIT CARD: The status of your transaction is %s.', Core::TEXTDOMAIN ),
						'<strong>' . strtoupper( $order->get_status_translate() ) . '</strong>'
					);
				echo '</p>';
			endif;

			if ( $charge->payment_method == 'boleto' ) :
				?>
				<p>
					<?php _e( 'BOLETO: If you have not yet received the boleto, please click the button below to print.', Core::TEXTDOMAIN ); ?>
				</p>

				<a href="<?php echo esc_url( $charge->last_transaction->pdf ); ?>" target="_blank" class="payment-link">
					<?php _e( 'Print', Core::TEXTDOMAIN ); ?>
				</a>
				<?php
			endif;

		endforeach;

		echo self::message_after();

		$message = ob_get_contents();

		ob_end_clean();

		return $message;
	}

	public static function render_payment_details( $order_id )
	{
		$order   = new Order( $order_id );
		$charges = $order->get_charges();

		if ( ! $charges ) {
			$charges = isset( $order->response_data->charges ) ? $order->response_data->charges : false;
		}
		
		if ( empty( $charges ) ) {
			return;
		}

		$model_charge = new Charge();

		?>
		<section>
			<h2><?php _e( 'Payment Data', Core::TEXTDOMAIN ); ?></h2>
			<table class="woocommerce-table">
			<?php
				foreach ( $charges as $charge ) {
					echo self::_get_payment_detail( $charge, $model_charge );
				}
			?>
			</table>
		</section>
		<?php
	}

	public static function render_installments( $wc_order )
	{
		$gateway = new Gateway();
		$total   = $wc_order->get_total();

		echo $gateway->get_installments_by_type( $total );
	}

	private static function _get_payment_detail( $charge, Charge $model_charge )
	{
		if ( $charge->payment_method == 'boleto' ) {

			$due_at = new \DateTime( $charge->last_transaction->due_at );

			ob_start()

			?>
			<tr>
				<th><?php _e( 'Payment Type', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php _e( 'Boleto', Core::TEXTDOMAIN ); ?></td>
			</tr>
			<tr>
				<th>Link:</th>
				<td>
					<a href="<?php echo $charge->last_transaction->pdf; ?>">
						<?php echo $charge->last_transaction->pdf; ?>
					</a>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Line Code', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $charge->last_transaction->line; ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Due at', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $due_at->format( 'd/m/Y' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Paid value', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Status', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $model_charge->get_i18n_status( $charge->status ); ?></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<?php

			$html = ob_get_contents();

			ob_end_clean();
		}

		if ( $charge->payment_method == 'credit_card' ) {

			ob_start()

			?>
			<tr>
				<th><?php _e( 'Payment Type', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php _e( 'Credit Card', Core::TEXTDOMAIN ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Card Holder Name', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $charge->last_transaction->card->holder_name; ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Flag', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $charge->last_transaction->card->brand; ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Card number', Core::TEXTDOMAIN ); ?>:</th>
				<td>
					**** **** **** <?php echo $charge->last_transaction->card->last_four_digits; ?>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Installments', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $charge->last_transaction->installments; ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Paid value', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Status', Core::TEXTDOMAIN ); ?>:</th>
				<td><?php echo $model_charge->get_i18n_status( $charge->status ); ?></td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<?php

			$html = ob_get_contents();

			ob_end_clean();
		}

		return $html;
	}
}
