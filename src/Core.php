<?php

namespace Woocommerce\Pagarme;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;

class Core
{
    private static $_instance = null;

    const SLUG = WCMP_SLUG;

    const PREFIX = WCMP_PREFIX;

    const LOCALIZE_SCRIPT_ID = 'PagarmeGlobalVars';

    private function __construct()
    {
        add_action('init', array(__CLASS__, 'load_textdomain'));
        add_action('admin_init', array(__CLASS__, 'redirect_on_activate'));

        self::initialize();
        self::admin_enqueue_scripts();
        self::front_enqueue_scripts();
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
        $id = "{$type}-script-" . self::SLUG;

        wp_enqueue_script(
            $id,
            self::plugins_url("assets/javascripts/{$type}/built.js"),
            array_merge(array('jquery'), $deps),
            self::filemtime("assets/javascripts/{$type}/built.js"),
            true
        );

        if ($type == 'front') {
            wp_enqueue_script(
                'sweetalert2',
                self::plugins_url("assets/javascripts/vendor/sweetalert2.js"),
                array_merge(array('jquery'), $deps),
                self::filemtime("assets/javascripts/vendor/sweetalert2.js"),
                true
            );
        }

        wp_localize_script(
            $id,
            self::LOCALIZE_SCRIPT_ID,
            self::get_localize_script_args($localize_args)
        );
    }

    public static function enqueue_styles($type)
    {
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
        return Utils::get_admin_url('admin.php') . '?page=wc-settings&tab=checkout&section=' . self::SLUG;
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

    public static function credit_card_errors_pt_br()
    {
        return array(
            'exp_month: A value is required.'                             => 'Validade: O mês é obrigatório.',
            'exp_month: The field exp_month must be between 1 and 12.'    => 'Validade: O mês deve estar entre 1 e 12.',
            "exp_year: The value 'undefined' is not valid for exp_year."  => 'Validade: Ano inválido.',
            'request: The card expiration date is invalid.'               => 'Validade: Data de expiração inválida.',
            'request: Card expired.'                                      => 'Validade: Cartão expirado.',
            'holder_name: The holder_name field is required.'             => 'O nome impresso no cartão é obrigatório.',
            'number: The number field is required.'                       => 'O número do cartão é obrigatório.',
            'number: The number field is not a valid credit card number.' => 'Este número de cartão é inválido.',
            'number: The field number must be a string with a minimum length of 13 and a maximum length of 19.'
            => 'O numéro do cartão tamanho deve ter entre 13 e 19 caracteres.',
        );
    }
}
