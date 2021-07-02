<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

//WooCommerce
use WC_Payment_Gateway;
use WC_Order;

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Gateway;

class Gateways extends WC_Payment_Gateway
{
    /**
     * @var Object
     */
    public $model;

    public function __construct()
    {
        $this->model = new Gateway();

        $this->id                 = 'woo-pagarme-payments';
        $this->method_title       = __('Pagar.me Payments', 'woo-pagarme-payments');
        $this->method_description = __('Payment Gateway Pagar.me', 'woo-pagarme-payments');
        $this->has_fields         = false;
        $this->icon               = Core::plugins_url('assets/images/logo.png');

        $this->init_form_fields();
        $this->init_settings();

        $this->enabled     = $this->get_option('enabled', 'no');
        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');

        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thank_you_page'));
    }

    /**
     * Output the admin options table.
     *
     * @since 1.0
     * @param null
     * @return Void
     */
    public function admin_options()
    {
        printf(
            '<h3>%s</h3>
			 <div %s>
			 	<table class="form-table">%s</table>
			 </div>',
            __('General', 'woo-pagarme-payments'),
            Utils::get_component('settings'),
            $this->generate_settings_html($this->get_form_fields(), false)
        );
    }

    /**
     * Return the name of the option in the WP DB.
     * @since 1.0
     * @return string
     */
    public function get_option_key()
    {
        return $this->model->settings->get_option_key();
    }

    public function is_available()
    {
        return ($this->model->settings->is_enabled() && !$this->get_errors() && $this->model->supported_currency());
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled'                           => $this->field_enabled(),
            'title'                             => $this->field_title(),
            'description'                       => $this->field_description(),
            'environment'                       => $this->field_environment(),
            'sandbox_public_key'                => $this->field_sandbox_public_key(),
            'sandbox_secret_key'                => $this->field_sandbox_secret_key(),
            'production_public_key'             => $this->field_production_public_key(),
            'production_secret_key'             => $this->field_production_secret_key(),
            'is_gateway_integration_type'       => $this->field_is_gateway_integration_type(),
            'section_payment_settings'          => $this->section_payment_settings(),
            'enable_credit_card'                => $this->field_enable_credit_card(),
            'enable_pix'                        => $this->field_enable_pix(),
            'enable_billet'                     => $this->field_enable_billet(),
            'multimethods_2_cards'              => $this->field_multimethods_2_cards(),
            'multimethods_billet_card'          => $this->field_multimethods_billet_card(),
            'multicustomers'                    => $this->field_multicustomers(),
            'section_credit_card'               => $this->section_credit_card(),
            'cc_operation_type'                 => $this->field_cc_operation_type(),
            'cc_soft_descriptor'                => $this->field_cc_soft_descriptor(),
            'cc_flags'                          => $this->field_cc_flags(),
            'cc_allow_save'                     => $this->field_cc_allow_save(),
            'cc_installment_type'               => $this->field_cc_installment_type(),
            'cc_installments_maximum'           => $this->field_cc_installment_fields('maximum'),
            'cc_installments_interest'          => $this->field_cc_installment_fields('interest'),
            'cc_installments_interest_increase' => $this->field_cc_installment_fields('interest_increase'),
            'cc_installments_without_interest'  => $this->field_cc_installment_fields('without_interest'),
            'cc_installments_by_flag'           => $this->field_cc_installment_fields('flags'),
            'section_pix'                       => $this->section_pix(),
            'pix_qrcode_expiration_time'        => $this->field_pix_qrcode_expiration_time(),
            'pix_additional_data'               => $this->field_pix_additional_data(),
            'section_billet'                    => $this->section_billet(),
            'billet_bank'                       => $this->field_billet_bank(),
            'billet_deadline_days'              => $this->field_billet_deadline_days(),
            'billet_instructions'               => $this->field_billet_instructions(),
            'section_antifraud'                 => $this->section_antifraud(),
            'antifraud_enabled'                 => $this->antifraud_enabled(),
            'antifraud_min_value'               => $this->antifraud_min_value(),
            'section_tools'                     => $this->section_tools(),
            'enable_logs'                       => $this->field_enabled_logs(),
        );
    }

    public function process_payment($order_id)
    {
        $wc_order = new WC_Order($order_id);

        return array(
            'result'   => 'success',
            'redirect' => $wc_order->get_checkout_payment_url(true),
        );
    }

    public function receipt_page($order_id)
    {
        $this->checkout_transparent($order_id);
    }

    public function thank_you_page($order_id)
    {
        $order = new WC_Order($order_id);

        require_once Core::get_file_path('thank-you-page.php', 'templates/');
    }

    public function checkout_transparent($order_id)
    {
        $wc_order = new WC_Order($order_id);

        require_once Core::get_file_path('main.php', 'templates/checkout/');
    }

    public function section_payment_settings()
    {
        return array(
            'title' => __('Payment settings', 'woo-pagarme-payments'),
            'type'  => 'title',
        );
    }

    public function field_enabled()
    {
        return array(
            'title'   => __('Enable', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable Pagar.me', 'woo-pagarme-payments'),
            'default' => 'no',
        );
    }

    public function field_title()
    {
        return array(
            'title'       => __('Checkout title', 'woo-pagarme-payments'),
            'description' => __('Name shown to the customer in the checkout page.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'default'     => __('Pagar.me', 'woo-pagarme-payments'),
        );
    }

    public function field_description()
    {
        return array(
            'title'   => __('Description', 'woo-pagarme-payments'),
            'description' => __('Description shown below the title in the checkout page.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'default' => __('Pay with credit card or boleto', 'woo-pagarme-payments'),
        );
    }

    public function field_environment()
    {
        return array(
            'type'    => 'select',
            'title'   => __('Environment', 'woo-pagarme-payments'),
            'class'   => 'wc-enhanced-select',
            'default' => 'sandbox',
            'options' => array(
                'sandbox'    => 'Sandbox',
                'production' => __('Production', 'woo-pagarme-payments'),
            ),
            'custom_attributes' => array(
                'data-action'  => 'environment',
                'data-element' => 'environment-select',
            ),
        );
    }

    public function field_sandbox_secret_key()
    {
        return array(
            'title'             => __('Sandbox secret key', 'woo-pagarme-payments'),
            'custom_attributes' => array(
                'data-field' => 'sandbox-secret-key',
            ),
        );
    }

    public function field_sandbox_public_key()
    {
        return array(
            'title'             => __('Sandbox public key', 'woo-pagarme-payments'),
            'custom_attributes' => array(
                'data-field' => 'sandbox-public-key',
            ),
        );
    }

    public function field_production_secret_key()
    {
        return array(
            'title'             => __('Production secret key', 'woo-pagarme-payments'),
            'custom_attributes' => array(
                'data-field' => 'production-secret-key',
            ),
        );
    }

    public function field_production_public_key()
    {
        return array(
            'title'             => __('Production public key', 'woo-pagarme-payments'),
            'custom_attributes' => array(
                'data-field' => 'production-public-key',
            ),
        );
    }

	public function field_is_gateway_integration_type()
	{
		return array(
			'title'   => __('Advanced settings', 'woo-pagarme-payments'),
			'type'    => 'checkbox',
			'label'   => __('Enable advanced settings', 'woo-pagarme-payments'),
			'default' => 'no',
            'description' => __('Configurations that only works for Gateway customers, who have a direct contract with an acquirer.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
			'custom_attributes' => array(
				'data-action'  => 'is-gateway-integration-type',
			),
		);
	}

    public function field_enable_credit_card()
    {
        return array(
            'title'   => __('Credit card', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable credit card', 'woo-pagarme-payments'),
            'default' => 'yes',
        );
    }

    public function field_enable_pix()
    {
        return array(
            'title'   => __('Pix', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable pix', 'woo-pagarme-payments'),
            'default' => 'no'
        );
    }

	public function field_enable_billet()
    {
        return array(
            'title'   => __('Boleto', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable boleto', 'woo-pagarme-payments'),
            'default' => 'yes',
            'custom_attributes' => array(
                'data-action'  => 'enable-billet',
                'data-requires-field' => 'billet-bank',
            ),
        );
    }

    public function field_multimethods_2_cards()
    {
        return array(
            'title'   => __('Multi-means </br>(2 Credit Cards)', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable multi-means (2 Credit Cards)', 'woo-pagarme-payments'),
            'default' => 'no',
        );
    }

    public function field_multimethods_billet_card()
    {
        return array(
            'title'   => __('Multi-means </br>(Boleto + Credit Card)', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable multi-means (Boleto + Credit Card)', 'woo-pagarme-payments'),
            'default' => 'no',
            'custom_attributes' => array(
                'data-action'  => 'enable-multimethods-billet-card',
                'data-requires-field' => 'billet-bank',
            ),
        );
    }

    public function field_multicustomers()
    {
        return array(
            'title'   => __('Multi-buyers', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable multi-buyers', 'woo-pagarme-payments'),
            'default' => 'no',
        );
    }

    public function section_credit_card()
    {
        return array(
            'title' => __('Credit Card settings', 'woo-pagarme-payments'),
            'type'  => 'title',
        );
    }

    public function field_cc_operation_type()
    {
        return array(
            'type'    => 'select',
            'title'   => __('Operation Type', 'woo-pagarme-payments'),
            'class'   => 'wc-enhanced-select',
            'default' => 1,
            'options' => array(
                1 => __('Authorize', 'woo-pagarme-payments'),
                2 => __('Authorize and Capture', 'woo-pagarme-payments'),
            ),
        );
    }

    public function field_cc_soft_descriptor()
    {
        return array(
            'title'             => __('Soft descriptor', 'woo-pagarme-payments'),
            'desc_tip'          => __('Description that appears on the credit card bill.', 'woo-pagarme-payments'),
            'description'       => sprintf(__( "Max length of <span id='max_length_span'>%s</span> characters.", 'woo-pagarme-payments' ), 13),
            'custom_attributes' => array(
                'data-field'     => 'soft-descriptor',
                'data-action'    => 'soft-descriptor',
                'data-element'   => 'validate',
                'maxlength'      => 13,
                'data-error-msg' => __('This field is required.', 'woo-pagarme-payments'),
            ),
        );
    }

    public function field_cc_allow_save()
    {
        return array(
            'title'   => __('Card wallet', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable card wallet', 'woo-pagarme-payments'),
            'default' => 'no',
            'description' => __('Allows for cards to be saved for future purchases.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'custom_attributes' => array(
                'data-field' => 'cc-allow-save',
            ),
        );
    }

    public function field_cc_flags()
    {
        return array(
            'type'              => 'multiselect',
            'title'             => __('Card Brands', 'woo-pagarme-payments'),
            'select_buttons'    => false,
            'class'             => 'wc-enhanced-select',
            'options'           => $this->model->settings->get_flags_list(),
            'custom_attributes' => array(
                'data-field'   => 'flags-select',
                'data-element' => 'flags-select',
                'data-action'  => 'flags',
            ),
        );
    }

    public function field_cc_manual_capture()
    {
        return array(
            'title'   => __('Manual Capture', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable Manual Capture', 'woo-pagarme-payments'),
            'default' => 'yes',
        );
    }

    public function field_cc_installment_type()
    {
        return array(
            'title'   => __('Installment configuration', 'woo-pagarme-payments'),
            'type'    => 'select',
            'class'   => 'wc-enhanced-select',
            'label'   => __('Choose the installment configuration', 'woo-pagarme-payments'),
            'default' => 1,
            'options' => array(
                Gateway::CC_TYPE_SINGLE  => __('For all card brands', 'woo-pagarme-payments'),
                Gateway::CC_TYPE_BY_FLAG => __('By card brand', 'woo-pagarme-payments'),
            ),
            'custom_attributes' => array(
                'data-element' => 'installments-type-select',
                'data-action'  => 'installments-type',
            ),
        );
    }

    public function field_cc_installment_fields($field)
    {
        $installments = array();

        $installments['maximum'] = array(
            'title'             => __('Max number of installments', 'woo-pagarme-payments'),
            'type'              => 'select',
            'default'           => 12,
            'options'           => $this->model->get_installment_options(),
            'custom_attributes' => array(
                'data-field' => 'installments-maximum',
            ),
        );

        $installments['interest'] = array(
            'title'             => __('Initial interest rate (%)', 'woo-pagarme-payments'),
            'type'              => 'text',
            'description'       => __('Interest rate applied starting with the first installment with interest.', 'woo-pagarme-payments'),
            'desc_tip'          => true,
            'placeholder'       => '0,00',
            'custom_attributes' => array(
                'data-field'        => 'installments-interest',
                'data-mask'         => '##0,00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['interest_increase'] = array(
            'title'             => __('Incremental interest rate (%)', 'woo-pagarme-payments'),
            'type'              => 'text',
            'description'       => __('Interest rate added for each installment with interest.', 'woo-pagarme-payments'),
            'desc_tip'          => true,
            'placeholder'       => '0,00',
            'custom_attributes' => array(
                'data-field'        => 'installments-interest-increase',
                'data-mask'         => '##0,00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['without_interest'] = array(
            'title'             => __('Number of installments without interest', 'woo-pagarme-payments'),
            'type'              => 'select',
            'default'           => 3,
            'options'           => $this->model->get_installment_options(),
            'custom_attributes' => array(
                'data-field' => 'installments-without-interest',
            ),
        );

        $installments['flags'] = array(
            'title' => __('Settings by card brand', 'woo-pagarme-payments'),
            'type'  => 'installments_by_flag',
        );

        return $installments[$field];
    }

    public function section_pix()
    {
        return array(
            'title' => __('Pix settings', 'woo-pagarme-payments'),
            'type'  => 'title',
        );
    }

    public function field_pix_qrcode_expiration_time()
    {
        return array(
            'title'       => __('QR code expiration time', 'woo-pagarme-payments'),
            'description' => __('Expiration time in seconds of the generated pix QR code.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'placeholder' => 3500,
            'default'     => 3500,
            'custom_attributes' => array(
                'data-mask'         => '##0',
                'data-mask-reverse' => 'true',
            ),
        );
    }

    public function field_pix_additional_data()
    {
        return array(
            'title'       => __('Additional information', 'woo-pagarme-payments'),
            'description' => __('Set of key and value used to add information to the generated pix. This will be visible to the buyer during the payment process.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'type'        => 'pix_additional_data',
        );
    }

    public function section_billet()
    {
        return array(
            'title' => __('Boleto settings', 'woo-pagarme-payments'),
            'type'  => 'title',
        );
    }

    public function field_billet_bank()
    {
        return array(
            'type'    => 'select',
            'title'   => __('Bank', 'woo-pagarme-payments'),
            'class'   => 'wc-enhanced-select',
            'default' => 0,
            'options' => array(
                '237' => 'Banco Bradesco S.A.',
                '341' => 'Banco Itaú S.A.',
                '033' => 'Banco Santander S.A.',
                '745' => 'Banco Citibank S.A.',
                '001' => 'Banco do Brasil S.A.',
                '104' => 'Caixa Econômica Federal',
            ),
            'custom_attributes' => array(
                'data-field' => 'billet-bank',
            )
        );
    }

    public function field_billet_deadline_days()
    {
        return array(
            'title'       => __('Default expiration day', 'woo-pagarme-payments'),
            'description' => __('Number of days until the expiration date of the generated boleto.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'placeholder' => 5,
            'default'     => 5,
            'custom_attributes' => array(
                'data-mask'         => '##0',
                'data-mask-reverse' => 'true',
            ),
        );
    }

    public function field_billet_instructions()
    {
        return array(
            'title'       => __('Payment instructions', 'woo-pagarme-payments'),
            'type'        => 'text',
            'description' => __('Instructions printed on the boleto.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
        );
    }

    public function section_antifraud()
    {
        return array(
            'title' => __('Anti-fraud settings', 'woo-pagarme-payments'),
            'type'  => 'title',
            'custom_attributes' => array(
                'data-field' => 'antifraud-section',
            )
        );
    }

    public function antifraud_enabled()
    {
        return array(
            'title'   => __('Enable', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable anti fraud', 'woo-pagarme-payments'),
            'default' => 'no',
            'custom_attributes' => array(
                'data-field' => 'antifraud-enabled',
            )
        );
    }

    public function antifraud_min_value()
    {
        return array(
            'title'             => __('Minimum amount', 'woo-pagarme-payments'),
            'type'              => 'text',
            'description'       => __('Minimum order amount to send it to the anti fraud', 'woo-pagarme-payments'),
            'desc_tip'          => true,
            'placeholder'       => '100,00',
            'custom_attributes' => array(
                'data-mask'         => '#.##0,00',
                'data-mask-reverse' => 'true',
                'data-field'        => 'antifraud-min-value',
            ),
        );
    }

    public function section_tools()
    {
        return array(
            'title' => __('Tools', 'woo-pagarme-payments'),
            'type'  => 'title',
        );
    }

    public function field_enabled_logs()
    {
        return array(
            'title'       => __('Logs', 'woo-pagarme-payments'),
            'type'        => 'checkbox',
            'label'       => __('Enable', 'woo-pagarme-payments'),
            'default'     => 'no',
            'description' => __('Log Pagar.me events, you can check this log in WooCommerce>Status>Logs.', 'woo-pagarme-payments'),
        );
    }

    /**
     * Get HTML for descriptions.
     *
     * @param  array $data
     * @return string
     */
    public function get_description_html($data)
    {
        if ($data['desc_tip'] === true) {
            return;
        } elseif (!empty($data['desc_tip'])) {
            $description = $data['description'];
        } elseif (!empty($data['description'])) {
            $description = $data['description'];
        } else {
            return;
        }

        return sprintf(
            '<p class="description %s">%s</p>',
            sanitize_html_class(Utils::get_value_by($data, 'class_p')),
            strip_tags($description, '<a><span>')
        );
    }

    public function generate_pix_additional_data_html($key, $data)
    {
        $field_key = $this->get_field_key($key);

        $value = (array) $this->get_option($key, array());
        ob_start();

    ?>
        <style>
            .woocommerce table.form-table fieldset.pix-additional-data input.small-input-pix {
                width: 198px;
            }
        </style>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo $field_key; ?>">
                    <?php echo $this->get_tooltip_html($data); ?>
                    <?php echo $data["title"]; ?>
                </label>
            </th>
            <td class="forminp">
                <fieldset class="pix-additional-data" data-field="additional-data">
                    <input name="<?php echo esc_attr($field_key); ?>[name]" id=" <?php echo esc_attr($field_key); ?>" class="small-input-pix" type="text" value="<?php echo $value["name"]; ?>" placeholder="<?php _e('Additional Information Name', 'woo-pagarme-payments'); ?>" />
                    <input name="<?php echo esc_attr($field_key); ?>[value]" id=" <?php echo esc_attr($field_key); ?>" class="small-input-pix" type="text" value="<?php echo $value["value"]; ?>" placeholder="<?php _e('Additional Information Value', 'woo-pagarme-payments'); ?>" />
                </fieldset>
            </td>
        </tr>
    <?php

        return ob_get_clean();
    }

    public function generate_installments_by_flag_html($key, $data)
    {
        $field_key = $this->get_field_key($key);
        $defaults  = array(
            'title'             => '',
            'disabled'          => false,
            'class'             => '',
            'css'               => '',
            'placeholder'       => '',
            'type'              => 'text',
            'desc_tip'          => false,
            'description'       => '',
            'custom_attributes' => array(),
        );

        $data  = wp_parse_args($data, $defaults);
        $value = (array) $this->get_option($key, array());
        $flags = $this->model->settings->get_flags_list();

        ob_start();

    ?>
        <style>
            .woocommerce table.form-table p.flag input.small-input { width: 150px; }
            th.align, input.align {
                text-align: center;
                vertical-align: middle;
            }
        </style>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php echo $this->get_tooltip_html($data); ?>
                <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
            </th>
            <td class="forminp">
                <fieldset data-field="installments-by-flag">
                    <table class="widefat wc_input_table sortable">
                        <thead>
                        <tr>
                            <th class="align"><?php _e('Card Brand', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Max number of installment', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Initial interest rate (%)', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Incremental interest rate (%)', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Number of installments<br/>without interest', 'woo-pagarme-payments'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="accounts ui-sortable">
                            <?php
                            foreach ($flags as $flag_key => $flag_name) :
                                $interest          = isset($value['interest'][$flag_key]) ? $value['interest'][$flag_key] : '';
                                $interest_increase = isset($value['interest_increase'][$flag_key]) ? $value['interest_increase'][$flag_key] : '';
                                $max_installment   = isset($value['max_installment'][$flag_key]) ? $value['max_installment'][$flag_key] : 12;
                                $no_interest       = isset($value['no_interest'][$flag_key]) ? $value['no_interest'][$flag_key] : 1;
                            ?>
                                <tr class="account ui-sortable-handle flag" data-flag="<?php echo $flag_key; ?>">
                                    <td><input class="align" type="text" value="<?php echo $flag_name; ?>" <?php disabled(1, true); ?> /></td>
                                    <td><input class="align" type="number" min="1" max="24" name="<?php echo esc_attr($field_key); ?>[max_installment][<?php echo $flag_key; ?>]" id="<?php echo esc_attr($field_key); ?>_max_installment_<?php echo $flag_key; ?>" value="<?php echo intval($max_installment); ?>" /></td>
                                    <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[interest][<?php echo $flag_key; ?>]" id="<?php echo esc_attr($field_key); ?>_interest_<?php echo $flag_key; ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($interest) ?>" /></td>
                                    <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[interest_increase][<?php echo $flag_key; ?>]" id="<?php echo esc_attr($field_key); ?>_interest_increase_<?php echo $flag_key; ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($interest_increase) ?>" /></td>
                                    <td><input class="align" type="number" min="1" max="<?php echo intval($no_interest); ?>" name="<?php echo esc_attr($field_key); ?>[no_interest][<?php echo $flag_key; ?>]" id="<?php echo esc_attr($field_key); ?>_no_interest_<?php echo $flag_key; ?>" value="<?php echo intval($no_interest); ?>" /></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </fieldset>
            </td>
        </tr>
<?php

        return ob_get_clean();
    }

    public function validate_installments_by_flag_field($key, $value)
    {
        return $value;
    }

    public function validate_pix_additional_data_field($key, $value)
    {
        return $value;
    }
}
