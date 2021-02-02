<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Model\Api;
use Woocommerce\Mundipagg\Model\Order;
use Woocommerce\Mundipagg\Model\Customer;
use Woocommerce\Mundipagg\Model\Gateway;
use Woocommerce\Mundipagg\Model\Charge;
use Woocommerce\Mundipagg\Model\Setting;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\View;
use Woocommerce\Mundipagg\Model;

use WC_Order;

class Checkout
{
	protected $cards = array();

	public function __construct()
	{
		$this->api = Api::get_instance();

		add_action( 'woocommerce_api_' . Model\Checkout::API_REQUEST, array( $this, 'process_checkout_transparent' ) );
		add_action( 'woocommerce_view_order', array( 'Woocommerce\Mundipagg\View\Checkouts', 'render_payment_details' ) );
		add_action( 'wp_ajax_xqRhBHJ5sW', array( $this, 'build_installments' ) );
		add_action( 'wp_ajax_nopriv_xqRhBHJ5sW', array( $this, 'build_installments' ) );
		add_filter( 'wcbcf_billing_fields', array( $this, 'set_required_fields' ) );
	}

	public function process_checkout_transparent()
	{
		if ( ! Utils::is_request_ajax() || Utils::server( 'REQUEST_METHOD' ) !== 'POST' ) {
			exit( 0 );
		}

		$wc_order = wc_get_order( Utils::post( 'order', 0, 'intval' ) );

		if ( ! $wc_order ) {
			wp_send_json_error( __( 'Invalid order', 'woo-mundipagg-payments' ) );
		}

		$fields = $this->prepare_fields( $_POST['fields'] );

		if ( empty( $fields ) ) {
			wp_send_json_error( __( 'Empty fields', 'woo-mundipagg-payments' ) );
		}

		$this->validate_amount_billet_and_card( $fields, $wc_order );
		$this->validate_amount_2_cards( $fields, $wc_order );
		$this->validate_brands( $fields );

		$response = $this->api->create_order(
			$wc_order,
			$fields['payment_method'],
			$fields
		);

		if ( ! $response || $response->code != 200 ) {
			wp_send_json_error( Utils::get_errors( $response->body->errors ) );
		}

		if ( (int) Utils::get_value_by( $fields, 'save_credit_card' ) === 1 ) {
			$this->save_customer_card( $response->raw_body, 1 );
		}

		if ( (int) Utils::get_value_by( $fields, 'save_credit_card2' ) === 1 ) {
			$this->save_customer_card( $response->raw_body, 2 );
		}

		$order  = new Order( $wc_order->get_order_number() );
		$charge = new Charge();

		$order->payment_method   = $fields['payment_method'];
		$order->mundipagg_id     = $response->body->id;
		$order->mundipagg_status = $response->body->status;
		$order->response_data    = $response->body;

		$order->update_by_mundipagg_status( $response->body->status );
		$charge->create_from_order( $response->body->id, $response->body->charges );

		WC()->cart->empty_cart();

		wp_send_json_success( $response->body );
	}

	public function build_installments()
	{
		if ( ! Utils::is_request_ajax() || Utils::server( 'REQUEST_METHOD' ) !== 'GET' ) {
			exit( 0 );
		}

		$flag  = Utils::get( 'flag', false, 'esc_html' );
		$total = Utils::get( 'total', false );

		$gateway = new Gateway();
		$html    = $gateway->get_installments_by_type( $total, $flag );

		echo $html;

		exit();
	}

	public function set_required_fields( $fields )
	{
		$fields['billing_neighborhood']['required'] = true;

		return $fields;
	}

	public function parse_cards( $data, $key = 'card' )
	{
		if ( isset( $data[ $key ] ) ) {
			$this->cards[ $data[ $key ]['id'] ] = $data[ $key ];
		}

		foreach ( $data as &$value ) :
			if ( is_array( $value ) ) {
				$this->parse_cards( $value, $key );
			}
		endforeach;
	}

	private function save_customer_card( $raw_body, $index )
	{
		$customer = new Customer( get_current_user_id() );
		$body     = json_decode( $raw_body, true );
		$cards    = $customer->cards;
		$count    = 1;

		$this->parse_cards( $body );

		foreach ( $this->cards as $card_id => $card ) {

			if ( $count === $index ) {
				if ( ! array_key_exists( $card_id, $cards ) ) {
					$cards[ $card_id ] = $card;
				}
			}

			$count++;
		}

		$customer->cards = $cards;
	}

	private function prepare_fields( $form_data )
	{
		if ( empty( $form_data ) ) {
			return false;
		}

		$fields = array();

		foreach ( $form_data as $data ) {
			if ( ! isset( $data['name'] ) || ! isset( $data['value'] ) ) {
				continue;
			}

			if ( empty( $data['value'] ) ) {
				continue;
			}

			$fields[ $data['name'] ] = Utils::rm_tags( $data['value'], true );

			if ( $data['name'] == 'card_number' || $data['name'] == 'card_number2' ) {
				$fields[ $data['name'] ] = Utils::format_document( $data['value'] );
			}

			if ( $data['name'] == 'card_expiry' ) {
				$this->prepare_expiry_field( $data, $fields );
			}

			if ( $data['name'] == 'card_expiry2' ) {
				$this->prepare_expiry_field( $data, $fields, 2 );
			}
		}

		return $fields;
	}

	private function prepare_expiry_field( $data, &$fields, $sufix = '' )
	{
		$expiry_pieces                         = explode( '/', $data['value'] );
		$fields[ "card_expiry_month{$sufix}" ] = trim( $expiry_pieces[0] );
		$fields[ "card_expiry_year{$sufix}" ]  = trim( $expiry_pieces[1] );
	}

	private function validate_amount_billet_and_card( $fields, WC_Order $wc_order )
	{
		if ( $fields['payment_method'] != 'billet_and_card' ) {
			return;
		}

		$billet_value = Utils::get_value_by( $fields, 'billet_value' );
		$card_value   = Utils::get_value_by( $fields, 'card_order_value' );
		$total        = Utils::format_order_price( $wc_order->get_total() );
		$billet       = Utils::format_desnormalized_order_price( $billet_value );
		$credit_card  = Utils::format_desnormalized_order_price( $card_value );
		$amount       = intval( $billet ) + intval( $credit_card );

		if ( $amount < $total ) {
			wp_send_json_error( __( 'The sum of boleto and credit card is less than the total', 'woo-mundipagg-payments' ) );
		}

		if ( $amount > $total ) {
			wp_send_json_error( __( 'The sum of boleto and credit card is greater than the total', 'woo-mundipagg-payments' ) );
		}
	}

	private function validate_amount_2_cards( $fields, WC_Order $wc_order )
	{
		if ( $fields['payment_method'] != '2_cards' ) {
			return;
		}

		$card1  = Utils::get_value_by( $fields, 'card_order_value' );
		$card2  = Utils::get_value_by( $fields, 'card_order_value2' );
		$total  = Utils::format_order_price( $wc_order->get_total() );
		$value1 = Utils::format_desnormalized_order_price( $card1 );
		$value2 = Utils::format_desnormalized_order_price( $card2 );
		$amount = intval( $value1 ) + intval( $value2 );

		if ( $amount < $total ) {
			wp_send_json_error( __( 'The sum of the two credit cards is less than the total', 'woo-mundipagg-payments' ) );
		}

		if ( $amount > $total ) {
			wp_send_json_error( __( 'The sum of the two credits cards is greater than the total', 'woo-mundipagg-payments' ) );
		}
	}

	private function validate_brands( $fields )
	{
		$setting = Setting::get_instance();
		$brand1  = Utils::get_value_by( $fields, 'brand' );
		$brand2  = Utils::get_value_by( $fields, 'brand2' );

		$flags = $setting->cc_flags;

		if ( empty( $flags ) ) {
			return;
		}

		if ( $brand1 && ! in_array( $brand1, $flags ) ) {
			wp_send_json_error( sprintf( __( 'The flag <b>%s</b> is not supported.', 'woo-mundipagg-payments' ), $brand1 ) );
		}

		if ( $brand2 && ! in_array( $brand2, $flags ) ) {
			wp_send_json_error( sprintf( __( 'The flag <b>%s</b> is not supported.', 'woo-mundipagg-payments' ), $brand2 ) );
		}
	}
}
