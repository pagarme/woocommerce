<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Controller\Gateways;

use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Payment\Voucher\Brands;
use Woocommerce\Pagarme\Model\Payment\Voucher\BrandsInterface;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Voucher
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class CreditCard extends AbstractGateway
{
    /** @var string */
    protected $method = 'credit-card';

    /**
     * @return array
     */
    public function append_form_fields()
    {
        return [
            'enable_credit_card'                => $this->field_enable_credit_card(),
            'cc_operation_type' => $this->field_cc_operation_type(),
            'cc_soft_descriptor' => $this->field_cc_soft_descriptor(),
            'cc_flags' => $this->field_cc_flags(),
            'cc_allow_save' => $this->field_cc_allow_save(),
            'cc_installment_type' => $this->field_cc_installment_type(),
            'cc_installments_maximum' => $this->field_cc_installment_fields('maximum'),
            'cc_installments_min_amount' => $this->field_cc_installment_fields('installment_min_amount'),
            'cc_installments_interest' => $this->field_cc_installment_fields('interest'),
            'cc_installments_interest_increase' => $this->field_cc_installment_fields('interest_increase'),
            'cc_installments_without_interest' => $this->field_cc_installment_fields('without_interest'),
            'cc_installments_by_flag' => $this->field_cc_installment_fields('flags'),
            'section_antifraud'                 => $this->section_antifraud(),
            'antifraud_enabled'                 => $this->antifraud_enabled(),
            'antifraud_min_value'               => $this->antifraud_min_value(),
        ];
    }

    /**
     * @return array
     */
    public function field_enable_credit_card()
    {
        return array(
            'title'   => __('Credit card', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable credit card', 'woo-pagarme-payments'),
            'default' => 'yes',
        );
    }

    /**
     * @return array
     */
    public function field_cc_operation_type()
    {
        return array(
            'type' => 'select',
            'title' => __('Operation Type', 'woo-pagarme-payments'),
            'class' => 'wc-enhanced-select',
            'default' => 1,
            'options' => array(
                1 => __('Authorize', 'woo-pagarme-payments'),
                2 => __('Authorize and Capture', 'woo-pagarme-payments'),
            ),
        );
    }

    /**
     * @return array
     */
    public function field_cc_soft_descriptor()
    {
        return array(
            'title' => __('Soft descriptor', 'woo-pagarme-payments'),
            'desc_tip' => __('Description that appears on the credit card bill.', 'woo-pagarme-payments'),
            'description' => sprintf(__("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.", 'woo-pagarme-payments'), 13),
            'custom_attributes' => array(
                'data-field' => 'soft-descriptor',
                'data-action' => 'soft-descriptor',
                'data-element' => 'validate',
                'maxlength' => 13,
                'data-error-msg' => __('This field is required.', 'woo-pagarme-payments'),
            ),
        );
    }

    /**
     * @return array
     */
    public function field_cc_allow_save()
    {
        return array(
            'title' => __('Card wallet', 'woo-pagarme-payments'),
            'type' => 'checkbox',
            'label' => __('Enable card wallet', 'woo-pagarme-payments'),
            'default' => 'no',
            'description' => __('Allows for cards to be saved for future purchases.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'custom_attributes' => array(
                'data-field' => 'cc-allow-save',
            ),
        );
    }

    /**
     * @return array
     */
    public function field_cc_flags()
    {
        return array(
            'type' => 'multiselect',
            'title' => __('Card Brands', 'woo-pagarme-payments'),
            'select_buttons' => false,
            'class' => 'wc-enhanced-select',
            'options' => $this->model->settings->get_flags_list(),
            'custom_attributes' => array(
                'data-field' => 'flags-select',
                'data-element' => 'flags-select',
                'data-action' => 'flags',
            ),
        );
    }

    /**
     * @return array
     */
    public function field_cc_installment_type()
    {
        return array(
            'title' => __('Installment configuration', 'woo-pagarme-payments'),
            'type' => 'select',
            'class' => 'wc-enhanced-select',
            'label' => __('Choose the installment configuration', 'woo-pagarme-payments'),
            'default' => 1,
            'options' => array(
                Gateway::CC_TYPE_SINGLE => __('For all card brands', 'woo-pagarme-payments'),
                Gateway::CC_TYPE_BY_FLAG => __('By card brand', 'woo-pagarme-payments'),
            ),
            'custom_attributes' => array(
                'data-element' => 'installments-type-select',
                'data-action' => 'installments-type',
            ),
        );
    }

    /**
     * @return array
     */
    public function field_cc_installment_fields($field)
    {
        $installments = array();

        $installments['maximum'] = array(
            'title' => __('Max number of installments', 'woo-pagarme-payments'),
            'type' => 'select',
            'default' => 12,
            'options' => $this->model->get_installment_options(),
            'custom_attributes' => array(
                'data-field' => 'installments-maximum',
            ),
        );

        $installments['installment_min_amount'] = array(
            'title' => __('Minimum installment amount', 'woo-pagarme-payments'),
            'type' => 'text',
            'description' => __('Defines the minimum value that an installment can assume', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'placeholder' => '0.00',
            'custom_attributes' => array(
                'data-field' => 'installments-min-amount',
                'data-mask' => '##0.00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['interest'] = array(
            'title' => __('Initial interest rate (%)', 'woo-pagarme-payments'),
            'type' => 'text',
            'description' => __('Interest rate applied starting with the first installment with interest.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'placeholder' => '0.00',
            'custom_attributes' => array(
                'data-field' => 'installments-interest',
                'data-mask' => '##0.00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['interest_increase'] = array(
            'title' => __('Incremental interest rate (%)', 'woo-pagarme-payments'),
            'type' => 'text',
            'description' => __('Interest rate added for each installment with interest.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'placeholder' => '0.00',
            'custom_attributes' => array(
                'data-field' => 'installments-interest-increase',
                'data-mask' => '##0.00',
                'data-mask-reverse' => 'true',
            ),
        );

        $installments['without_interest'] = array(
            'title' => __('Number of installments without interest', 'woo-pagarme-payments'),
            'type' => 'select',
            'default' => 3,
            'options' => $this->model->get_installment_options(),
            'custom_attributes' => array(
                'data-field' => 'installments-without-interest',
            ),
        );

        $installments['flags'] = array(
            'title' => __('Settings by card brand', 'woo-pagarme-payments'),
            'type' => 'installments_by_flag',
        );

        return $installments[$field];
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
}
