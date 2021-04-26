<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Setting;

class Orders
{
    private $settings;

    public function __construct()
    {
        $this->settings = Setting::get_instance();
        $this->debug    = $this->settings->is_enabled_logs();

        add_action('on_pagarme_order_paid', array($this, 'set_order_paid'), 20, 2);
        add_action('on_pagarme_order_created', array($this, 'set_order_created'), 20, 2);
        add_action('on_pagarme_order_canceled', array($this, 'set_order_canceled'), 20, 2);
        add_action('add_meta_boxes', array($this, 'add_capture_metabox'));
    }

    public function set_order_created(Order $order, $body)
    {
        $order->payment_on_hold();
    }

    public function set_order_paid(Order $order, $body)
    {
        $order->payment_paid();
    }

    public function set_order_canceled(Order $order, $body)
    {
        $order->payment_canceled();
    }

    public function add_capture_metabox()
    {
        add_meta_box(
            'woo-pagarme-capture',
            'Pagar.me - Captura/Cancelamento',
            array('Woocommerce\Pagarme\View\Orders', 'render_capture_metabox'),
            'shop_order',
            'advanced',
            'high'
        );
    }
}
