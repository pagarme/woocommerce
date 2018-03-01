<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit(0);
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Charge;
use Woocommerce\Mundipagg\Resource\Charges as Charges_Resource;

class Charges
{
	public function __construct()
	{
	   	$this->model = new Charge();
		$this->_build_actions();

	   add_action( 'wp_ajax_STW3dqRT6E', array( $this, 'handle_ajax_operations' ) );
	}

	public function handle_actions( $body )
	{
		$this->model->create_from_webhook( $body );
	}

	public function handle_ajax_operations()
	{
		if ( ! Utils::is_request_ajax() ) {
			exit(0);
		}

		$charge_id = Utils::post( 'charge_id', false );
		$amount    = Utils::post( 'amount', 0, 'intval' );
		$mode      = Utils::post( 'mode', false );

		if ( ! $charge_id ) {
			http_response_code( 412 );
			Utils::error_server_json( 'empty_charge_id', 'É necessário informar o ID da charge.' );
			exit(1);
		}

		if ( ! in_array( $mode, [ 'capture', 'cancel' ] ) ) {
			http_response_code( 412 );
			Utils::error_server_json( 'invalid_mode', 'Operação inválida!' );
			exit(1);
		}

		$resource = new Charges_Resource();
		$response = $resource->{$mode}( $charge_id, Utils::format_order_price( $amount ) );

		error_log( print_r( $response, true ) );

		if ( $response->code != 200 ) {
			http_response_code( 412 );
			Utils::error_server_json( 'operation_error', 'Não foi possível efetuar esta operação!' );
			exit(1);
		}

		$this->model->update(
            array(
				'charge_status' => esc_sql( $response->body->status ),
				'charge_data'   => maybe_serialize( $response->body )
            ),
            array(
                'charge_id' => $charge_id
            )
		);

		wp_send_json_success([
			'mode'    => $mode,
			'message' => 'Operação efetuada com sucesso!'
		]);
	}

	private function _build_actions()
	{
		$events = array(
			'charge_created',
			'charge_updated',
			'charge_paid',
			'charge_pending'
		);

		foreach( $events as $event ) {
			add_action( "on_mundipagg_{$event}", array( $this, 'handle_actions' ) );
		}
	}
}    