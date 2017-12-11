<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Setting;
use Woocommerce\Mundipagg\Model\Order;
use Exception;

class Webhooks
{
    public function __construct()
    {
        add_action( 'woocommerce_api_' . Core::get_webhook_name(), array( $this, 'handle_requests' ) );
        add_action( 'on_mundipagg_order_paid', array( $this, 'set_order_paid' ), 20, 2 );
        add_action( 'on_mundipagg_order_created', array( $this, 'set_order_created' ), 20, 2 );
        add_action( 'on_mundipagg_order_canceled', array( $this, 'set_order_canceled' ), 20, 2 );
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
    
    public function set_order_created( Order $order, $body )
    {
        $order->payment_on_hold();
    }

    public function set_order_paid( Order $order, $body )
	{
        $wc_order = wc_get_order( $order->ID );
        $wc_order->add_order_note( __( 'Mundipagg: Payment has already been confirmed.', Core::TEXTDOMAIN ) );
        $wc_order->payment_complete();
    }

    public function set_order_canceled( Order $order, $body )
    {
        $wc_order = wc_get_order( $order->ID );
        $wc_order->update_status( 'cancelled', __( 'Mundipagg: Payment canceled.', Core::TEXTDOMAIN ) );
    }
}
