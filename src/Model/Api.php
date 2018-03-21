<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Exception;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Resource\Customers;
use Woocommerce\Mundipagg\Resource\Orders;

use WC_Order;

class Api
{
	public static $instance = null;
	public $debug = false;
	public $settings;

	private function __construct()
	{
		$this->settings = Setting::get_instance();
		$this->debug    = $this->settings->is_enabled_logs();
	}

	public function create_customer( WC_Order $wc_order )
	{
		$customers = new Customers();

		try {
			$model    = new Order( $wc_order->get_order_number() );
			$document = $this->_get_document_by_person_type( $model );
			$address  = array(
				"street"       => $model->billing_address_1,
				"number"       => $model->billing_number,
				"complement"   => $model->billing_address_2,
				"zip_code"     => preg_replace( '/[^\d]+/', '', $model->billing_postcode ),
				"neighborhood" => $model->billing_neighborhood,
				"city"         => $model->billing_city,
				"state"        => $model->billing_state,
				"country"      => "BR",
			);

			$params = array(
				'name'     => "{$model->billing_first_name} {$model->billing_last_name}",
				'email'    => $model->billing_email,
				'document' => Utils::format_document( $document['value'] ),
				'type'     => $document['type'],
				'address'  => $address
			);

			$response = $customers->create( $params );

			if ( $this->debug ) {
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE CUSTOMER REQUEST: ' . print_r( $params, true ) );
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE CUSTOMER RESPONSE: ' . print_r( $response->body, true ) );
			}

			return $response->body;

		} catch ( Exception $e ) {
			if ( $this->debug ) {
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE CUSTOMER ERROR: ' . $e->__toString() );
			}
			error_log( $e->__toString() );
		    return null;
		}
	}

	public function create_order( WC_Order $wc_order, $payment_method, $form_fields )
	{
		$customer = $this->create_customer( $wc_order );

		if ( ! $customer ) {
			return;
		}

		try {
			$wc_order_id = intval( $wc_order->get_order_number() );
			$orders      = new Orders();
			$payment     = new Payment( $payment_method );
			$items       = $this->_build_order_items( $wc_order, $form_fields, $payment );
			$payments    = $payment->get_payment_data( $wc_order, $form_fields, $customer );
			$shipping    = $this->_build_shipping( $wc_order );
			$amount      = $this->_get_amount_total( $payments );

			if ( ! is_array( $payments ) ) {
				return $payments;
			}

			$params = array(
				'amount'            => $amount,
				'code'              => $wc_order_id,
				'items'             => $items,
				'customer'          => $customer,
				'shipping'          => $shipping,
				'payments'          => $payments,
				'antifraud_enabled' => $this->is_enabled_antifraud( $wc_order, $payment_method )
			);

			$response = $orders->create( $params );

			if ( $this->debug ) {
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE ORDER REQUEST: ' . print_r( $params, true ) );
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE ORDER RESPONSE: ' . print_r( $response->body, true ) );
			}

			return $response;

		} catch ( Exception $e ) {
			if ( $this->debug ) {
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE ORDER ERROR: ' . $e->__toString() );
			}
			error_log( $e->__toString() );
		    return null;
		}
	}

	public function is_enabled_antifraud( WC_Order $wc_order, $payment_method )
	{
		if ( $payment_method == 'billet' ) {
			return false;
		}

		if ( $this->settings->antifraud_enabled != 'yes' ) {
			return false;
		}

		if ( ! $min_value = $this->settings->antifraud_min_value ) {
			return false;
		}
		
		$min_value = Utils::format_desnormalized_order_price( $min_value );
		$total     = Utils::format_order_price( $wc_order->get_total() );

		if ( $total < $min_value ) {
			return false;
		}

		return true;
	}

	private function _build_shipping( WC_Order $wc_order )
	{
		$method = $wc_order->get_shipping_method();
		$order  = new Order( $wc_order->get_order_number() );
		
		if ( ! $method ) {
			$method = "Não informado";
		}

		$total    = Utils::format_order_price( $wc_order->get_total_shipping() );
		$shipping = $order->get_shipping_info();

		return array(
			"amount"          => $total,
			"description"     => $method,
			"address"         => array(
				"street"       => $shipping['address_1'],
				"number"       => $shipping['number'],
				"complement"   => $shipping['address_2'],
				"zip_code"     => preg_replace( '/[^\d]+/', '', $shipping['postcode'] ),
				"neighborhood" => $shipping['neighborhood'],
				"city"         => $shipping['city'],
				"state"        => $shipping['state'],
				"country"      => "BR"
			)
		);
	}

	private function _build_order_items( WC_Order $wc_order, $form_fields, Payment $payment )
	{
		$items = $wc_order->get_items();

		if ( ! $items ) {
			return;
		}

		$installments = Utils::get_value_by( $form_fields, 'installments' );

		foreach ( $items as $item ) {
			$product     = $wc_order->get_product_from_item( $item );
			$price       = $product->get_price();
			$quantity    = absint( $item['qty'] );
			$description = sanitize_title( $item['name'] ) . ' x ' . $quantity;
			$amount      = Utils::format_order_price( $price );
			$data[]      = compact( 'amount', 'description', 'quantity' );
		}

		return $data;
	}

	private function _get_document_by_person_type( $order )
	{
		$wcbcf_options = get_option( 'wcbcf_settings' ); //WooCommerce Extra Checkout Fields
		
		$cpf = array(
			'type' => 'individual',
			'value' => $order->billing_cpf
		);
		$cnpj = array(
			'type' => 'company',
			'value' => $order->billing_cnpj
		);

		switch ( $wcbcf_options['person_type'] ) {
			case 1:
				return ( $order->billing_persontype == 1 ) ? $cpf : $cnpj;
			case 2:
				return $cpf;
			case 3:
				return $cnpj;
			default:
				return array( 'type' => '', 'value' => '' );
		}
	}

	private function _get_amount_total( $payments )
	{
		$total = 0;

		foreach( $payments as $key => $value ) {
     		$total += $value['amount'];
		}
		 
		return $total;
	}

	public static function get_instance()
	{
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
