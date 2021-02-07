<?php
namespace Woocommerce\Pagarme\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Charge;
use Woocommerce\Pagarme\Model\Setting;

class Orders
{
	public static function render_capture_metabox( $post )
	{
		$model        = new Order( $post->ID );
		$charges      = $model->get_charges( true );
		$charge_model = new Charge();

		if ( empty( $charges ) ) {
			echo '<p>Nenhum registro encontrado.</p>';
			return;
		}

		?>
		<style>
			.modal { display:none; }
			tbody.items { font-size: 11px; }
		</style>
		<div class="wrapper">
			<table cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th>Charge ID</th>
						<th>Tipo</th>
						<th>Valor Total</th>
						<th>Parcialmente Capturado</th>
						<th>Parcialmente Cancelado</th>
						<th>Status</th>
						<th class="action">Ação</th>
					</tr>
				</thead>
				<tbody class="items">
				<?php foreach ( $charges as $item ) : ?>
					<tr <?php echo Utils::get_component( 'capture' ); ?>>
						<?php
							$charge = maybe_unserialize( $item->charge_data );
							$paid_amount = isset( $charge->paid_amount ) ? Utils::format_order_price_to_view( $charge->paid_amount ) : ' - ';
							$canceled_amount = isset( $charge->canceled_amount ) ? Utils::format_order_price_to_view( $charge->canceled_amount ) : ' - ';
						?>
						<td><?php echo $item->charge_id; ?></td>
						<td><?php echo strtoupper( $charge->payment_method ); ?></td>
						<td><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></td>
						<td><?php echo $paid_amount; ?></td>
						<td><?php echo $canceled_amount; ?></td>
						<td><?php echo strtoupper( $item->charge_status ); ?></td>
						<td style="width:150px; padding-top:12px; text-align:center;">
							<button data-type="cancel"
									data-ref="<?php echo $item->charge_id; ?>"
									<?php echo ! $charge_model->is_allowed_cancel( $item ) ? 'disabled=disabled' : ''; ?>
									class="button-primary">Cancelar</button>

							<?php if ( $charge->payment_method == 'credit_card' ) : ?>
							<button data-type="capture"
									data-ref="<?php echo $item->charge_id; ?>"
									<?php echo ! $charge_model->is_allowed_capture( $item ) ? 'disabled=disabled' : ''; ?>
									class="button-primary">Capturar</button>
							<?php endif; ?>
						</td>
					<?php self::render_capture_modal( $item, $charge ); ?>
					<?php self::render_cancel_modal( $item, $charge ); ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	private static function render_capture_modal( $item, $charge )
	{
		$paid_amount = isset( $charge->paid_amount ) ? Utils::format_order_price_to_view( $charge->paid_amount ) : ' - ';

		?>
		<div data-charge-action="<?php echo $item->charge_id; ?>-capture" data-charge="<?php echo $item->charge_id; ?>" class="modal">
			<h2>Pagarme - Captura</h2>
			<p><b>CHARGE ID: </b><?php echo $item->charge_id; ?></p>
			<p><b>TIPO: </b><?php echo strtoupper( $charge->payment_method ); ?></p>
			<p><b>VALOR TOTAL: </b><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></p>
			<p><b>PARCIALMENTE CAPTURADO: </b><?php echo $paid_amount; ?></p>
			<p><b>STATUS: </b><?php echo strtoupper( $item->charge_status ); ?></p>
			<p>
				<label>Valor a ser capturado:
					<input data-element="amount" type="text"/>
				</label>
			</p>
			<p>
				<button class="button-secondary" data-action="capture">Confirmar</button>
				<button class="button-secondary" data-iziModal-close>Cancelar</button>
			</p>
		</div>
		<?php
	}

	private static function render_cancel_modal( $item, $charge )
	{
		$canceled_amount = isset( $charge->canceled_amount ) ? $charge->canceled_amount : 0;
		$paid_amount     = isset( $charge->paid_amount ) ? $charge->paid_amount : 0;
		$value_to_cancel = $charge->amount;

		if ( $paid_amount ) {
			$value_to_cancel = $paid_amount;
		}

		if ( $paid_amount && $canceled_amount ) {
			$value_to_cancel = $paid_amount - $canceled_amount;
		}

		?>
		<div data-charge-action="<?php echo $item->charge_id; ?>-cancel" data-charge="<?php echo $item->charge_id; ?>" class="modal">
			<h2>Pagarme - Cancelamento</h2>
			<p><b>CHARGE ID: </b><?php echo $item->charge_id; ?></p>
			<p><b>TIPO: </b><?php echo strtoupper( $charge->payment_method ); ?></p>
			<p><b>VALOR TOTAL: </b><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></p>
			<p><b>PARCIALMENTE CANCELADO: </b><?php echo $canceled_amount ? Utils::format_order_price_to_view( $canceled_amount ) : '-'; ?></p>
			<p><b>STATUS: </b><?php echo strtoupper( $item->charge_status ); ?></p>
			<p>
				<label>Valor a ser cancelado:
					<input data-element="amount" type="text" value="<?php echo $value_to_cancel; ?>"
					<?php echo $item->charge_status == 'pending' ? 'disabled=disabled' : ''; ?>/>
				</label>
			</p>
			<p>
				<button class="button-secondary" data-action="cancel">Confirmar</button>
				<button class="button-secondary" data-iziModal-close>Cancelar</button>
			</p>
		</div>
		<?php
	}
}
