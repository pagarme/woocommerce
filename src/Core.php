<?php

namespace Woocommerce\Pagarme;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Action\ActionsRunner;

class Core
{
    private static $_instance = null;

    const SLUG = WCMP_SLUG;

    const PREFIX = WCMP_PREFIX;

    const LOCALIZE_SCRIPT_ID = 'PagarmeGlobalVars';

    const INVALID_CARD_ERROR_MESSAGE = 'This card number is invalid.';

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

    public static function get_localize_script_args($args = array())
    {
        $defaults = array(
            'ajaxUrl'        => Utils::get_admin_url('admin-ajax.php'),
            'WPLANG'         => get_locale(),
            'spinnerUrl'     => self::plugins_url('assets/images/icons/spinner.png'),
            'prefix'         => self::PREFIX,
            'checkoutErrors' => array(
                'pt_BR' => self::credit_card_errors_pt_br(),
            ),
        );

        return array_merge($defaults, $args);
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
        if ($type == 'front') {
            wp_enqueue_script(
                'pagarme-checkout-card',
                self::plugins_url("assets/javascripts/front/checkout/model/payment.js"),
                array_merge(array('jquery'), $deps),
                self::filemtime("assets/javascripts/front/checkout/model/payment.js"),
                true
            );
            wp_localize_script(
                'pagarme-checkout-card',
                'PagarmeGlobalVars',
                self::get_localize_script_args()
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

    public static function get_webhook_url($custom_url = false)
    {
        $url = !$custom_url ? Utils::get_site_url() : $custom_url;

        return sprintf('%s/wc-api/%s/', $url, self::get_webhook_name());
    }

    public static function get_webhook_name()
    {
        return Utils::add_prefix('-webhook');
    }

    public static function get_hub_command_url($custom_url = false)
    {
        $url = !$custom_url ? Utils::get_site_url() : $custom_url;

        return sprintf(
            '%s/wc-api/%s/',
            $url,
            self::get_hub_command_name()
        );
    }

    public static function get_hub_command_name()
    {
        return Utils::add_prefix('-hubcommand');
    }

    public static function get_hub_url()
    {
        return sprintf(
            '%s/wc-api/%s/',
            Utils::get_site_url(),
            self::get_hub_name()
        );
    }

    public static function get_hub_name()
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

    public static function credit_card_errors_pt_br()
    {
        return array(
            'exp_month: A value is required.'                             =>
                __('Expiration Date: The month is required.', 'woo-pagarme-payments'),
            'exp_month: The field exp_month must be between 1 and 12.'    =>
                __('Expiration Date: The month must be between 1 and 12.', 'woo-pagarme-payments'),
            "exp_year: The value 'undefined' is not valid for exp_year."  =>
                __('Expiration Date: Invalid year.', 'woo-pagarme-payments'),
            'request: The card expiration date is invalid.'               =>
                __('Expiration Date: Invalid expiration date.', 'woo-pagarme-payments'),
            'request: Card expired.'                                      =>
                __('Expiration Date: Expired card.', 'woo-pagarme-payments'),
            'holder_name: The holder_name field is required.'             =>
                __('The card holder name is required.', 'woo-pagarme-payments'),
            'number: The number field is required.'                       =>
                __('The card number is required.', 'woo-pagarme-payments'),
            'number: The number field is not a valid credit card number.' =>
                __(self::INVALID_CARD_ERROR_MESSAGE, 'woo-pagarme-payments'),
            'card: The number field is not a valid card number'           =>
                __(self::INVALID_CARD_ERROR_MESSAGE, 'woo-pagarme-payments'),
            'card.number: The field number must be a string with a minimum length of 13 and a maximum length of 19.'
            => __('The card number must be between 13 and 19 characters.', 'woo-pagarme-payments'),
            'card: Card expired.'                                         =>
                __('The expiration date is expired.', 'woo-pagarme-payments'),
            'card.cvv: The field cvv must be a string with a minimum length of 3 and a maximum length of 4.'
            => __('The card code must be between 3 and 4 characters.', 'woo-pagarme-payments'),
            'card: Invalid data to change card brand'                     =>
                __(self::INVALID_CARD_ERROR_MESSAGE, 'woo-pagarme-payments'),
            'card: Tokenize timeout'                                      =>
                __('Timeout na tokenização.', 'woo-pagarme-payments'),
            'card: Can\'t check card form: Invalid element received'        =>
                __('Can\'t check card form: Invalid element received.', 'woo-pagarme-payments'),
        );
    }
}
