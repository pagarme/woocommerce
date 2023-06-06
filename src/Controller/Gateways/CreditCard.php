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

use WC_Admin_Settings;
use Woocommerce\Pagarme\Controller\Gateways\Exceptions\InvalidOptionException;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Gateway;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class CreditCard
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class CreditCard extends AbstractGateway
{
    /** @var string */
    protected $method = \Woocommerce\Pagarme\Model\Payment\CreditCard::PAYMENT_CODE;

    /**
     * @return array
     */
    public function append_form_fields()
    {
        return [
            'cc_operation_type' => $this->field_cc_operation_type(),
            'cc_soft_descriptor' => $this->field_cc_soft_descriptor(),
            'cc_flags' => $this->field_cc_flags(),
            'cc_installment_type' => $this->field_cc_installment_type(),
            'cc_installments_maximum' => $this->field_cc_installment_fields('maximum'),
            'cc_installments_min_amount' => $this->field_cc_installment_fields('installment_min_amount'),
            'cc_installments_interest' => $this->field_cc_installment_fields('interest'),
            'cc_installments_interest_increase' => $this->field_cc_installment_fields('interest_increase'),
            'cc_installments_without_interest' => $this->field_cc_installment_fields('without_interest'),
            'cc_installments_by_flag' => $this->field_cc_installment_fields('flags'),
            'cc_allow_save' => $this->field_cc_allow_save(),
        ];
    }

    /**
     * @return array
     */
    protected function gateway_form_fields()
    {
        return [
            'section_antifraud' => $this->section_antifraud(),
            'antifraud_enabled' => $this->antifraud_enabled(),
            'antifraud_min_value' => $this->antifraud_min_value(),
        ];
    }

    /**
     * @return array
     */
    public function field_enabled()
    {
        return [
            'title'   => __('Enable/Disable', 'woocommerce'),
            'type'     => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label'   => __('Enable credit card', 'woo-pagarme-payments'),
            'old_name'    => 'enable_credit_card',
            'default'     => $this->config->getData('enable_credit_card') ?? strtolower(Yesno::NO),
        ];
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
            'default' => $this->config->getCcOperationType() ?? 1,
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
        $maxLength = $this->model->get_soft_descriptor_max_length($this->isGatewayType());

        return array(
            'title' => __('Soft descriptor', 'woo-pagarme-payments'),
            'desc_tip' => __('Description that appears on the credit card bill.', 'woo-pagarme-payments'),
            'description' => sprintf(__("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.",
                'woo-pagarme-payments'), $maxLength),
            'default' => $this->config->getData('cc_soft_descriptor') ?? '',
            'custom_attributes' => array(
                'data-field' => 'soft-descriptor',
                'data-field-validate' => 'required|max-length',
                'data-max-length' => $maxLength,
                'data-error-message-required' => __('This field is required.', 'woo-pagarme-payments'),
                'data-error-message-max-length' => sprintf(__('This field has exceeded the %d character limit.', 'woo-pagarme-payments'), $maxLength),
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
            'type'     => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label' => __('Enable card wallet', 'woo-pagarme-payments'),
            'default'     => $this->config->getData('cc_allow_save') ?? strtolower(Yesno::NO),
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
            'options' => $this->getBrandsList(),
            'default'     => $this->config->getData('cc_flags') ?? '',
            'custom_attributes' => array(
                'data-field' => 'flags-select',
                'data-element' => 'flags-select',
                'data-action' => 'flags',
                'data-field-validate' => 'required',
                'data-error-message-required' => __('This field is required.', 'woo-pagarme-payments'),
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
            'default' => $this->config->getData('cc_installment_type') ?? 1,
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
            'default' => $this->config->getData('cc_installments_maximum') ?? 12,
            'options' => $this->model->get_installment_options($this->isGatewayType()),
            'custom_attributes' => array(
                'data-field' => 'installments-maximum',
            ),
        );

        $installments['installment_min_amount'] = array(
            'title' => __('Minimum installment amount', 'woo-pagarme-payments'),
            'type' => 'text',
            'default' => $this->config->getData('cc_installments_min_amount') ?? '',
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
            'default' => $this->config->getData('cc_installments_interest') ?? '',
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
            'default' => $this->config->getData('cc_installments_interest_increase') ?? '',
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
            'default' => $this->config->getData('cc_installments_without_interest') ?? 3,
            'options' => $this->model->get_installment_options($this->isGatewayType()),
            'custom_attributes' => array(
                'data-field' => 'installments-without-interest',
            ),
        );

        $installments['flags'] = array(
            'title' => __('Settings by card brand', 'woo-pagarme-payments'),
            'type' => 'installments_by_flag',
            'default' => $this->config->getData('cc_installments_by_flag') ?? '',
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
            'type'     => 'select',
            'default' => $this->config->getData('antifraud_enabled') ?? strtolower(Yesno::NO),
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label'   => __('Enable anti fraud', 'woo-pagarme-payments'),
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
            'default'           => $this->config->getData('antifraud_min_value') ?? '',
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
        $flags = $this->getBrandsList();
        $max_installment = $this->model->get_installments_maximum_quantity($this->isGatewayType());

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
                            $max_installment_value   = isset($value['max_installment'][$flag_key]) && $value['max_installment'][$flag_key] < $max_installment ? $value['max_installment'][$flag_key] : $max_installment;
                            $installment_min_amount   = isset($value['installment_min_amount'][$flag_key]) ? $value['installment_min_amount'][$flag_key] : '';
                            $no_interest       = isset($value['no_interest'][$flag_key]) ? $value['no_interest'][$flag_key] : 1;
                            ?>
                            <tr class="account ui-sortable-handle flag" data-flag="<?php echo esc_attr($flag_key); ?>">
                                <td><input class="align" type="text" value="<?php echo esc_attr($flag_name); ?>" <?php disabled(1, true); ?> /></td>
                                <td><input class="align" type="number" min="1" max="<?php echo intval($max_installment); ?>" name="<?php echo esc_attr($field_key); ?>[max_installment][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_max_installment_<?php echo esc_attr($flag_key); ?>" value="<?php echo intval($max_installment_value); ?>" data-field="installments-maximum-by-flag" /></td>
                                <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[installment_min_amount][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_installment_min_amount_<?php echo esc_attr($flag_key); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($installment_min_amount) ?>" /></td>
                                <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[interest][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_interest_<?php echo esc_attr($flag_key); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($interest) ?>" /></td>
                                <td><input class="align" type="text" placeholder="0,00" data-mask="##0,00" data-mask-reverse="true" name="<?php echo esc_attr($field_key); ?>[interest_increase][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_interest_increase_<?php echo esc_attr($flag_key); ?>" value="<?php echo /*phpcs:ignore*/ wc_format_localized_price($interest_increase) ?>" /></td>
                                <td><input class="align" type="number" min="1" max="<?php echo intval($max_installment); ?>" name="<?php echo esc_attr($field_key); ?>[no_interest][<?php echo esc_attr($flag_key); ?>]" id="<?php echo esc_attr($field_key); ?>_no_interest_<?php echo esc_attr($flag_key); ?>" value="<?php echo intval($no_interest); ?>" data-field="installments-without-interest-by-flag" /></td>
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

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function validate_installments_by_flag_field($key, $value)
    {
        foreach ($value['max_installment'] as $brand => $maxInstallment) {
            if($maxInstallment < $value['no_interest'][$brand]) {
                $value['no_interest'][$brand] = $maxInstallment;
            }
        }
        return $value;
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_cc_soft_descriptor_field($key, $value): string
    {
        if (empty($value)) {
            $requiredErrorMessage = sprintf(
                __('%s is required.', 'woo-pagarme-payments'),
                __('Soft descriptor', 'woo-pagarme-payments')
            );
            WC_Admin_Settings::add_error($requiredErrorMessage);
            throw new InvalidOptionException(InvalidOptionException::code, $requiredErrorMessage);
        }

        $maxLength = $this->model->get_soft_descriptor_max_length($this->isGatewayType());
        if (strlen($value) > $maxLength) {
            $maximumLengthErrorMessage = sprintf(
                __('%s has exceeded the %d character limit.', 'woo-pagarme-payments'),
                __('Soft descriptor', 'woo-pagarme-payments'),
                $maxLength
            );
            WC_Admin_Settings::add_error($maximumLengthErrorMessage);
            throw new InvalidOptionException(InvalidOptionException::code, $maximumLengthErrorMessage);
        }

        return $value;
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_cc_flags_field($key, $value): array
    {
        if (empty($value)) {
            $requiredErrorMessage = sprintf(
                __('%s is required.', 'woo-pagarme-payments'),
                __('Card Brands', 'woo-pagarme-payments')
            );
            WC_Admin_Settings::add_error($requiredErrorMessage);
            throw new InvalidOptionException(InvalidOptionException::code, $requiredErrorMessage);
        }

        return $value;
    }

    public function getBrandsList()
    {
        //Some brands are hidden for PSP
        return array(
            'visa'              => 'Visa',
            'mastercard'        => 'MasterCard',
            'amex'              => 'Amex',
            'hipercard'         => 'HiperCard',
            'diners'            => 'Diners',
            'elo'               => 'Elo',
            'discover'          => 'Discover',
            'aura'              => 'Aura',
            'jcb'               => 'JCB',
            'credz'             => 'Credz',
            'banese'            => 'Banese',
            'cabal'             => 'Cabal',
        );
    }
}
