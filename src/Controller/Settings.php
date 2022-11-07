<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Setting;

class Settings
{

    /** @var string */
    const WC_PAYMENT_GATEWAY = 'WC_Payment_Gateway';

    /** @var Gateway */
    public $model;

    public function __construct()
    {
        $this->model = new Gateway();

        add_filter(Core::plugin_basename('plugin_action_links_'), array($this, 'plugin_link'));
        add_action( 'admin_menu', array( $this, 'settings_menu' ), 58 );
        add_action( 'admin_init', array( $this, 'plugin_settings' ) );

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
        foreach (glob(__DIR__ . '/Gateways/*.php') as $file) {
            include_once($file);
        }
    }

    /**
     * Add the settings page.
     */
    public function settings_menu() {
        add_submenu_page(
            'woocommerce',
            "Pagar.me",
            "Pagar.me",
            'manage_options',
            'woo-pagarme-payments',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function settings_page() {
        $options = $this->get_option_key();
        include dirname( __FILE__ ) . '/../View/Settings.php';
    }


    public function get_option_key()
    {
        return "wc_pagarme_settings";
    }

    /**
	 * Plugin settings form fields.
	 */
    public function plugin_settings() {
        $option = $this->get_option_key();

        add_settings_section(
            'options_section',
            __( 'General', 'woo-pagarme-payments' ),
            array( $this, 'section_options_callback' ),
            $option
        );

        add_settings_field(
            'enabled',
            __('Enable', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'options_section',
            array(
                'menu'  => $option,
                'id'    => 'enabled',
                'label'   => __('Enable Pagar.me', 'woo-pagarme-payments')
            )
        );

        add_settings_field(
            'hub_button_integration',
            __('Hub integration', 'woo-pagarme-payments'),
            array( $this, 'hub_integration_button_callback' ),
            $option,
            'options_section',
            array(
                'menu'  => $option,
                'id'    => 'hub_button_integration'
            )
        );

        add_settings_field(
            'hub_environment',
            __('Integration environment', 'woo-pagarme-payments'),
            array( $this, 'hub_environment_callback' ),
            $option,
            'options_section',
            array(
                'menu'  => $option,
                'id'    => 'hub_environment'
            )
        );

        add_settings_section(
            'section_payment_settings',
            __('Payment methods', 'woo-pagarme-payments'),
            array( $this, 'section_options_callback' ),
            $option
        );

        add_settings_field(
            'enable_credit_card',
            __('Credit card', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'enable_credit_card',
                'label'   => __('Enable credit card', 'woo-pagarme-payments'),
                'default' => 'yes'
            )
        );

        add_settings_field(
            'enable_billet',
            __('Boleto', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'enable_billet',
                'label'   => __('Enable boleto', 'woo-pagarme-payments'),
                'default' => 'yes'
            )
        );

        add_settings_field(
            'enable_pix',
            __('Pix', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'enable_pix',
                'label'   => __('Enable pix', 'woo-pagarme-payments'),
                'default' => 'no'
            )
        );

        add_settings_field(
            'multimethods_2_cards',
            __('Multi-means </br>(2 Credit cards)', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'multimethods_2_cards',
                'label'   => __('Enable multi-means (2 Credit cards)', 'woo-pagarme-payments'),
                'default' => 'no'
            )
        );

        add_settings_field(
            'multimethods_billet_card',
            __('Multi-means </br>(Boleto + Credit card)', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'multimethods_billet_card',
                'label'   => __('Enable multi-means (Boleto + Credit card)', 'woo-pagarme-payments'),
                'default' => 'no'
            )
        );

        add_settings_field(
            'multicustomers',
            __('Multi-buyers', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'multicustomers',
                'label'   => __('Enable multi-buyers', 'woo-pagarme-payments'),
                'default' => 'no'
            )
        );

        add_settings_field(
            'enable_voucher',
            __('Voucher Card', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'section_payment_settings',
            array(
                'menu'  => $option,
                'id'    => 'enable_voucher',
                'label'   => __('Enable voucher', 'woo-pagarme-payments'),
                'default' => 'no',
                'description' => __('You need to have an exclusive contract with a voucher flag (Gateway customer only)', 'woo-pagarme-payments')
            )
        );

        add_settings_section(
            'tools_section',
            __( 'Tools', 'woo-pagarme-payments' ),
            array( $this, 'section_options_callback' ),
            $option
        );

        add_settings_field(
            'enable_logs',
            __('Logs', 'woo-pagarme-payments'),
            array( $this, 'checkbox_element_callback' ),
            $option,
            'tools_section',
            array(
                'menu'  => $option,
                'id'    => 'enable_logs',
                'label'       => __('Enable', 'woo-pagarme-payments'),
                'default'     => 'no',
                'description' => __('Log Pagar.me events, you can check this log in WooCommerce>Status>Logs.', 'woo-pagarme-payments')
            )
        );

        // Register settings.
        register_setting( $option, $option, array( $this, 'validate_options' ) );
    }

    /**
     * Section null fallback.
     */
    public function section_options_callback() {

    }

    /**
     * Checkbox element fallback.
     *
     * @param array $args Callback arguments.
     */
    public function checkbox_element_callback( $args ) {
        $menu    = $args['menu'];
        $id      = $args['id'];
        $options = get_option( $menu );

        if ( isset( $options[ $id ] ) ) {
            $current = $options[ $id ];
        } else {
            $current = isset( $args['default'] ) ? $args['default'] : '0';
        }

        include dirname( __FILE__ ) . '/../View/Admin/html-checkbox-field.php';
    }

    /**
     * Text element fallback.
     *
     * @param array $args Callback arguments.
     */
    public function text_element_callback( $args ) {
        $menu    = $args['menu'];
        $id      = $args['id'];
        $options = get_option( $menu );

        if ( isset( $options[ $id ] ) ) {
            $current = $options[ $id ];
        } else {
            $current = isset( $args['default'] ) ? $args['default'] : '';
        }

        include dirname( __FILE__ ) . '/../View/Admin/html-text-field.php';
    }

    /**
     * Select element fallback.
     *
     * @param array $args Callback arguments.
     */
    public function select_element_callback( $args ) {
        $menu    = $args['menu'];
        $id      = $args['id'];
        $options = get_option( $menu );

        if ( isset( $options[ $id ] ) ) {
            $current = $options[ $id ];
        } else {
            $current = isset( $args['default'] ) ? $args['default'] : 0;
        }

        include dirname( __FILE__ ) . '/../View/Admin/html-select-field.php';
    }

    /**
     * Select element fallback.
     *
     * @param array $args Callback arguments.
     */
    public function hub_integration_button_callback( $args ) {
        $menu    = $args['menu'];
        $id      = $args['id'];
        $options = get_option( $menu );

        if ( isset( $options[ $id ] ) ) {
            $current = $options[ $id ];
        } else {
            $current = isset( $args['default'] ) ? $args['default'] : 0;
        }




        $hub_install_id = $this->model->settings->hub_install_id;
        $button_label = $this->model->get_hub_button_text($hub_install_id);
        $url_hub = $this->model->get_hub_url($hub_install_id);




        include dirname( __FILE__ ) . '/../View/Admin/html-hub-integration-button.php';
    }

    /**
     * Select element fallback.
     *
     * @param array $args Callback arguments.
     */
    public function hub_environment_callback( $args ) {
        $hub_environment = $this->model->settings->hub_environment;
        $is_sandbox_mode = $this->model->is_sandbox_mode();
        include dirname( __FILE__ ) . '/../View/Admin/html-hub-enviroment.php';
    }

    /**
     * Valid options.
     *
     * @param  array $input options to valid.
     *
     * @return array        validated options.
     */
    public function validate_options( $input ) {
        $output = array();

        // Loop through each of the incoming options.
        foreach ( $input as $key => $value ) {
            // Check to see if the current option has a value. If so, process it.
            if ( isset( $input[ $key ] ) ) {
                $output[ $key ] = sanitize_text_field( $input[ $key ] );
            }
        }

        return $output;
    }
}
