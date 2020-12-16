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
	public $debug           = false;
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
			$document = $this->get_document_by_person_type( $model );
			$address  = array(
				'street'       => substr($model->billing_address_1, 0, 64),
				'number'       => substr($model->billing_number, 0, 15),
				'complement'   => substr($model->billing_address_2, 0, 64),
				'zip_code'     => preg_replace( '/[^\d]+/', '', $model->billing_postcode ),
				'neighborhood' => substr($model->billing_neighborhood, 0, 64),
				'city'         => substr($model->billing_city, 0, 64),
				'state'        => substr($model->billing_state, 0, 2),
				'country'      => 'BR',
			);

			$name = "{$model->billing_first_name} {$model->billing_last_name}";

			$params = array(
				'name'     => substr($name, 0, 64),
				'email'    => substr($model->billing_email, 0, 64),
				'document' => substr(Utils::format_document( $document['value'] ), 0, 16),
				'type'     => $document['type'],
				'address'  => $address,
				'phones'   => $this->get_phones( $model ),
			);

			$response = $customers->create( $params );

			if (!empty($this->settings)) {
				$this->settings->log()->add('woo-mundipagg', 'CREATE CUSTOMER REQUEST: ' . json_encode($params, JSON_PRETTY_PRINT));
				$this->settings->log()->add('woo-mundipagg', 'CREATE CUSTOMER RESPONSE: ' . json_encode($response->body, JSON_PRETTY_PRINT));
			}

			return $response->body;

		} catch ( Exception $e ) {
			if (!empty($this->settings)) {
				$this->settings->log()->add( 'woo-mundipagg', 'CREATE CUSTOMER ERROR: ' . $e->__toString() );
			}
			error_log( $e->__toString() );
			return null;
		}
	}

	public function create_order( WC_Order $wc_order, $payment_method, $form_fields )
	{
        $file = 'woo-mundipagg';
		$customer = $this->create_customer($wc_order);

		if ( ! $customer ) {
			return;
		}

		try {
			$wc_order_id = intval( $wc_order->get_order_number() );
			$orders      = new Orders();
			$payment     = new Payment( $payment_method );
			$items       = $this->build_order_items( $wc_order, $form_fields );
			$payments    = $payment->get_payment_data( $wc_order, $form_fields, $customer );
			$shipping    = $this->build_shipping( $wc_order );
			$amount      = $this->get_amount_total( $payments );


			if ( ! is_array( $payments ) ) {
				return $payments;
			}

			$hash = $customer->document;

			$idempotencyKey = md5("{$hash}-{$wc_order_id}");

			$params = array(
				'amount'            => $amount,
				'code'              => $wc_order_id,
				'items'             => $items,
				'customer'          => $customer,
				'shipping'          => $shipping,
				'payments'          => $payments,
				'antifraud_enabled' => $this->is_enabled_antifraud( $wc_order, $payment_method ),
				'idempotencyKey'    => $idempotencyKey
			);

            if (!empty($this->settings)) {
                $previous_status = $wc_order->get_status();

                //LOG ORDER REQUEST
                $this->settings->log()->add(
                    $file,
                    'CREATE ORDER REQUEST: #' .
                    $wc_order_id .
                    json_encode($params, JSON_PRETTY_PRINT)
                );
            }
			$response = $orders->create($params);

			if (!empty($this->settings)) {

			    //LOG ORDER RESPONSE
				$this->settings->log()->add(
				    $file,
                    'CREATE ORDER RESPONSE: #' .
                    $wc_order_id .
                    json_encode($response->body, JSON_PRETTY_PRINT));

                $statusArray = [
                    'previous_status' => $previous_status,
                    'new_status' => $wc_order->get_status()
                ];

				$message =
                    'ORDER STATUS UPDATE: #' .
                    $wc_order_id .
                    json_encode($statusArray, JSON_PRETTY_PRINT);

				//LOG ORDER STATUS CHANGE
                $this->settings->log()->add($file, $message);
			}

			return $response;

		} catch ( Exception $e ) {
			if (!empty($this->settings)) {
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
		/** phpcs:disable */
		if ( ! $min_value = $this->settings->antifraud_min_value ) {
			return false;
		}
		/** phpcs:enable */

		$min_value = Utils::format_desnormalized_order_price( $min_value );
		$total     = Utils::format_order_price( $wc_order->get_total() );

		if ( $total < $min_value ) {
			return false;
		}

		return true;
	}

	private function build_shipping( WC_Order $wc_order )
	{
		$method = $wc_order->get_shipping_method();
		$order  = new Order( $wc_order->get_order_number() );

		if ( ! $method ) {
			$method = 'Não informado';
		}

		$total    = Utils::format_order_price( $wc_order->get_total_shipping() );
		$shipping = $order->get_shipping_info();

		return array(
			'amount'      => $total,
			'description' => $method,
			'address'     => array(
				'street'       => substr($shipping['address_1'], 0, 64),
				'number'       => substr($shipping['number'], 0, 15),
				'complement'   => substr($shipping['address_2'], 0, 64),
				'zip_code'     => substr(preg_replace( '/[^\d]+/', '', $shipping['postcode'] ), 0, 16),
				'neighborhood' => substr($shipping['neighborhood'], 0, 64),
				'city'         => substr($shipping['city'], 0, 64),
				'state'        => substr($shipping['state'], 0, 2),
				'country'      => 'BR',
			),
		);
	}

	private function build_order_items( WC_Order $wc_order, $form_fields )
	{
		$items = $wc_order->get_items();

		if ( ! $items ) {
			return;
		}

		$installments = Utils::get_value_by( $form_fields, 'installments' );

		foreach ( $items as $item ) {
			$product     = $item->get_product();
			$price       = $product->get_price();
			$quantity    = absint( $item['qty'] );
			$description = sanitize_title( $item['name'] ) . ' x ' . $quantity;
			$amount      = Utils::format_order_price( $price );
			$code        = $item['product_id'];
			$data[]      = compact( 'amount', 'description', 'quantity', 'code' );
		}

		return $data;
	}

	private function get_document_by_person_type( $order )
	{
		$wcbcf_options = get_option( 'wcbcf_settings' ); //WooCommerce Extra Checkout Fields

		$cpf = array(
			'type'  => 'individual',
			'value' => $order->billing_cpf,
		);

		$cnpj = array(
			'type'  => 'company',
			'value' => $order->billing_cnpj,
		);

		switch ( $wcbcf_options['person_type'] ) {
			case 1:
				return ( $order->billing_persontype == 1 ) ? $cpf : $cnpj;
			case 2:
				return $cpf;
			case 3:
				return $cnpj;
			default:
				return array(
					'type'  => '',
					'value' => '',
				);
		}
	}

	private function get_phones( $order )
	{
		$phones    = array();
		$phone     = $order->billing_phone;
		$cellphone = $order->billing_cellphone;

		if ( $phone ) {
			$pieces               = Utils::format_phone_number( $phone );
			$phones['home_phone'] = array(
				'country_code' => $this->get_phone_country_code( $order->billing_country ),
				'area_code'    => isset( $pieces[0] ) ? $pieces[0] : '',
				'number'       => isset( $pieces[1] ) ? $pieces[1] : '',
			);
		}

		if ( $cellphone ) {
			$pieces                 = Utils::format_phone_number( $cellphone );
			$phones['mobile_phone'] = array(
				'country_code' => $this->get_phone_country_code( $order->billing_country ),
				'area_code'    => isset( $pieces[0] ) ? $pieces[0] : '',
				'number'       => isset( $pieces[1] ) ? $pieces[1] : '',
			);
		} else {
			$phones['mobile_phone'] = $phones['home_phone'];
		}

		return $phones;
	}

	private function get_phone_country_code( $country )
	{
		$list = array(
			'BR' => 55,
			'US' => 1,
			'UK' => 44,
			'ES' => 34,
		);

		return isset( $list[ $country ] ) ? $list[ $country ] : 55;
	}

	private function get_amount_total( $payments )
	{
		$total = 0;

		foreach ( $payments as $key => $value ) {
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
