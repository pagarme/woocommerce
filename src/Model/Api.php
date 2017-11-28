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
	public $settings;

	private function __construct()
	{
		$this->settings = Setting::get_instance();
	}

	public function create_customer( WC_Order $wc_order )
	{
		$customers = new Customers();

		try {
			$model       = new Order( $wc_order->get_order_number() );
			$person_type = $model->billing_persontype;
			$document    = ( $person_type == 1 ) ? $model->billing_cpf : $model->billing_cnpj;

			$address = array(
				"street"       => $model->billing_address_1,
				"number"       => $model->billing_number,
				"complement"   => $model->billing_address_2,
				"zip_code"     => preg_replace( '/[^\d]+/', '', $model->billing_postcode ),
				"neighborhood" => $model->billing_neighborhood,
				"city"         => $model->billing_city,
				"state"        => $model->billing_state,
				"country"      => "BR",
			);

			$response = $customers->create([
				'name'     => "{$model->billing_first_name} {$model->billing_last_name}",
				'email'    => $model->billing_email,
				'document' => Utils::format_document( $document ),
				'type'     => $person_type == 1 ? 'individual' : 'company',
				'address'  => $address
			]);

			return $response->body;

		} catch ( Exception $e ) {
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
			$items       = $this->_build_order_items( $wc_order, $form_fields );
			$payments    = $payment->get_payment_data( $form_fields, $customer );

			if ( ! is_array( $payments ) ) {
				return $payments;
			}

			return $orders->create([
				'code'              => $wc_order_id,
				'items'             => $items,
				'customer'          => $customer,
				'payments'          => $payments,
				'antifraud_enabled' => $this->is_enabled_antifraud( $wc_order, $payment_method )
			]);

		} catch ( Exception $e ) {
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
		
		$total     = Utils::format_order_price( $wc_order->get_total() );
		$min_value = Utils::format_order_price( $min_value );

		if ( $total < $min_value ) {
			return false;
		}

		return true;
	}

	private function _build_order_items( WC_Order $wc_order, $form_fields )
	{
		$items = $wc_order->get_items();

		if ( ! $items ) {
			return;
		}

		foreach ( $items as $item ) {
			$product     = $wc_order->get_product_from_item( $item );
			$price       = $this->_get_price_with_interest( $product->get_price(), Utils::get_value_by( $form_fields, 'installments' ) );
			$quantity    = absint( $item['qty'] );
			$description = sanitize_title( $item['name'] ) . ' x ' . $quantity;
			$amount      = Utils::format_order_price( $price );
			$data[]      = compact( 'amount', 'description', 'quantity' );
		}

		return $data;
	}

	private function _get_price_with_interest( $price, $installments )
	{
		$amount            = $price;
		$max_installments  = intval( $this->settings->cc_installments_maximum );
		$no_interest       = intval( $this->settings->cc_installments_without_interest );
		$interest          = Utils::str_to_float( $this->settings->cc_installments_interest );
		$interest_increase = Utils::str_to_float( $this->settings->cc_installments_interest_increase );

		if ( $installments <= $no_interest ) {
			return $amount;
		}

		if ( $interest ) {

			if ( $interest_increase && $installments > $no_interest + 1 ) {
				$interest += ( $interest_increase * ( $installments - ( $no_interest + 1 ) ) );
			}

			$amount += Utils::calc_percentage( $interest, $price );
		}

		return $amount;
	}

	public static function get_instance()
	{
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
