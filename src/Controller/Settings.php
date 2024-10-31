<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use WC_Subscriptions_Core_Plugin;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Hub\Environment;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Hub\Integration;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Select;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Section;
use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Page\PageSettings;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Subscription;

/**
 * Abstract Settings
 * @package Woocommerce\Pagarme\Controller
 */
class Settings
{

    /** @var string */
    const WC_PAYMENT_GATEWAY = 'WC_Payment_Gateway';

    /** @var string */
    const PAGARME_DOCS_ANTIFRAUD_URL = 'https://docs.pagar.me/reference/vis%C3%A3o-geral-sobre-antifraude-1';

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
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_filter(Core::plugin_basename('plugin_action_links_'), array($this, 'plugin_link'));
        add_action('admin_menu', array($this, 'settings_menu'), 58);
        add_action('admin_init', array($this, 'plugin_settings'));

        if (Subscription::hasSubscriptionPlugin()){
            add_filter('woocommerce_payment_gateways_setting_columns', array($this, 'subscription_payments_toggles_column'));
            add_action('woocommerce_payment_gateways_setting_column_subscription_payments_toggles', array($this, 'populate_subscription_payments_toggles_column'));
            add_action('wp_ajax_pagarme_toggle_payment_subscription', array($this, 'pagarme_toggle_payment_subscription'));
        }

        $this->gateway_load();
        $this->select = $select;
        if (!$select) {
            $this->select = new Select();
        }
        $this->setSectionsFields();
    }

    public function jsUrl($jsFileName)
    {
        return Core::plugins_url('assets/javascripts/admin/' . $jsFileName . '.js');
    }

    public function admin_scripts()
    {
        wp_register_script('pagarme_settings', $this->jsUrl('pagarme_settings'), array('jquery'), false, true);
        wp_enqueue_script('pagarme_settings');
        wp_register_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css');
        wp_enqueue_style('woocommerce_admin_styles');

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonces' => array(
                'gateway_toggle' => wp_create_nonce('woocommerce-toggle-payment-gateway-enabled'),
            ),
            'allow_no_address_swal' => array(
                'title' => __('Are you sure?', 'woo-pagarme-payments'),
                'text' => __('If your Pagar.me Antifraud is active, orders will fail.', 'woo-pagarme-payments'),
                'cancelButtonText' => __('Cancel', 'woo-pagarme-payments'),
            ),
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
                            'fieldObject' => Integration::class,
                            'id' => 'hub_button_integration',
                            'title' => 'Pagar.me integration',
                        ],
                        [
                            'fieldObject' => Environment::class,
                            'id' => 'hub_environment',
                            'title' => 'Integration environment',
                            'default' => '',
                        ],
                        [
                            'fieldObject' => Select::class,
                            'id' => 'multicustomers',
                            'title' => 'Multi-buyers',
                            'options' => $this->yesNoOptions->toLabelsArray(),
                            'default' => strtolower(Yesno::NO),
                        ],
                        [
                            'fieldObject' => Select::class,
                            'id' => 'modify_address',
                            'title' => 'Modify address fields',
                            'options' => $this->yesNoOptions->toLabelsArray(),
                            'default' => strtolower(Yesno::YES),
                            'description' => [
                                'format' => "Corrects the 'label' and 'placeholder' attributes of the 'Address' and "
                                    . "'Complement' fields to instruct users on how to fill them in correctly.",
                                'values' => []
                            ],
                        ],
                        [
                            'fieldObject' => Select::class,
                            'id' => 'allow_no_address',
                            'title' => 'Allow order without address',
                            'options' => $this->yesNoOptions->toLabelsArray(),
                            'default' => strtolower(Yesno::NO),
                            'description' => [
                                'format' => 'For PSP customers with Pagar.me Antifraud active, it is mandatory to fill'
                                    . ' in all address fields. %sRead documentation »%s',
                                'values' => [
                                    '<a href="'
                                        . self::PAGARME_DOCS_ANTIFRAUD_URL
                                        . '" target="_blank" rel="noopener">',
                                    '</a>'
                                ]
                            ],
                        ],
                        [
                            'fieldObject' => Select::class,
                            'id' => 'enable_logs',
                            'title' => 'Logs',
                            'options' => $this->yesNoOptions->toLabelsArray(),
                            'default' => strtolower(Yesno::NO),
                            'description' => 'Log Pagar.me events, you can check this log in WooCommerce>Status>Logs.'
                        ]
                    ],
                ]
            ]
        ];

        if (empty($this->config->getAccountId()) && $this->config->getHubInstallId()) {
            $this->sectionsFields['section'][0]['fields'][] =
                [
                    'fieldObject' => Select::class,
                    'id' => 'is_gateway_integration_type',
                    'title' => 'Advanced settings',
                    'options' => $this->yesNoOptions->toLabelsArray(),
                    'default' => strtolower(Yesno::NO),
                    'description' => 'Configurations that only works for Gateway customers, who have a direct contract with an acquirer.'
                ];
        }
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
        $this->config = new Config();
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, Gateways\AbstractGateway::class)) {
                if (strpos($class, "Voucher") !== false && $this->config->getIsVoucherPSP()) {
                    continue;
                }
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
    public function settings_menu()
    {
        add_submenu_page(
            'woocommerce',
            "Pagar.me",
            "Pagar.me",
            'manage_options',
            'woo-pagarme-payments',
            array($this, 'settings_page')
        );
    }

    /**
     * Render the settings page for this plugin.
     */
    public function settings_page()
    {
        $options = $this->get_option_key();
        $pageSettings = new PageSettings($options, $this->getGateways());
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
    public function plugin_settings()
    {
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
        register_setting($option, $option, array($this, 'validate_options'));
    }

    /**
     * @param $input
     * @return array
     */
    public function validate_options($fields)
    {
        $sanitizedData = [];
        foreach ($fields as $key => $field) {
            if (isset($fields[$key])) {
                $sanitizedData[$key] = $this->sanitize_field($field);
            }
        }
        return $sanitizedData;
    }

    private function sanitize_field($field)
    {
        if (is_array($field)) {
            return $this->validate_options($field);
        }
        return sanitize_text_field($field);
    }

    /**
     * @param array $header
     * @return array
     */
    public static function subscription_payments_toggles_column($header)
    {
        $columnPosition = array_search('renewals', array_keys($header)) + 1;
        $newColumn = ['subscription_payments_toggles' => __('Active for subscription', 'woo-pagarme-payments')];

        return array_slice($header, 0, $columnPosition, true)
            + $newColumn
            + array_slice($header, $columnPosition, count($header) - $columnPosition, true);
    }

    /**
     * @param $gateway
     */
    public function populate_subscription_payments_toggles_column($gateway)
    {
        echo '<td class="subscription_payments_toggles">';

        $paymentGatewaysHandler = WC_Subscriptions_Core_Plugin::instance()->get_gateways_handler_class();

        if (
            !str_starts_with($gateway->id, 'woo-pagarme-payments-')
            || !$paymentGatewaysHandler::gateway_supports_subscriptions($gateway)
        ) {
            echo '-</td>';
            return;
        }

        if ($paymentGatewaysHandler::gateway_supports_subscriptions($gateway)) {
            echo '<a class="pagarme-toggle-payment-subscription" href="'
                . esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=' . strtolower($gateway->id))) . '">';
            if ($this->isGatewaySubscriptionActive($gateway)) {
                echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="'
                    . esc_attr(
                        sprintf(
                            __('The "%s" payment method is currently enabled', 'woocommerce'),
                            $gateway->title
                        )
                    ) . '">' . esc_attr__( 'Yes', 'woocommerce' ) . '</span>';
            } else {
                echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="'
                    . esc_attr(
                        sprintf(
                            __('The "%s" payment method is currently disabled', 'woocommerce'),
                            $gateway->title
                        )
                    ) . '">' . esc_attr__( 'No', 'woocommerce' ) . '</span>';
            }
            echo '</a>';
        }

        echo '</td>';
    }


    /**
     * @param $gateway
     * @return bool
     */
    private function isGatewaySubscriptionActive($gateway): bool
    {
        $prefix = $this->getPaymentMethodPrefix($gateway);
        $option = $prefix . 'allowed_in_subscription';
        return wc_string_to_bool($gateway->settings[$option] ?? true);
    }

    /**
     * @param $gateway
     * @return string
     */
    public function getPaymentMethodPrefix($gateway): string
    {
        $paymentMethod = substr($gateway->id, strrpos($gateway->id, '-') + 1);
        return $paymentMethod === 'credit_card' ? 'cc_' : $paymentMethod . '_';
    }

    public function pagarme_toggle_payment_subscription() {
        if (
            current_user_can('manage_woocommerce')
            && check_ajax_referer('woocommerce-toggle-payment-gateway-enabled', 'security')
            && isset($_POST['gateway_id'])
        ) {
            // Set current tab.
            $referer = wp_get_referer();
            if ($referer) {
                global $current_tab;
                parse_str(wp_parse_url($referer, PHP_URL_QUERY), $queries);
                $current_tab = $queries['tab'] ?? '';
            }

            // Load gateways.
            $payment_gateways = WC()->payment_gateways->payment_gateways();

            // Get posted gateway.
            $gateway_id = wc_clean(wp_unslash($_POST['gateway_id']));

            foreach ($payment_gateways as $gateway) {
                if (!in_array(
                    $gateway_id,
                    array($gateway->id, sanitize_title(get_class($gateway))),
                    true)
                ) {
                    continue;
                }

                $prefix = $this->getPaymentMethodPrefix($gateway);
                $optionName = $prefix . 'allowed_in_subscription';
                $enabled = $gateway->get_option($optionName, 'no');
                $option  = array(
                    'id' => $gateway->get_option_key(),
                );

                if (!wc_string_to_bool($enabled)) {
                    if ($gateway->needs_setup()) {
                        wp_send_json_error('needs_setup');
                        wp_die();
                    } else {
                        do_action('woocommerce_update_option', $option);
                        $gateway->update_option($optionName, 'yes');
                    }
                } else {
                    do_action('woocommerce_update_option', $option);
                    $gateway->update_option($optionName, 'no');
                }
                do_action('woocommerce_update_options');
                wp_send_json_success(!wc_string_to_bool($enabled));
                wp_die();
            }
        }
        wp_send_json_error( 'invalid_gateway_id' );
        wp_die();
    }
}
