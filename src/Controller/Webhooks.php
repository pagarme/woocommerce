<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Order;
use Woocommerce\Mundipagg\Model\Setting;
use Exception;

class Webhooks
{
    private $settings;

	public function __construct()
	{
        $this->settings = Setting::get_instance();
		add_action( 'woocommerce_api_' . Core::get_webhook_name(), array( $this, 'handle_requests' ) );
	}

	public function handle_requests()
	{
		$body = Utils::get_json_post_data();

		if ( empty( $body ) ) {
            $this->settings->log()->add( 'woo-mundipagg', 'Webhook Received: empty body!');
			return;
		}
        $this->settings->log()->add( 'woo-mundipagg', 'Webhook Received: ' . json_encode( $body, JSON_PRETTY_PRINT));

		$event = $this->sanitize_event_name( $body->type );

		if ($this->was_sent( $event, $body->id, $body->data->code)) {
			return;
		}

		if (strpos( $event, 'charge' ) !== false) {
			update_post_meta( $body->data->code, "webhook_{$event}_{$body->id}", true );
			do_action( "on_mundipagg_{$event}", $body );
			return;
		}

		$order_id = Utils::get_order_by_meta_value( $body->data->id );

		if ( ! $order_id ) {
			return;
		}

		$order = new Order($order_id);

		update_post_meta( $order_id, "webhook_{$event}_{$body->id}", true );
		do_action( "on_mundipagg_{$event}", $order, $body );
	}

	public function sanitize_event_name( $event )
	{
		return str_replace( '.', '_', strtolower( $event ) );
	}

	public function was_sent( $event_name, $event_id, $order_id )
	{
		$value = get_post_meta( $order_id, "webhook_{$event_name}_{$event_id}", true );

		return $value ? true : false;
	}
}
