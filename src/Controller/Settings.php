<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Hub\Environment;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Hub\Integration;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Select;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Section;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Page\PageSettings;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Gateway;

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

    /**
     * @var array
     */
    private $gateways;

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
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_filter(Core::plugin_basename('plugin_action_links_'), array($this, 'plugin_link'));
        add_action('admin_menu', array($this, 'settings_menu'), 58);
        add_action('admin_init', array($this, 'plugin_settings'));

        $this->gateway_load();
        $this->select = $select;
        if (!$select) {
            $this->select = new Select();
        }
        $this->setSectionsFields();
    }

    public function admin_scripts()
    {
        wp_register_script('pagarme_settings', Core::plugins_url('assets/javascripts/admin/pagarme_settings.js'), array('jQuery'), false, true);
        wp_enqueue_script('pagarme_settings');
        wp_register_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array());
        wp_enqueue_style('woocommerce_admin_styles');

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonces' => array(
                'gateway_toggle' => wp_create_nonce('woocommerce-toggle-payment-gateway-enabled'),
            )
        );
        wp_localize_script('pagarme_settings', 'pagarme_settings', $params);
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
                            'fieldObject' => Select::class,
                            'id' => 'enabled',
                            'title' => 'Enable',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::NO_VALUE,
                        ],
                        [
                            'fieldObject' => Integration::class,
                            'id' => 'hub_button_integration',
                            'title' => 'Hub integration',
                        ],
                        [
                            'fieldObject' => Environment::class,
                            'id' => 'hub_environment',
                            'title' => 'Integration environment',
                            'default' => 'Develop',
                        ]
                    ]
                ],
                [

                    'id' => 'tools_section',
                    'title' => 'Tools',
                    'fields' => [
                        [
                            'fieldObject' => Select::class,
                            'id' => 'is_gateway_integration_type',
                            'title' => 'Advanced settings',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::NO_VALUE,
                            'description' => 'Configurations that only works for Gateway customers, who have a direct contract with an acquirer.'
                        ],
                        [
                            'fieldObject' => Select::class,
                            'id' => 'enable_logs',
                            'title' => 'Logs',
                            'options' => $this->yesNoOptions->toOptionArray(),
                            'default' => Yesno::NO_VALUE,
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
        $this->gateways = $gateways;
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
        $pageSettings = new PageSettings($options, $this->gateways);
        $pageSettings->includeTemplate();
    }


    /**
     * @return string
     */
    public function get_option_key()
    {
        return $this->model->config->getOptionKey();
    }

    /**
     * @param $values
     * @return void
     * @throws \Exception
     */
    public function getField($values)
    {
        if (class_exists($values['fieldObject'])) {
            $field = new $values['fieldObject']();
            $field->setData($values)->toHtml();
            return;
        }
        throw new \Exception(sprintf('Field object class %s not exists. ', $values['fieldObject']));
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
     * @param $input
     * @return array
     */
    public function validate_options($input ) {
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
