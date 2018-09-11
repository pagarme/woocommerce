<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Charge;

// WooCommerce
use WC_Order;

class Order extends Meta
{
	protected $response_data;
	protected $payment_method;
	protected $mundipagg_status;
	protected $mundipagg_id;
	protected $wc_order;

	// == BEGIN WC ORDER ==
	protected $billing_persontype;
	protected $billing_cnpj;
	protected $billing_first_name;
	protected $billing_last_name;
	protected $billing_email;
	protected $billing_birthdate;
	protected $billing_country;
	protected $billing_phone;
	protected $billing_cellphone;
	protected $billing_address_1;
	protected $billing_address_2;
	protected $billing_number;
	protected $billing_neighborhood;
	protected $billing_city;
	protected $billing_state;
	protected $billing_postcode;
	protected $billing_cpf;
	protected $shipping_address_1;
	protected $shipping_number;
	protected $shipping_address_2;
	protected $shipping_postcode;
	protected $shipping_neighborhood;
	protected $shipping_city;
	protected $shipping_state;
	// == END WC ORDER ==

	public $with_prefix = array(
		'payment_method'   => 1,
		'response_data'    => 1,
		'mundipagg_status' => 1,
		'mundipagg_id'     => 1,
	);

	/** phpcs:disable */
	public function __construct( $ID = false )
	{
		parent::__construct( $ID );

		$this->wc_order = new WC_Order( $this->ID );
	}
	/** phpcs:enable */

	public function get_status_translate()
	{
		$status = strtolower( $this->__get( 'mundipagg_status' ) );
		$texts  = array(
			'paid'     => __( 'Paid', 'woo-mundipagg-payments' ),
			'pending'  => __( 'Pending', 'woo-mundipagg-payments' ),
			'canceled' => __( 'Canceled', 'woo-mundipagg-payments' ),
			'failed'   => __( 'Failed', 'woo-mundipagg-payments' ),
		);

		return isset( $texts[ $status ] ) ? $texts[ $status ] : false;
	}

	public function payment_on_hold()
	{
		$current_status = $this->wc_order->get_status();

		if ( ! in_array( $current_status, [ 'on-hold', 'completed', 'canceled', 'cancelled', 'processing' ] ) ) {
			$this->wc_order->update_status( 'on-hold', __( 'MundiPagg: Awaiting payment confirmation.', 'woo-mundipagg-payments' ) );
			wc_reduce_stock_levels( $this->wc_order->get_order_number() );
		}
	}

	public function payment_paid()
	{
		$current_status = $this->wc_order->get_status();

		if ( ! in_array( $current_status, [ 'completed', 'processing' ] ) ) {
			$this->wc_order->add_order_note( __( 'Mundipagg: Payment has already been confirmed.', 'woo-mundipagg-payments' ) );
			$this->wc_order->payment_complete();
		}
	}

	public function payment_canceled()
	{
		$current_status = $this->wc_order->get_status();

		if ( ! in_array( $current_status, [ 'cancelled', 'canceled' ] ) ) {
			$this->wc_order->update_status( 'cancelled', __( 'Mundipagg: Payment canceled.', 'woo-mundipagg-payments' ) );
		}
	}

	public function update_by_mundipagg_status( $mundipagg_status )
	{
		switch ( $mundipagg_status ) {
			case 'pending':
				$this->payment_on_hold();
				break;
			case 'paid':
				$this->payment_paid();
				break;
			case 'failed':
				$this->payment_canceled();
				break;
			case 'canceled':
				$this->payment_canceled();
				break;
		}
	}

	public function get_charges( $full_data = false )
	{
		$model = new Charge();
		$items = $model->find_by_wc_order( $this->ID );

		if ( ! $items ) {
			return false;
		}

		if ( $full_data ) {
			return $items;
		}

		$list = [];

		foreach ( $items as $item ) {
			$charge = new \stdClass();
			$charge = maybe_unserialize( $item->charge_data );
			$list[] = $charge;
		}

		return $list;
	}

	/**
	 * Returns the shipping data. If it is empty then returns billing data as fallback.
	 *
	 * @return array
	 */
	public function get_shipping_info()
	{
		return array(
			'address_1'    => $this->handle_shipping_properties( 'address_1' ),
			'number'       => $this->handle_shipping_properties( 'number' ),
			'address_2'    => $this->handle_shipping_properties( 'address_2' ),
			'postcode'     => $this->handle_shipping_properties( 'postcode' ),
			'neighborhood' => $this->handle_shipping_properties( 'neighborhood' ),
			'city'         => $this->handle_shipping_properties( 'city' ),
			'state'        => $this->handle_shipping_properties( 'state' ),
		);
	}

	private function handle_shipping_properties( $prop )
	{
		$shipping_prop = $this->__get( "shipping_{$prop}" );

		if ( empty( $shipping_prop ) ) {
			return $this->__get( "billing_{$prop}" );
		}

		return $shipping_prop;
	}
}
