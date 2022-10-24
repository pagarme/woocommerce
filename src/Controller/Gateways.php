<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

//WooCommerce
use WC_Payment_Gateway;
use WC_Order;

use Pagarme\Core\Hub\Services\HubIntegrationService;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Gateway;

class Gateways extends WC_Payment_Gateway
{
    /**
     * @var Object
     */
    public $model;

    const PAYMENT_METHOD = 'Pagar.me';

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
        $this->has_fields = true;

        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_thankyou_' . self::PAYMENT_METHOD, array($this, 'thank_you_page'));
    }

    public function payment_fields()
    {
        echo (Utils::get_template_as_string(
            'templates/checkout/main',
            array(
                'model'    => $this->model,
            )
        ));
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
            'hub_button_integration'            => $this->field_hub_button_integration(),
            'hub_environment'                   => $this->field_hub_environment(),
            'title'                             => $this->field_title(),
            'is_gateway_integration_type'       => $this->field_is_gateway_integration_type(),
            'section_payment_settings'          => $this->section_payment_settings(),
            'enable_credit_card'                => $this->field_enable_credit_card(),
            'enable_pix'                        => $this->field_enable_pix(),
            'enable_billet'                     => $this->field_enable_billet(),
            'enable_voucher'                    => $this->field_enable_voucher(),
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
            'cc_installments_min_amount'        => $this->field_cc_installment_fields('installment_min_amount'),
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
            'section_voucher'                   => $this->section_voucher(),
            'voucher_soft_descriptor'           => $this->field_voucher_soft_descriptor(),
            'field_voucher_flags'               => $this->field_voucher_flags(),
            'voucher_card_wallet'               => $this->field_voucher_card_wallet(),
            'section_antifraud'                 => $this->section_antifraud(),
            'antifraud_enabled'                 => $this->antifraud_enabled(),
            'antifraud_min_value'               => $this->antifraud_min_value(),
            'section_tools'                     => $this->section_tools(),
            'enable_logs'                       => $this->field_enabled_logs(),
        );
    }

    public function process_payment($order_id): array
    {
        $wc_order = new WC_Order($order_id);
        $formattedPost['order'] = $order_id;
        $formattedPost['fields'] = array();
        $paymentMethod = sanitize_text_field($_POST['pagarme_payment_method']);

        $formattedPost = $this->formatPOST($formattedPost, $paymentMethod);
        $_POST = $formattedPost;

        $checkout = new Checkout();
        $checkout->process_checkout_transparent($wc_order);

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($wc_order)
        );
    }

    private function formatPOST($formattedPost, $paymentMethod)
    {
        $filteredPost = array_intersect_key($_POST, array_flip(
            $this->dataToFilterFromPost($paymentMethod)
        ));

        $formattedPost = $this->addsFilteredDataInFormattedPostArray($filteredPost, $formattedPost);

        $formattedPost = $this->renameFieldsFromFormattedPost($formattedPost, $paymentMethod);

        $formattedPost = $this->formatMulticustomerCardArray($formattedPost);

        return $formattedPost;
    }

    private function addsFilteredDataInFormattedPostArray($filteredPost, $formattedPost)
    {
        foreach ($filteredPost as $key => $value) {
            array_push($formattedPost['fields'], [
                "name" => sanitize_text_field($key),
                "value" => sanitize_text_field($value)
            ]);
        }

        return $formattedPost;
    }

    private function formatMulticustomerCardArray($formattedPost)
    {
        foreach ($formattedPost['fields'] as $fieldsValue) {
            if (strstr($fieldsValue['name'], 'multicustomer_')) {
                $formattedPost = $this->addsDataInFormattedPost(
                    $fieldsValue['value'],
                    $fieldsValue['name'],
                    $formattedPost
                );
            }
        }

        return $formattedPost;
    }

    private function dataToFilterFromPost($paymentMethod)
    {
        switch ($paymentMethod) {
            case 'credit_card':
                return [
                    'brand1',
                    'pagarmetoken1',
                    'installments_card',
                    'multicustomer_card',
                    'pagarme_payment_method',
                    'enable_multicustomers_card',
                    'save_credit_card1',
                    'card_id'
                ];
            case '2_cards':
                return [
                    'card_order_value',
                    'brand2',
                    'pagarmetoken2',
                    'installments',
                    'multicustomer_card1',
                    'card_order_value2',
                    'brand3',
                    'pagarmetoken3',
                    'installments2',
                    'multicustomer_card2',
                    'pagarme_payment_method',
                    'enable_multicustomers_card1',
                    'enable_multicustomers_card2',
                    'save_credit_card2',
                    'save_credit_card3',
                    'card_id2',
                    'card_id3'
                ];
            case 'billet-and-card':
                return [
                    'card_billet_order_value',
                    'installments3',
                    'multicustomer_card_billet',
                    'billet_value',
                    'brand4',
                    'pagarmetoken4',
                    'multicustomer_billet_card',
                    'pagarme_payment_method',
                    'enable_multicustomers_billet',
                    'enable_multicustomers_card',
                    'save_credit_card4',
                    'card_id4'
                ];
            case 'billet':
                return [
                    'multicustomer_billet',
                    'pagarme_payment_method',
                    'enable_multicustomers_billet',
                ];
            case 'pix':
                return [
                    'multicustomer_pix',
                    'pagarme_payment_method',
                    'enable_multicustomers_pix',
                ];
            case 'voucher':
                return [
                    'brand6',
                    'pagarme_payment_method',
                    'pagarmetoken6',
                    'save_credit_card6',
                    'card_id6'
                ];
            default:
                return $_POST;
        }
    }


    private function addsDataInFormattedPost(
        $fieldValue,
        $fieldValueName,
        $formattedPost
    ) {
        foreach ($fieldValue as $key => $value) {
            array_push($formattedPost['fields'], [
                "name" => $fieldValueName . '[' . $key . ']',
                "value" => $value
            ]);
        }

        return $formattedPost;
    }

    private function renameFieldsFromFormattedPost($formattedPost, $paymentMethod)
    {
        foreach ($formattedPost['fields'] as $arrayFieldKey => $field) {

            $formattedPost = $this->applyForAllFields(
                $field,
                $formattedPost,
                $arrayFieldKey
            );

            if ($paymentMethod == 'credit_card') {
                $formattedPost = $this->applyForCardCreditField(
                    $field,
                    $formattedPost,
                    $arrayFieldKey
                );
            }

            if ($paymentMethod == 'billet-and-card') {
                $formattedPost = $this->applyForBilletAndCardField(
                    $field,
                    $formattedPost,
                    $arrayFieldKey
                );
            }

            if ($paymentMethod == '2_cards') {
                $formattedPost = $this->applyFor2CardField(
                    $field,
                    $formattedPost,
                    $arrayFieldKey
                );
            }

            if ($paymentMethod == 'voucher') {
                $formattedPost = $this->applyForCardCVoucherField(
                    $field,
                    $formattedPost,
                    $arrayFieldKey
                );
            }
        }

        return $formattedPost;
    }

    private function applyForAllFields(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {
        if (in_array('pagarme_payment_method', $field)) {
            $field['name'] = 'payment_method';
            $formattedPost['fields'][$arrayFieldKey] = $field;
        }

        return $formattedPost;
    }

    private function applyForCardCreditField(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {

        $dictionary = [
            'installments_card' => 'installments',
            'brand1' => 'brand',
            'save_credit_card1' => 'save_credit_card'
        ];

        foreach ($dictionary as $fieldKey => $formatedPostKey) {
            if (in_array($fieldKey, $field)) {
                $field['name'] = $formatedPostKey;
                $formattedPost['fields'][$arrayFieldKey] = $field;
            }
        }

        return $formattedPost;
    }

    private function applyForCardCVoucherField(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {
        $dictionary = [
            'card_id6' => 'card_id',
            'brand6' => 'brand',
            'save_credit_card6' => 'save_credit_card'
        ];
        foreach ($dictionary as $fieldKey => $formatedPostKey) {
            if (in_array($fieldKey, $field)) {
                $field['name'] = $formatedPostKey;
                $formattedPost['fields'][$arrayFieldKey] = $field;
            }
        }
        return $formattedPost;
    }

    private function applyForBilletAndCardField(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {

        $dictionary = [
            'card_billet_order_value' => 'card_order_value',
            'multicustomer_card_billet' => 'multicustomer_card',
            'multicustomer_billet_card' => 'multicustomer_billet',
            'brand4' => 'brand',
            'installments3' => 'installments',
            'pagarmetoken4' => 'pagarmetoken1',
            'card_id4' => 'card_id',
            'save_credit_card4' => 'save_credit_card'
        ];

        foreach ($dictionary as $fieldKey => $formatedPostKey) {
            if (in_array($fieldKey, $field)) {
                $field['name'] = $formatedPostKey;
                $formattedPost['fields'][$arrayFieldKey] = $field;
            }
        }

        if (in_array('pagarme_payment_method', $field)) {
            $field['name'] = 'payment_method';
            $field['value'] = 'billet_and_card';
            $formattedPost['fields'][$arrayFieldKey] = $field;
        }

        return $formattedPost;
    }

    private function applyFor2CardField(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {

        $dictionary = [
            'brand2' => 'brand',
            'brand3' => 'brand2',
            'pagarmetoken2' => 'pagarmetoken1',
            'pagarmetoken3' => 'pagarmetoken2',
            'card_id2' => 'card_id',
            'card_id3' => 'card_id2',
            'save_credit_card2' => 'save_credit_card',
            'save_credit_card3' => 'save_credit_card2'

        ];

        foreach ($dictionary as $fieldKey => $formatedPostKey) {
            if (in_array($fieldKey, $field)) {
                $field['name'] = $formatedPostKey;
                $formattedPost['fields'][$arrayFieldKey] = $field;
            }
        }

        return $formattedPost;
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
            'title' => __('Payment methods', 'woo-pagarme-payments'),
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

    public function field_enable_voucher()
    {
        return array(
            'title'   => __('Voucher', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable voucher', 'woo-pagarme-payments'),
            'default' => 'no'
        );
    }

    public function field_voucher_card_wallet()
    {
        return array(
            'title'    => __('Card Wallet', 'woo-pagarme-payments'),
            'desc_tip' => __('Enable Card Wallet', 'woo-pagarme-payments'),
            'type'     => 'checkbox',
            'label'    => __('Card Wallet', 'woo-pagarme-payments'),
            'default'  => 'no',
            'custom_attributes' => array(
                'data-field'   => 'voucher-card-wallet',
            ),
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
            'title'   => __('Multi-means </br>(2 Credit cards)', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable multi-means (2 Credit cards)', 'woo-pagarme-payments'),
            'default' => 'no',
        );
    }

    public function field_multimethods_billet_card()
    {
        return array(
            'title'   => __('Multi-means </br>(Boleto + Credit card)', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable multi-means (Boleto + Credit card)', 'woo-pagarme-payments'),
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

    public function section_voucher()
    {
        return array(
            'title' => __('Voucher settings', 'woo-pagarme-payments'),
            'type'  => 'title',
            'custom_attributes' => array(
                'data-field' => 'voucher-section',
            )
        );
    }

    public function field_voucher_soft_descriptor()
    {
        return array(
            'title'             => __('Soft descriptor', 'woo-pagarme-payments'),
            'desc_tip'          => __('Description that appears on the voucher bill.', 'woo-pagarme-payments'),
            'description'       => sprintf(__("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.", 'woo-pagarme-payments'), 13),
            'custom_attributes' => array(
                'data-field'     => 'voucher-soft-descriptor',
                'data-action'    => 'voucher-soft-descriptor',
                'data-element'   => 'validate',
                'maxlength'      => 22,
                'data-error-msg' => __('This field is required.', 'woo-pagarme-payments'),
            ),
        );
    }

    public function field_voucher_flags()
    {
        return array(
            'type'              => 'multiselect',
            'title'             => __('Voucher Card Brands', 'woo-pagarme-payments'),
            'select_buttons'    => false,
            'class'             => 'wc-enhanced-select',
            'options'           => array(
                'alelo'  => 'Alelo',
                'sodexo' => 'Sodexo',
                'vr'     => 'VR',
            ),
            'custom_attributes' => array(
                'data-field'   => 'voucher-flags-select',
                'data-element' => 'voucher-flags-select',
                'data-action'  => 'flags',
            ),
        );
    }

    public function section_credit_card()
    {
        return array(
            'title' => __('Credit card settings', 'woo-pagarme-payments'),
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
            'description'       => sprintf(__("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.", 'woo-pagarme-payments'), 13),
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

        $installments['installment_min_amount'] = array(
            'title'             => __('Minimum installment amount', 'woo-pagarme-payments'),
            'type'              => 'text',
            'description'       => __('Defines the minimum value that an installment can assume', 'woo-pagarme-payments'),
            'desc_tip'          => true,
            'placeholder'       => '0.00',
            'custom_attributes' => array(
                'data-field'        => 'installments-min-amount',
                'data-mask'         => '##0.00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['interest'] = array(
            'title'             => __('Initial interest rate (%)', 'woo-pagarme-payments'),
            'type'              => 'text',
            'description'       => __('Interest rate applied starting with the first installment with interest.', 'woo-pagarme-payments'),
            'desc_tip'          => true,
            'placeholder'       => '0.00',
            'custom_attributes' => array(
                'data-field'        => 'installments-interest',
                'data-mask'         => '##0.00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['interest_increase'] = array(
            'title'             => __('Incremental interest rate (%)', 'woo-pagarme-payments'),
            'type'              => 'text',
            'description'       => __('Interest rate added for each installment with interest.', 'woo-pagarme-payments'),
            'desc_tip'          => true,
            'placeholder'       => '0.00',
            'custom_attributes' => array(
                'data-field'        => 'installments-interest-increase',
                'data-mask'         => '##0.00',
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

    public function field_hub_button_integration()
    {
        return array(
            'title' => __('Hub integration', 'woo-pagarme-payments'),
            'type'  => 'hub_button_integration',
        );
    }

    public function field_hub_environment()
    {
        return array(
            'title' => __('Integration environment', 'woo-pagarme-payments'),
            'type'  => 'hub_environment',
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
            'title'       => __('Default expiration days', 'woo-pagarme-payments'),
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
            'title' => __('Anti fraud settings', 'woo-pagarme-payments'),
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
                <label for="<?php echo esc_attr($field_key); ?>">
                    <?php echo wp_kses($this->get_tooltip_html($data), ['span' => array('class' => true, 'data-tip' => true)]); ?>
                    <?php echo esc_attr($data["title"]); ?>
                </label>
            </th>
            <td class="forminp">
                <fieldset class="pix-additional-data" data-field="additional-data">
                    <input name="<?php echo esc_attr($field_key); ?>[name]" id=" <?php echo esc_attr($field_key); ?>" class="small-input-pix" type="text" value="<?php echo esc_attr($value["name"]); ?>" placeholder="<?php _e('Additional Information Name', 'woo-pagarme-payments'); ?>" />
                    <input name="<?php echo esc_attr($field_key); ?>[value]" id=" <?php echo esc_attr($field_key); ?>" class="small-input-pix" type="text" value="<?php echo esc_attr($value["value"]); ?>" placeholder="<?php _e('Additional Information Value', 'woo-pagarme-payments'); ?>" />
                </fieldset>
            </td>
        </tr>
    <?php

        return ob_get_clean();
    }

    public function generate_hub_button_integration_html($key, $data)
    {
        $hub_install_id = $this->model->settings->hub_install_id;
        $button_label = $this->model->get_hub_button_text($hub_install_id);
        $url_hub = $this->model->get_hub_url($hub_install_id);

        ob_start();
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
            </th>
            <td class="forminp flex">
                <p id="botao-hub" onclick="window.location.href = '<?php echo esc_url($url_hub); ?>';return false;" button-text="<?php echo esc_attr($button_label); ?>">
                    <button><?php echo esc_attr($button_label); ?>&nbsp;&nbsp;
                        <img src="data:image/png;base64,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qwxGjgVUoUJy/OB/8LtbszA16SYFY0DgxbY/RoDuXaBZt+3vY9tungD+Z+BKa/urDWD2k/R6W4scAaFt4OK6rcl7wOUOMPikS4bkSH6aQqEAvJ/RN+WAgVugd83trbWP0wcgQ10t3wAHh8BokbLXPd7d09nbv2da/f0AhAxyrtE/1ZMAAA0YaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/Pgo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJYTVAgQ29yZSA0LjQuMC1FeGl2MiI+CiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICB4bWxuczpHSU1QPSJodHRwOi8vd3d3LmdpbXAub3JnL3htcC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgeG1wTU06RG9jdW1lbnRJRD0iZ2ltcDpkb2NpZDpnaW1wOjE3YWFmOTUyLWNiMTYtNDc5Mi1hMmU1LWZkZGRmODY1ZWFmZCIKICAgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpiZGE4ODI3My0wNzllLTQ2NTAtYjAyYS0wOTRlZTkwOWQzOTciCiAgIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphNGVlM2VhMi02NzM5LTQwODYtYWY4ZS00NjgyNmU3ZmNiNTgiCiAgIGRjOkZvcm1hdD0iaW1hZ2UvcG5nIgogICBHSU1QOkFQST0iMi4wIgogICBHSU1QOlBsYXRmb3JtPSJXaW5kb3dzIgogICBHSU1QOlRpbWVTdGFtcD0iMTYyMTUyMzk4MDE2MTIxMCIKICAgR0lNUDpWZXJzaW9uPSIyLjEwLjI0IgogICB0aWZmOk9yaWVudGF0aW9uPSIxIgogICB4bXA6Q3JlYXRvclRvb2w9IkdJTVAgMi4xMCI+CiAgIDx4bXBNTTpIaXN0b3J5PgogICAgPHJkZjpTZXE+CiAgICAgPHJkZjpsaQogICAgICBzdEV2dDphY3Rpb249InNhdmVkIgogICAgICBzdEV2dDpjaGFuZ2VkPSIvIgogICAgICBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmRkMmI0OWM4LTVjYjQtNGYxZC04ZDFhLTBmNmE2YzcxNDRiOCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iR2ltcCAyLjEwIChXaW5kb3dzKSIKICAgICAgc3RFdnQ6d2hlbj0iMjAyMS0wNS0yMFQxMjoxOTo0MCIvPgogICAgPC9yZGY6U2VxPgogICA8L3htcE1NOkhpc3Rvcnk+CiAgPC9yZGY6RGVzY3JpcHRpb24+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz7JVoCNAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5QUUDxMowsDBfAAAAo5JREFUSMe9lr1rFEEUwGf37nK5RKKQXAJBUUM0okiKEKy0sbAJ+QsCaayMVarYKdhpK1gKwoGIIiLEQiGYFMEvhBj01ETiHrmPvdzebnZ3dj7ejI0Qc97sfW185Qxvf/PezPxmNRQSmOy+TMS7RyWSVSnlLwC6iqm74lPn7dH+MY6iDsrwiqwTIHiJMHyv7OTORwokCuBeCMGAPq+4hbP/BSik+IMFRhi+s2a8Th5IS/fqg9pWf6x6xZMHBlQsorSLK5Oqb+qhRE2TQgJuZZGapqd7kodf2Z450VaVlpePlXeN44ThacL8+wxosclKi6ZjnOj4IC1nMylM3WtCgNkIyoG9/5xb6orkBOetjf6A+o8aQQPq3Y70rnqBc7NBa6npGKcjhtq3wqAM6OPINUgYfvq3EvZXKSBvbZxSJlfc/BAHdkVKOe0G1vizTwt6I2DJ3kqD4JV6QCmlJAzf/SfJ9ssXKQveCLlfIRzYtk+cG4XqZncDUcyr2gqCG5nV63sLpxwvSCkhbC8A+AfTMdIq4Hpu+RAHZqnyLbc4jhBCyCfO1WbVxYG9K9lbSkkzTh6qcr3AmUNG+euAEGC34ksvsOdDgDOqPMqDB/qR3qFZTdP7WjmRXYnUnGqOC7YW4tljendX7+VWr0BcT4x4xB6ta6Bq9ruUQtQFIn04riGtLcHG9cQQQuhH7fjI4IRPmL8Y0xOp2jkQzIgLKXZibQAlQkQ1l0z0TCnfQyFgvWWYlMR0tr60paSKW7jU6qtOGH7SqQcXm/+FEIHlFs91BCxYm4OMk2wTMKAMz0Zi+9xOdoDy4EWIC/NuYE11ytFqB0wnN9mX6p+J6bEzCGm9INhPysnStvUtMzZ8AXcK/A0UsGey2h9i1gAAAABJRU5ErkJggg==">
                    </button>
                </p>
                <?php if ($hub_install_id) : ?>
                <p id="btn-uninstall-hub" class="btn-hub pagarme-warning" button-text="<?php echo esc_attr(__('Disintegrate', 'woo-pagarme-payments')); ?>">
                    <button type="button"><?php echo esc_attr(__('Disintegrate', 'woo-pagarme-payments')); ?>
                        <img src="data:image/png;base64,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qwxGjgVUoUJy/OB/8LtbszA16SYFY0DgxbY/RoDuXaBZt+3vY9tungD+Z+BKa/urDWD2k/R6W4scAaFt4OK6rcl7wOUOMPikS4bkSH6aQqEAvJ/RN+WAgVugd83trbWP0wcgQ10t3wAHh8BokbLXPd7d09nbv2da/f0AhAxyrtE/1ZMAAA0YaVRYdFhNTDpjb20uYWRvYmUueG1wAAAAAAA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/Pgo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJYTVAgQ29yZSA0LjQuMC1FeGl2MiI+CiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiCiAgICB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIKICAgIHhtbG5zOnN0RXZ0PSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VFdmVudCMiCiAgICB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iCiAgICB4bWxuczpHSU1QPSJodHRwOi8vd3d3LmdpbXAub3JnL3htcC8iCiAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyIKICAgIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIKICAgeG1wTU06RG9jdW1lbnRJRD0iZ2ltcDpkb2NpZDpnaW1wOjE3YWFmOTUyLWNiMTYtNDc5Mi1hMmU1LWZkZGRmODY1ZWFmZCIKICAgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpiZGE4ODI3My0wNzllLTQ2NTAtYjAyYS0wOTRlZTkwOWQzOTciCiAgIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDphNGVlM2VhMi02NzM5LTQwODYtYWY4ZS00NjgyNmU3ZmNiNTgiCiAgIGRjOkZvcm1hdD0iaW1hZ2UvcG5nIgogICBHSU1QOkFQST0iMi4wIgogICBHSU1QOlBsYXRmb3JtPSJXaW5kb3dzIgogICBHSU1QOlRpbWVTdGFtcD0iMTYyMTUyMzk4MDE2MTIxMCIKICAgR0lNUDpWZXJzaW9uPSIyLjEwLjI0IgogICB0aWZmOk9yaWVudGF0aW9uPSIxIgogICB4bXA6Q3JlYXRvclRvb2w9IkdJTVAgMi4xMCI+CiAgIDx4bXBNTTpIaXN0b3J5PgogICAgPHJkZjpTZXE+CiAgICAgPHJkZjpsaQogICAgICBzdEV2dDphY3Rpb249InNhdmVkIgogICAgICBzdEV2dDpjaGFuZ2VkPSIvIgogICAgICBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmRkMmI0OWM4LTVjYjQtNGYxZC04ZDFhLTBmNmE2YzcxNDRiOCIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iR2ltcCAyLjEwIChXaW5kb3dzKSIKICAgICAgc3RFdnQ6d2hlbj0iMjAyMS0wNS0yMFQxMjoxOTo0MCIvPgogICAgPC9yZGY6U2VxPgogICA8L3htcE1NOkhpc3Rvcnk+CiAgPC9yZGY6RGVzY3JpcHRpb24+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz7JVoCNAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5QUUDxMowsDBfAAAAo5JREFUSMe9lr1rFEEUwGf37nK5RKKQXAJBUUM0okiKEKy0sbAJ+QsCaayMVarYKdhpK1gKwoGIIiLEQiGYFMEvhBj01ETiHrmPvdzebnZ3dj7ejI0Qc97sfW185Qxvf/PezPxmNRQSmOy+TMS7RyWSVSnlLwC6iqm74lPn7dH+MY6iDsrwiqwTIHiJMHyv7OTORwokCuBeCMGAPq+4hbP/BSik+IMFRhi+s2a8Th5IS/fqg9pWf6x6xZMHBlQsorSLK5Oqb+qhRE2TQgJuZZGapqd7kodf2Z450VaVlpePlXeN44ThacL8+wxosclKi6ZjnOj4IC1nMylM3WtCgNkIyoG9/5xb6orkBOetjf6A+o8aQQPq3Y70rnqBc7NBa6npGKcjhtq3wqAM6OPINUgYfvq3EvZXKSBvbZxSJlfc/BAHdkVKOe0G1vizTwt6I2DJ3kqD4JV6QCmlJAzf/SfJ9ssXKQveCLlfIRzYtk+cG4XqZncDUcyr2gqCG5nV63sLpxwvSCkhbC8A+AfTMdIq4Hpu+RAHZqnyLbc4jhBCyCfO1WbVxYG9K9lbSkkzTh6qcr3AmUNG+euAEGC34ksvsOdDgDOqPMqDB/qR3qFZTdP7WjmRXYnUnGqOC7YW4tljendX7+VWr0BcT4x4xB6ta6Bq9ruUQtQFIn04riGtLcHG9cQQQuhH7fjI4IRPmL8Y0xOp2jkQzIgLKXZibQAlQkQ1l0z0TCnfQyFgvWWYlMR0tr60paSKW7jU6qtOGH7SqQcXm/+FEIHlFs91BCxYm4OMk2wTMKAMz0Zi+9xOdoDy4EWIC/NuYE11ytFqB0wnN9mX6p+J6bEzCGm9INhPysnStvUtMzZ8AXcK/A0UsGey2h9i1gAAAABJRU5ErkJggg==">
                    </button>
                </p>
                    <script>
                        jQuery(function ($) {
                            swal.close();
                            const content = {
                                title: '<?php echo esc_attr(__('Disintegrate?', 'woo-pagarme-payments')); ?>',
                                text: '<?php echo esc_attr(__('This removes the integration keys on your platform enabling the opportunity to effect a new integration. However, its integration still remains in our dash and it is necessary to remove the integration manually.', 'woo-pagarme-payments')); ?>',
                                type: 'info',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: '<?php echo esc_attr(__('Yes, remove keys!', 'woo-pagarme-payments')); ?>'
                            };
                            $('#btn-uninstall-hub').on('click', function (e) {
                                try {
                                    swal(content).then((result) => {
                                        if (result) {
                                            swal({
                                                title: ' ',
                                                text: '<?php echo esc_attr(__('Processing', 'woo-pagarme-payments')); ?>',
                                                allowOutsideClick: false
                                            });
                                            swal.showLoading();
                                            $.ajax({
                                                url: '<?php echo admin_url('/wc-api/pagarme-hubcommand'); ?>',
                                                type: 'POST',
                                                dataType: "json",
                                                data: JSON.stringify({
                                                    command: 'Uninstall',
                                                    force: true
                                                }),
                                                success: function (response) {
                                                    swal(
                                                        '<?php echo esc_attr(__('Disintegration Complete', 'woo-pagarme-payments')); ?>',
                                                        '<?php echo esc_attr(__('Integration keys successfully removed. Reload the page.', 'woo-pagarme-payments')); ?>',
                                                        'success'
                                                    )
                                                },
                                                fail: function (response) {
                                                    swal(
                                                        '<?php echo esc_attr(__('Disintegration Error', 'woo-pagarme-payments')); ?>',
                                                        '<?php echo esc_attr(__('Integration keys not removed.', 'woo-pagarme-payments')); ?>',
                                                        'error'
                                                    )
                                                }
                                            });
                                        }
                                    });
                                } catch (e) {
                                    swal(
                                        '<?php echo esc_attr(__('Disintegration Error', 'woo-pagarme-payments')); ?>',
                                        '<?php echo esc_attr(__('Integration keys not removed.', 'woo-pagarme-payments')); ?>',
                                        'error'
                                    )
                                }
                            });
                        });
                    </script>
                <?php endif; ?>
            </td>
        </tr>
    <?php
        return ob_get_clean();
    }

    public function generate_hub_environment_html($key, $data)
    {
        ob_start();
    ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php echo __('Integration environment', 'woo-pagarme-payments'); ?>
            </th>
            <td class="forminp">
                <?php echo esc_attr($this->model->settings->hub_environment); ?>
            </td>
        </tr>
        <?php if ($this->model->is_sandbox_mode()) : ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                <td class="forminp ">
                    <div class="pagarme-message-warning">
                        <span>
                            <?= __('Important! This store is linked to the Pagar.me test environment. This environment is intended for integration validation and does not generate real financial transactions.', 'woo-pagarme-payments'); ?>
                        </span>
                    </div>
                </td>
                </th>
            </tr>
        <?php endif; ?>
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
            .woocommerce table.form-table p.flag input.small-input {
                width: 150px;
            }

            th.align,
            input.align {
                text-align: center;
                vertical-align: middle;
            }
        </style>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php echo esc_html($this->get_tooltip_html($data)); ?>
                <label for="<?php echo esc_attr($field_key); ?>"><?php echo wp_kses_post($data['title']); ?></label>
            </th>
            <td class="forminp">
                <fieldset data-field="installments-by-flag">
                    <table class="widefat wc_input_table sortable">
                        <thead>
                            <tr>
                                <th class="align"><?php _e('Card Brand', 'woo-pagarme-payments'); ?></th>
                                <th class="align"><?php _e('Max number of installments', 'woo-pagarme-payments'); ?></th>
                                <th class="align"><?php _e('Minimum installment amount', 'woo-pagarme-payments'); ?></th>
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
                                $installment_min_amount   = isset($value['installment_min_amount'][$flag_key]) ? $value['installment_min_amount'][$flag_key] : '';
                                $no_interest       = isset($value['no_interest'][$flag_key]) ? $value['no_interest'][$flag_key] : 1;
                            ?>
                                <tr class="account ui-sortable-handle flag" data-flag="<?php echo esc_attr($flag_key); ?>">
                                    <td><input class="align" type="text" value="<?php echo esc_attr($flag_name); ?>" <?php disabled(1, true); ?> /></td>
                                    <td><input class="align" type="number" min="1" max="24" name="<?php echo esc_attr($field_key); ?>[max_installment][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_max_installment_<?php echo esc_attr($flag_key); ?>" value="<?php echo intval($max_installment); ?>" /></td>
                                    <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[installment_min_amount][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_installment_min_amount_<?php echo esc_attr($flag_key); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($installment_min_amount) ?>" /></td>
                                    <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[interest][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_interest_<?php echo esc_attr($flag_key); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($interest) ?>" /></td>
                                    <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[interest_increase][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_interest_increase_<?php echo esc_attr($flag_key); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($interest_increase) ?>" /></td>
                                    <td><input class="align" type="number" min="1" max="<?php echo intval($no_interest); ?>" name="<?php echo esc_attr($field_key); ?>[no_interest][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_no_interest_<?php echo esc_attr($flag_key); ?>" value="<?php echo intval($no_interest); ?>" /></td>
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
