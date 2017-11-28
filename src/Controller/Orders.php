<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Order;

class Orders
{
    public function __construct()
    {
        add_action( 'on_mundipagg_order_paid', array( $this, 'set_order_paid' ), 20, 2 );
        add_action( 'on_mundipagg_order_created', array( $this, 'set_order_created' ), 20, 2 );
        add_action( 'on_mundipagg_order_canceled', array( $this, 'set_order_canceled' ), 20, 2 );
    }

    public function set_order_created( Order $order, $body )
    {
        $order->payment_on_hold();
    }

    public function set_order_paid( Order $order, $body )
	{
        $wc_order = wc_get_order( $order->ID );
        $wc_order->add_order_note( __( 'Mundipagg: Payment has already been confirmed.', Core::SLUG ) );
        $wc_order->payment_complete();
    }

    public function set_order_canceled( Order $order, $body )
    {
        $wc_order = wc_get_order( $order->ID );
        $wc_order->update_status( 'cancelled', __( 'Mundipagg: Payment canceled.', Core::SLUG ) );
    }
}    