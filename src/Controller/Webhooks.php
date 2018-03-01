<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Order;
use Exception;

class Webhooks
{
    public function __construct()
    { 
        add_action( 'woocommerce_api_' . Core::get_webhook_name(), array( $this, 'handle_requests' ) );
    }

    public function handle_requests()
    {
        $body = Utils::get_json_post_data();

        if ( empty( $body ) ) {
            return;
        }

        $event = $this->sanitize_event_name( $body->type );

        if ( strpos( $event, 'charge' ) !== false ) {
            do_action( "on_mundipagg_{$event}", $body );
            return;
        }

        $order_id = Utils::get_order_by_meta_value( $body->data->id );

		if ( ! $order_id ) {
			return;
        }

        $order = new Order( $order_id );
        
        do_action( "on_mundipagg_{$event}", $order, $body );
    }

    public function sanitize_event_name( $event )
	{
		return str_replace( '.', '_', strtolower( $event ) );
    }
}
