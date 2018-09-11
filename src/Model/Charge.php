<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Setting;

class Charge
{
	/**
	 * The table name.
	 */
	const TABLE = 'woocommerce_mundipagg_charges';

	public function insert( array $data )
	{
		global $wpdb;

		return $wpdb->insert(
			$this->get_table_name(),
			array(
				'wc_order_id'   => intval( $data['wc_order_id'] ),
				'order_id'      => esc_sql( $data['order_id'] ),
				'charge_id'     => esc_sql( $data['charge_id'] ),
				'charge_data'   => maybe_serialize( $data['charge_data'] ),
				'charge_status' => esc_sql( $data['charge_status'] ),
			)
		);
	}

	public function update( array $fields, array $where )
	{
		global $wpdb;

		return $wpdb->update(
			$this->get_table_name(),
			$fields,
			$where
		);
	}

	/** phpcs:disable */
	public function is_exists( $charge_id )
	{
		global $wpdb;

		$table = $this->get_table_name();
		$query = $wpdb->prepare(
			"SELECT
				`id`
			FROM
				`{$table}`
			WHERE
				`charge_id` = %s
			",
			esc_sql( $charge_id )
		);

		return (int) $wpdb->get_var( $query );
	}
	/** phpcs:enable */

	public function create_from_webhook( $webhook_data )
	{
		if ( ! $webhook_data ) {
			return;
		}

		if ( ! $this->is_exists( $webhook_data->data->id ) ) {
			return $this->insert([
				'wc_order_id'   => $webhook_data->data->order->code,
				'order_id'      => $webhook_data->data->order->id,
				'charge_id'     => $webhook_data->data->id,
				'charge_data'   => $webhook_data->data,
				'charge_status' => $webhook_data->data->status,
			]);
		}

		$this->update(
			array(
				'charge_status' => esc_sql( $webhook_data->data->status ),
				'charge_data'   => maybe_serialize( $webhook_data->data ),
			),
			array(
				'charge_id' => esc_sql( $webhook_data->data->id ),
			)
		);
	}

	public function create_from_order( $order_id, $charges )
	{
		if ( empty( $charges ) ) {
			return;
		}

		foreach ( $charges as $charge ) {
			if ( ! $this->is_exists( $charge->id ) ) {
				$this->insert([
					'wc_order_id'   => $charge->code,
					'order_id'      => $order_id,
					'charge_id'     => $charge->id,
					'charge_data'   => $charge,
					'charge_status' => $charge->status,
				]);
			} else {
				$this->update(
					array(
						'charge_status' => esc_sql( $charge->status ),
						'charge_data'   => maybe_serialize( $charge ),
					),
					array(
						'charge_id' => esc_sql( $charge->id ),
					)
				);
			}
		}
	}

	/** phpcs:disable */
	public function find_by_wc_order( $wc_order_id )
	{
		global $wpdb;

		if ( ! $wc_order_id ) {
			return false;
		}

		$table = $this->get_table_name();
		$query = $wpdb->prepare(
			"SELECT
				id, wc_order_id, order_id, charge_id, charge_data, charge_status, updated_at
			 FROM
				`{$table}`
			WHERE
				`wc_order_id` = %d
			",
			intval( $wc_order_id )
		);

		return $wpdb->get_results( $query );
	}
	/** phpcs:enable */

	public function get_i18n_status( $status )
	{
		if ( get_locale() != 'pt_BR' ) {
			return ucfirst( $status );
		}

		$list = array(
			'pending'    => 'pendente',
			'paid'       => 'pago',
			'canceled'   => 'cancelado',
			'processing' => 'processando',
			'failed'     => 'falhou',
		);

		$status = strtolower( $status );

		return ucfirst( isset( $list[ $status ] ) ? $list[ $status ] : $status );
	}

	public function is_allowed_capture( $charge )
	{
		$data = maybe_unserialize( $charge->charge_data );

		if ( $data->payment_method == 'boleto' ) {
			return false;
		}

		if ( $charge->charge_status == 'pending' ) {
			return true;
		}

		return false;
	}

	public function is_allowed_cancel( $charge )
	{
		$data   = maybe_unserialize( $charge->charge_data );
		$status = $charge->charge_status;
		$method = $data->payment_method;

		if ( $method == 'boleto' && in_array( $status, [ 'pending' ] ) ) {
			return true;
		}

		if ( $method == 'credit_card' && in_array( $status, [ 'pending', 'paid' ] ) ) {
			return true;
		}

		return false;
	}

	public function get_table_name()
	{
		global $wpdb;

		return $wpdb->prefix . self::TABLE;
	}
}
