<?php
namespace Woocommerce\Mundipagg\Resource;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Unirest\Request;
use Unirest\Request\Body;

class Orders extends Base
{
	const PATH = 'orders';

	/**
	 * Create a new order
	 *
	 * @param array $data fields to send
	 *
	 * @return object Unirest\Response
	 */
	public function create( array $data )
	{
		$fields = array(
			'code',
			'items',
			'customer_id',
			'customer',
			'shipping',
			'payments',
			'closed',
			'antifraud_enabled',
		);

		$args = $this->get_args( $fields, $data );

		return Request::post( Base::URL . self::PATH, $this->get_headers(), Body::Json( $args ) );
	}
}
