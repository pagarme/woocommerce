<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Setting;

class Settings
{

    const WC_PAYMENT_GATEWAY = 'WC_Payment_Gateway';

    public function __construct()
    {
        add_filter(Core::plugin_basename('plugin_action_links_'), array($this, 'plugin_link'));

        $this->gateway_load();
    }

    /**
     * Add link settings page
     *
     * @since 1.0
     * @param array $links
     * @return array
     */
    public function plugin_link($links)
    {
        $plugin_links = array(
            sprintf(
                '<a href="%s">%s</a>',
                Core::get_page_link(),
                __('Settings', 'woo-pagarme-payments')
            ),
        );
        return array_merge($plugin_links, $links);
    }

    public function gateway_load()
    {
        if (!class_exists(self::WC_PAYMENT_GATEWAY)) {
            return;
        }
        add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateway'));
    }

    /**
     * @param $methods
     * @return mixed
     */
    public function add_payment_gateway($methods)
    {
        foreach ($this->getGateways() as $gateway) {
            $methods[] = $gateway;
        }
        return $methods;
    }

    /**
     * @return array
     */
    private function getGateways()
    {
        $this->autoLoad();
        $gateways = [];
        foreach(get_declared_classes() as $class){
            if(is_subclass_of( $class, Gateways\AbstractGateway::class)) {
                $gateways[] = $class;
            }
        }
        return $gateways;
    }

    public function autoLoad()
    {
        foreach(glob( __DIR__ . '/Gateways/*.php') as $file) {
            include_once($file);
        }
    }
}
