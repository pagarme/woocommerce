<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Order;
use Woocommerce\Mundipagg\Model\Setting;

class Orders
{
    public function __construct()
    {
        $this->settings = Setting::get_instance();
        $this->debug    = $this->settings->is_enabled_logs();

        add_action( 'on_mundipagg_order_paid', array( $this, 'set_order_paid' ), 20, 2 );
        add_action( 'on_mundipagg_order_created', array( $this, 'set_order_created' ), 20, 2 );
        add_action( 'on_mundipagg_order_canceled', array( $this, 'set_order_canceled' ), 20, 2 );
        add_action( 'add_meta_boxes', array( $this, 'add_capture_metabox' ) );
    }

    public function set_order_created( Order $order, $body )
    {
        $order->payment_on_hold();

        if ( $this->debug ) {
			$this->settings->log()->add( 'woo-mundipagg', 'WEBHOOK ORDER CREATED: ' . print_r( $body, true ) );
		}
    }

    public function set_order_paid( Order $order, $body )
	{
        $order->payment_paid();

        if ( $this->debug ) {
            $this->settings->log()->add( 'woo-mundipagg', 'WEBHOOK ORDER PAID: ' . print_r( $body, true ) );
        }
    }

    public function set_order_canceled( Order $order, $body )
    {
        $order->payment_canceled();

        if ( $this->debug ) {
			$this->settings->log()->add( 'woo-mundipagg', 'WEBHOOK ORDER CANCELED: ' . print_r( $body, true ) );
		}
    }

    public function add_capture_metabox()
    {
        add_meta_box(
			'woo-mundipagg-capture',
			'MundiPagg - Captura/Cancelamento',
			array( 'Woocommerce\Mundipagg\View\Orders', 'render_capture_metabox' ),
			'shop_order',
			'advanced',
			'high'
		);
    }
}    