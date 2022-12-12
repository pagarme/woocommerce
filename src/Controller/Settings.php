<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Select;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Section;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Setting;

class Settings
{

    /** @var string */
    const WC_PAYMENT_GATEWAY = 'WC_Payment_Gateway';

    /** @var Gateway */
    public $model;

    /** @var Yesno */
    private $yesNoOptions;

    /** @var Select */
    private $select;

    /** @var Gateway */
    protected $config;

    protected $sectionsFields = [];

    public function __construct(
        Select $select = null,
        Config $config = null
    ) {
        $this->select = $select;
        if (!$select) {
            $this->select = new Select();
        }
        $this->config = $config;
        if (!$config) {
            $this->config = new Config();
        }
        $this->model = new Gateway();
        $this->yesNoOptions = new Yesno();
        add_filter(Core::plugin_basename('plugin_action_links_'), array($this, 'plugin_link'));
        add_action( 'admin_menu', array( $this, 'settings_menu' ), 58 );
        add_action( 'admin_init', array( $this, 'plugin_settings' ) );

        $this->gateway_load();
        $this->select = $select;
        if (!$select) {
            $this->select = new Select();
        }
        $this->setSectionsFields();
    }

    private function setSectionsFields(array $value = null)
    {
        if ($value) {
            $this->sectionsFields = $value;
            return;
        }
        $this->sectionsFields = [
            'section' => [
                [
                    'id' => 'options_section',
                    'title' => 'General',
                    'fields' => [
                        [
                            'id' => 'enabled',
                            'title' => 'Enable',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                        ],
                        [
                            'id' => 'hub_button_integration',
                        ],
                        [
                            'id' => 'hub_environment',
                        ]
                    ]
                ],
                [
                    'id' => 'section_payment_settings',
                    'title' => 'Payment methods',
                    'fields' => [
                        [
                            'id' => 'enable_credit_card',
                            'title' => 'Credit card',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Enable credit card'
                        ],
                        [
                            'id' => 'enable_billet',
                            'title' => 'Boleto',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Enable credit card'
                        ],
                        [
                            'id' => 'enable_pix',
                            'title' => 'Pix',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Enable pix'
                        ],
                        [
                            'id' => 'multimethods_2_cards',
                            'title' => 'Multi-means </br>(2 Credit cards)',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Enable multi-means (2 Credit cards)'
                        ],
                        [
                            'id' => 'multimethods_billet_card',
                            'title' => 'Multi-means </br>(Boleto + Credit card)',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Enable multi-means (Boleto + Credit card)'
                        ],
                        [
                            'id' => 'multicustomers',
                            'title' => 'Multi-buyers',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Enable multi-buyers'
                        ],
                        [
                            'id' => 'enable_voucher',
                            'title' => 'Voucher Card',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'You need to have an exclusive contract with a voucher flag (Gateway customer only)'
                        ]
                    ],
                ],
                [
                    'id' => 'tools_section',
                    'title' => 'Tools',
                    'fields' => [
                        [
                            'id' => 'is_gateway_integration_type',
                            'title' => 'Advanced settings',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Configurations that only works for Gateway customers, who have a direct contract with an acquirer.'
                        ],
                        [
                            'id' => 'enable_logs',
                            'title' => 'Logs',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::VALUE_NO,
                            'description' => 'Log Pagar.me events, you can check this log in WooCommerce>Status>Logs.'
                        ]
                    ],
                ]
            ]
        ];
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


    /**
     * @return string
     */
    public function get_option_key()
    {
        return $this->model->config->getOptionKey();
    }

    public function getField($values)
    {
        if ($values['id'] === 'hub_button_integration') {
            add_settings_field(
                'hub_button_integration',
                __('Hub integration', 'woo-pagarme-payments'),
                array( $this, 'hub_integration_button_callback' ),
                $values['page'],
                'options_section',
                array(
                    'menu'  => $values['page'],
                    'id'    => 'hub_button_integration'
                )
            );
            return;
        }
        if ($values['id'] === 'hub_environment') {
            add_settings_field(
                'hub_environment',
                __('Integration environment', 'woo-pagarme-payments'),
                array( $this, 'hub_environment_callback' ),
                $values['page'],
                'options_section',
                array(
                    'menu'  => $values['page'],
                    'id'    => 'hub_environment'
                )
            );
            return;
        }
        $select = new Select();
        $select->setData($values)->toHtml();
    }

    /**
     * Plugin settings form fields.
     */
    public function plugin_settings() {
        $option = $this->get_option_key();
        foreach ($this->sectionsFields['section'] as $key => $value) {
            $section = new Section(
                [
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'page' => $option,
                ]
            );
            $section->toHtml();
            foreach ($value['fields'] as $key => $field) {
                $field['page'] = $option;
                $field['section'] = $section->getId();
                $field['name'] = $option;
                $this->getField($field);
            }
        }
        // Register settings.
        register_setting( $option, $option, array( $this, 'validate_options' ) );
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

        $isAdvancedSettings = $this->model->settings->is_gateway_integration_type();


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
