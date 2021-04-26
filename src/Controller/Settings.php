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
    public function __construct()
    {
        add_filter(Core::plugin_basename('plugin_action_links_'), array($this, 'plugin_link'));

        $this->gateway_load();
    }

    /**
     * Add link settings page
     *
     * @since 1.0
     * @param Array $links
     * @return Array
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
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateway'));
    }

    public function add_payment_gateway($methods)
    {
        $methods[] = __NAMESPACE__ . '\Gateways';

        return $methods;
    }
}
