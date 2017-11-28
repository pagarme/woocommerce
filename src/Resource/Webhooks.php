<?php
namespace Woocommerce\Mundipagg\Resource;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Unirest\Request;
use Unirest\Request\Body;

class Webhooks extends Base
{
	const PATH = 'accounts';

	/**
	 * Override parent method auth

	 * Set Basic Authentication Header on Unirest/Request
	 *
	 * @return void
	 */
	public function auth()
	{
		Request::auth( $this->settings->account_management_key, '' );
	}

	/**
	 * Create default webhook to listen all events
	 *
	 * @param string notification_url
	 *
	 * @return object Unirest\Response
	 */
	public function create( $notification_url )
	{
		$events = array( 
			'order.created', 'order.paid', 'order.payment_failed',
			'order.canceled', 'order.closed', 'order.updated',
			'charge.created', 'charge.updated', 'charge.paid', 
			'charge.payment_failed', 'charge.refunded', 'charge.pending',
			'charge.underpaid', 'charge.overpaid', 'charge.partial_canceled'
		);

		$args            = array(
			'status'       => 'active',
			'url'          => $notification_url,
			'events'       => $events,
			'interval'     => 300,
			'max_attempts' => 3		
		);

		return Request::post(
			Base::URL . self::PATH . '/' . $this->settings->account_id . '/webhook-settings',
			$this->get_headers(),
			Body::Json( $args )
		);
	}
}
