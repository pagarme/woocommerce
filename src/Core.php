<?php

namespace Woocommerce\Pagarme;

if (!function_exists('add_action')) {
    exit(0);
}

use WooCommerce;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Action\ActionsRunner;

class Core
{
    private static $_instance = null;

    const SLUG = WCMP_SLUG;

    const PREFIX = WCMP_PREFIX;

    private function __construct()
    {
        add_action('init', array(__CLASS__, 'load_textdomain'));
        add_action('admin_init', array(__CLASS__, 'redirect_on_activate'));
        self::initialize();
        self::admin_enqueue_scripts();
        self::front_enqueue_scripts();
        add_filter('script_loader_tag', [$this, 'addNoDeferToPagespeed'], 10, 2);
        self::addActionsRunners();
    }

    public static function load_textdomain()
    {
        load_plugin_textdomain(self::SLUG, false, self::plugin_rel_path('languages'));
    }

    public static function redirect_on_activate()
    {
        global $pagenow;

        if ($pagenow !== 'plugins.php' || Utils::get('activate-multi')) {
            return;
        }

        if (!get_option(WCMP_OPTION_ACTIVATE, false)) {
            return;
        }

        delete_option(WCMP_OPTION_ACTIVATE);

        wp_redirect(self::get_page_link());

        exit(1);
    }

    public static function initialize()
    {
        $controllers = array(
            'Settings',
            'Checkout',
            'Webhooks',
            'Hub',
            'HubCommand',
            'Orders',
            'Charges',
            'Accounts',
            'HubAccounts',
            'TdsToken',
        );

        self::load_controllers($controllers);
    }

    public static function load_controllers($controllers)
    {
        foreach ($controllers as $controller) {
            $class = sprintf(__NAMESPACE__ . '\Controller\%s', $controller);
            new $class();
        }
    }

    public static function admin_enqueue_scripts()
    {
        add_action('admin_enqueue_scripts', array(__CLASS__, 'scripts_admin'));
    }

    public static function front_enqueue_scripts()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'scripts_front'));
    }

    public static function scripts_admin()
    {
        self::enqueue_scripts('admin');
        self::enqueue_styles('admin');
    }

    public static function scripts_front()
    {
        if (is_checkout() || is_account_page()) {
            self::enqueue_styles('front');
            self::enqueue_scripts('front');
        }
    }

    public static function enqueue_scripts($type, $deps = array(), $localize_args = array())
    {
        wp_enqueue_script(
            'jquery.mask',
            self::plugins_url("assets/javascripts/vendor/jquery.mask.js"),
            array('jquery'),
            '1.14.16',
            false
        );
        wp_enqueue_script(
            'sweetalert2',
            self::plugins_url("assets/javascripts/vendor/sweetalert2.all.min.js"),
            array(),
            '6.11.5',
            true
        );
        if ($type == 'admin') {
            wp_enqueue_script(
                'izimodal',
                self::plugins_url("assets/javascripts/admin/vendor/iziModal.min.js"),
                array_merge(['jquery'], $deps),
                '1.6.1',
                false
            );
        }

    }

    public static function enqueue_styles($type)
    {
        if ($type == 'admin') {
            wp_enqueue_style(
                'izimodal',
                self::plugins_url("assets/stylesheets/vendor/iziModal.min.css"),
                array(),
                '1.6.1'
            );
        }
        wp_enqueue_style(
            'sweetalert2',
            self::plugins_url("assets/stylesheets/vendor/sweetalert2.min.css"),
            array(),
            '6.11.5'
        );
        wp_enqueue_style(
            "{$type}-style-" . self::SLUG,
            self::plugins_url("assets/stylesheets/{$type}/style.css"),
            array(),
            self::filemtime("assets/stylesheets/{$type}/style.css")
        );
    }

    public static function get_name()
    {
        return __('Pagar.me module for Woocommerce', 'woo-pagarme-payments');
    }

    public static function plugin_dir_path($path = '')
    {
        return plugin_dir_path(WCMP_ROOT_FILE) . $path;
    }

    public static function plugin_rel_path($path)
    {
        return dirname(self::plugin_basename()) . '/' . $path;
    }

    /**
     * Plugin file root path
     *
     * @since 1.0
     * @param String $file
     * @return String
     */
    public static function get_file_path($file, $path = '')
    {
        return self::plugin_dir_path($path) . $file;
    }

    public static function plugins_url($path)
    {
        return esc_url(plugins_url($path, WCMP_ROOT_FILE));
    }

    public static function filemtime($path)
    {
        $file = self::plugin_dir_path($path);

        return file_exists($file) ? filemtime($file) : WCMP_VERSION;
    }

    public static function get_page_link()
    {
        return Utils::get_admin_url('admin.php') . '?page=' . self::SLUG;
    }

    public static function tag_name($name = '')
    {
        return sprintf('wcmp_%s_%s', self::PREFIX, str_replace('-', '_', $name));
    }

    /**
     * Plugin base name
     *
     * @since 1.0
     * @param String $filter
     * @return String
     */
    public static function plugin_basename($filter = '')
    {
        return $filter . plugin_basename(WCMP_ROOT_FILE);
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) :
            self::$_instance = new self();
        endif;
    }

    /**
     * @param $custom_url
     *
     * @return string
     */
    public static function getWebhookUrl()
    {
        return (new WooCommerce())->api_request_url(self::getWebhookName());
    }

    /**
     * @return string
     */
    public static function getWebhookName()
    {
        return Utils::add_prefix('-webhook');
    }

    /**
     * @param $custom_url
     *
     * @return string
     */
    public static function getHubCommandUrl()
    {
        return (new WooCommerce())->api_request_url(self::getHubCommandName());
    }

    /**
     * @return string
     */
    public static function getHubCommandName()
    {
        return Utils::add_prefix('-hubcommand');
    }

    /**
     * @return string
     */
    public static function getHubUrl()
    {
        return (new WooCommerce())->api_request_url(self::getHubName());
    }

    /**
     * @return string
     */
    public static function getHubName()
    {
        return Utils::add_prefix('-hub');
    }

    public function addNoDeferToPagespeed($tag, $handle) {
        if ( strpos($handle, WCMP_JS_HANDLER_BASE_NAME) !== 0) {
            return $tag;
        }
        return str_replace( ' src', ' data-pagespeed-no-defer src', $tag );
    }

    private function addActionsRunners()
    {
        $actions = new ActionsRunner();
        $actions->run();
    }
}
