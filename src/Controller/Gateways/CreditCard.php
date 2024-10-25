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
use Woocommerce\Pagarme\Model\Subscription;
use Woocommerce\Pagarme\Model\CardInstallments;

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

    const SOFT_DESCRIPTOR_FIELD_NAME = "Soft descriptor";

    const CARD_BRANDS_FIELD_NAME = 'Card Brands';

    const DEFAULT_BRANDS = ['visa', 'mastercard', 'elo', 'hipercard'];

    const LEGACY_CONFIG_NAME = "woocommerce_pagarme-credit-card_settings";

    /** @var array  */
    const LEGACY_SETTINGS_NAME = [
        "cc_installments_maximum" => "max_installment",
        "cc_installments_min_amount" => "smallest_installment",
        "cc_installments_interest" => "interest_rate",
        "cc_installments_interest_increase" => "interest_rate",
        "cc_installments_interest_legacy" => "interest_rate",
        "cc_installments_without_interest" => "free_installments"
    ];

    const LEGACY_SETTINGS_NEEDS_CONVERSION = ["cc_installments_interest"];

    /**
     * @return void
     */
    public function addRefundSupport()
    {
        $this->supports[] = 'refunds';
    }

    /**
     * @return boolean
     */
    public function hasSubscriptionSupport(): bool
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function isSubscriptionActive(): bool
    {
        return wc_string_to_bool($this->config->getData('cc_allowed_in_subscription') ?? true);
    }

    /**
     * @return array
     */
    public function append_form_fields()
    {
        $fields = [
            'cc_operation_type' => $this->field_cc_operation_type(),
            'cc_soft_descriptor' => $this->field_cc_soft_descriptor(),
            'cc_allow_save' => $this->field_cc_allow_save(),
            'cc_flags' => $this->field_cc_flags(),

            'cc_installments_section' => $this->section_installments(),
            'cc_installment_type' => $this->field_cc_installment_type(),
            'cc_installments_maximum' => $this->field_cc_installment_fields('maximum'),
            'cc_installments_min_amount' => $this->field_cc_installment_fields('installment_min_amount'),
            'cc_installments_interest' => $this->field_cc_installment_fields('interest'),
            'cc_installments_interest_increase' => $this->field_cc_installment_fields('interest_increase'),
            'cc_installments_interest_legacy' => $this->field_cc_installment_fields('interest_legacy'),
            'cc_installments_without_interest' => $this->field_cc_installment_fields('without_interest'),
            'cc_installments_by_flag' => $this->field_cc_installment_fields('flags')
        ];

        if (Subscription::hasSubscriptionPlugin()) {
            $fields['cc_subscription_section'] = $this->section('Subscription settings');
            $fields['cc_allowed_in_subscription'] = $this->field_cc_allowed_for_subscription();
            $fields['cc_subscription_installments'] = $this->field_cc_subscription_installments();
        }

        $fields['section_tds'] = $this->section('3DS settings');
        $fields['tds_enabled'] = $this->field_cc_tds_enabled();
        $fields['tds_min_amount'] = $this->field_cc_tds_min_amount();

        return $fields;
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
            'default' => $this->config->getCcOperationType() ?? 2,
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
        $maxLength = $this->model->getSoftDescriptorMaxLength($this->isGatewayType());

        return [
            'title' => __(self::SOFT_DESCRIPTOR_FIELD_NAME, 'woo-pagarme-payments'),
            'type' => 'text',
            'desc_tip' => __("Name that will appear on the buyer's card bill.", 'woo-pagarme-payments'),
            'description' => sprintf(
                __("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.",
                'woo-pagarme-payments'), $maxLength),
            'default' => $this->config->getData('cc_soft_descriptor') ?? '',
            'custom_attributes' => array(
                'data-field' => 'soft-descriptor',
                'data-field-validate' => 'max-length',
                'data-max-length' => $maxLength,
                'data-error-message-max-length' => sprintf(
                    __('This field has exceeded the %d character limit.', 'woo-pagarme-payments'),
                    $maxLength
                ),
            ),
        ];
    }

    /**
     * @return array
     */
    public function field_cc_allow_save()
    {
        return [
            'title' => __('Allow payments with saved cards', 'woo-pagarme-payments'),
            'type'     => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label' => __('Enable card wallet', 'woo-pagarme-payments'),
            'default'     => $this->config->getData('cc_allow_save') ?? strtolower(Yesno::NO),
            'description' => __(
                'Allows users to pay for a new order with cards saved from previous orders. Card details are saved '
                . 'on Pagar.me and not in your store.',
                'woo-pagarme-payments'
            ),
            'desc_tip' => true,
            'custom_attributes' => array(
                'data-field' => 'cc-allow-save',
            ),
        ];
    }

    /**
     * @return array
     */
    private function field_cc_allowed_for_subscription()
    {
        return [
            'title' => __('Active for subscription', 'woo-pagarme-payments'),
            'type'     => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label' => __('Enable credit card for subscription', 'woo-pagarme-payments'),
            'default'     => $this->config->getData('cc_allowed_for_subscription') ?? strtolower(Yesno::YES),
            'description' => __('Activates credit card payment method for subscriptions.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'custom_attributes' => array(
                'data-field' => 'cc-allowed-for-subscription',
            ),
        ];
    }

    /**
     * @return array
     */
    private function field_cc_subscription_installments()
    {
        return [
            'title' => __('Allow installments for subscription', 'woo-pagarme-payments'),
            'type' => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label' => __('Enable installments for subscription', 'woo-pagarme-payments'),
            'default' => $this->config->getData('cc_subscription_installments') ?? strtolower(Yesno::NO),
            'desc_tip' => __('Activates credit card installments for subscriptions.', 'woo-pagarme-payments'),
            'description' => __('Works only for monthly and yearly subscriptions.', 'woo-pagarme-payments'),
            'custom_attributes' => array(
                'data-field' => 'cc-subscription-installments',
            ),
        ];
    }

    /**
     * @return array
     */
    public function field_cc_flags()
    {
        return array(
            'type' => 'multiselect',
            'title' => __(self::CARD_BRANDS_FIELD_NAME, 'woo-pagarme-payments'),
            'select_buttons' => false,
            'class' => 'wc-enhanced-select',
            'options' => $this->getBrandsList(),
            'default'     => $this->config->getData('cc_flags') ?? self::DEFAULT_BRANDS,
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
    public function section_installments()
    {
        return [
            'title' => __('Installment settings', 'woo-pagarme-payments'),
            'type'  => 'title',
            'description' => sprintf(
                __(
                    'Do you have any questions about these settings? For more information and examples, visit our '
                    . '%sdocumentation »%s',
                    'woo-pagarme-payments'
                ),
                '<a
                    href="https://docs.pagar.me/docs/configurando-os-meios-de-pagamento-woocommerce#cart%C3%A3o-de-cr%C3%A9dito"
                    target="_blank" rel="noopener noreferrer">',
                '</a>'
            )
        ];
    }

    /**
     * @return array
     */
    public function field_cc_installment_type()
    {
        return [
            'title' => __('Installment type', 'woo-pagarme-payments'),
            'type' => 'select',
            'class' => 'wc-enhanced-select',
            'label' => __('Choose the installment configuration', 'woo-pagarme-payments'),
            'default' => $this->getDefaultInstallmentType(),
            'options' => array(
                Gateway::CC_TYPE_SINGLE => __('For all card brands', 'woo-pagarme-payments'),
                Gateway::CC_TYPE_BY_FLAG => __('By card brand', 'woo-pagarme-payments'),
                Gateway::CC_TYPE_LEGACY => __('Use default 1.0', 'woo-pagarme-payments'),
            ),
            'custom_attributes' => array(
                'data-element' => 'installments-type-select',
                'data-action' => 'installments-type',
            ),
        ];
    }

    private function getDefaultInstallmentType()
    {
        if ($this->config->getData('cc_installment_type')) {
            return $this->config->getData('cc_installment_type');
        }
        if (get_option($this::LEGACY_CONFIG_NAME) !== false) {
            return CardInstallments::INSTALLMENTS_LEGACY;
        }
        return CardInstallments::INSTALLMENTS_FOR_ALL_FLAGS;
    }

    /**
     * @return array
     */
    public function field_cc_installment_fields($field)
    {
        $installments = [];

        $installments['maximum'] = [
            'title' => __('Max number of installments', 'woo-pagarme-payments'),
            'type' => 'select',
            'default' => $this->getOldConfiguration('cc_installments_maximum') ?? 12,
            'options' => $this->model->getInstallmentOptions($this->isGatewayType()),
            'custom_attributes' => array(
                'data-field' => 'installments-maximum',
            ),
        ];

        $installments['installment_min_amount'] = $this->renderInstallmentTextField(
            'Minimum installment amount',
            'cc_installments_min_amount',
            'Defines the minimum value that an installment can assume',
            'installments-min-amount'
        );

        $installments['interest'] = $this->renderInstallmentTextField(
            'Initial interest rate (%)',
            'cc_installments_interest',
            'Fee that will be charged from the first installment with interest.',
            'installments-interest'
        );

        $installments['interest_increase'] = $this->renderInstallmentTextField(
            'Incremental interest rate (%)',
            'cc_installments_interest_increase',
            'Fee that will be charged increasingly from the second installment with interest added to the initial '
                . 'interest rate.',
            'installments-interest-increase'
        );

        $installments['interest_legacy'] = $this->renderInstallmentTextField(
            'Interest rate (%)',
            'cc_installments_interest_legacy',
            'Fee that will be charged increasingly to all installments.',
            'installments-interest-legacy'
        );

        $installments['without_interest'] = [
            'title' => __('Number of installments without interest', 'woo-pagarme-payments'),
            'type' => 'select',
            'default' => $this->getOldConfiguration('cc_installments_without_interest') ?? 3,
            'options' => $this->model->getInstallmentOptions($this->isGatewayType()),
            'custom_attributes' => array(
                'data-field' => 'installments-without-interest',
            ),
        ];

        $installments['flags'] = [
            'title' => __('Settings by card brand', 'woo-pagarme-payments'),
            'type' => 'installments_by_flag',
            'default' => $this->config->getData('cc_installments_by_flag') ?? '',
        ];

        return $installments[$field];
    }

    /**
     * @param string $title
     * @param string $defaultDataKey
     * @param string $description
     * @param string $dataFieldAttribute
     *
     * @return array
     */
    private function renderInstallmentTextField($title, $defaultDataKey, $description, $dataFieldAttribute)
    {
        return [
            'title' => __($title, 'woo-pagarme-payments'),
            'type' => 'text',
            'default' => $this->getOldConfiguration($defaultDataKey) ?? '',
            'description' => __($description, 'woo-pagarme-payments'),
            'desc_tip' => true,
            'placeholder' => '0.00',
            'custom_attributes' => array(
                'data-field' => $dataFieldAttribute,
                'data-mask' => '##0.00',
                'data-mask-reverse' => 'true',
            ),
        ];
    }

    /**
     * @return null|string
     */
    protected function getOldTitleName() {
        if($this->config->getData("credit_card_title")) {
            return $this->config->getData("credit_card_title");
        }
        $oldData = get_option($this::LEGACY_CONFIG_NAME);
        if (empty($oldData['title'])){
            return null;
        }
        return $oldData['title'];
    }

    /**
     * @return array
     */
    public function section_antifraud()
    {
        return [
            'title' => __('Anti fraud settings', 'woo-pagarme-payments'),
            'type'  => 'title',
            'custom_attributes' => array(
                'data-field' => 'antifraud-section',
            )
        ];
    }

    /**
     * @return array
     */
    public function antifraud_enabled()
    {
        return [
            'title'   => __('Enable', 'woo-pagarme-payments'),
            'type'     => 'select',
            'default' => $this->config->getData('antifraud_enabled') ?? strtolower(Yesno::NO),
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label'   => __('Enable anti fraud', 'woo-pagarme-payments'),
            'custom_attributes' => array(
                'data-field' => 'antifraud-enabled',
            )
        ];
    }

    /**
     * @return array
     */
    public function antifraud_min_value()
    {
        return [
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
        ];
    }

    /**
     * @return array
     */
    public function section($title)
    {
        return [
            'title' => __($title, 'woo-pagarme-payments'),
            'type'  => 'title'
        ];
    }

    /**
     * @return array
     */
    private function field_cc_tds_enabled()
    {
        return [
            'title' => __('Enable', 'woo-pagarme-payments'),
            'type' => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label' => __('Enable 3DS', 'woo-pagarme-payments'),
            'default' => $this->config->getData('cc_tds_enabled') ?? strtolower(Yesno::NO),
            'custom_attributes' => [
                'data-field' => 'cc-tds-enabled',
            ],
        ];
    }

    /**
     * @return array
     */
    private function field_cc_tds_min_amount()
    {
        return [
            'title' => __('Minimum value for 3DS authentication', 'woo-pagarme-payments'),
            'type' => 'text',
            'default' => $this->config->getData('cc_tds_min_amount') ?? '',
            'placeholder' => '0,00',
            'custom_attributes' => array(
                'data-field' => 'cc-tds-min-amount',
                'data-mask' => '#.##0,00',
                'data-mask-reverse' => 'true',
            ),
        ];
    }


    public function generate_installments_by_flag_html($key, $data)
    {
        $fieldKey = $this->get_field_key($key);
        $defaults  = [
            'title'             => '',
            'disabled'          => false,
            'class'             => '',
            'css'               => '',
            'placeholder'       => '',
            'type'              => 'text',
            'desc_tip'          => false,
            'description'       => '',
            'custom_attributes' => array(),
        ];

        $data  = wp_parse_args($data, $defaults);
        $value = (array) $this->get_option($key, array());
        $flags = $this->getBrandsList();
        $maxInstallment = $this->model->getInstallmentsMaximumQuantity($this->isGatewayType());

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
        <tr>
            <th scope="row" class="titledesc">
                <?php echo esc_html($this->get_tooltip_html($data)); ?>
                <label for="<?php echo esc_attr($fieldKey); ?>" id="installments-by-flag-label">
                    <?php echo wp_kses_post($data['title']); ?>
                </label>
            </th>
            <td class="forminp">
                <fieldset data-field="installments-by-flag">
                    <legend></legend>
                    <table aria-describedby="installments-by-flag-label" class="widefat wc_input_table sortable">
                        <thead>
                        <tr>
                            <th class="align"><?php _e('Card Brand', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Max number of installments', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Minimum installment amount', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Initial interest rate (%)', 'woo-pagarme-payments'); ?></th>
                            <th class="align"><?php _e('Incremental interest rate (%)', 'woo-pagarme-payments'); ?></th>
                            <th class="align">
                                <?php _e('Number of installments<br/>without interest', 'woo-pagarme-payments'); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="accounts ui-sortable">
                        <?php
                        foreach ($flags as $flagKey => $flag_name) :
                            $interest = $value['interest'][$flagKey] ?? '';
                            $interestIncrease = $value['interest_increase'][$flagKey] ?? '';
                            $maxInstallmentValue = isset($value['max_installment'][$flagKey])
                                && $value['max_installment'][$flagKey] < $maxInstallment
                                ? $value['max_installment'][$flagKey]
                                : $maxInstallment;
                            $installmentMinAmount = $value['installment_min_amount'][$flagKey] ?? '';
                            $noInterest = $value['no_interest'][$flagKey] ?? 0;

                            $escapedHtmlAttrFieldKey = esc_attr($fieldKey);
                            $escapedHtmlAttrFlagKey = esc_attr($flagKey);
                            $maxInstallmentFieldName =
                                "{$escapedHtmlAttrFieldKey}[max_installment][{$escapedHtmlAttrFlagKey}]";
                            $maxInstallmentFieldId =
                                "{$escapedHtmlAttrFieldKey}_max_installment_{$escapedHtmlAttrFlagKey}";
                            $installmentMinAmountFieldName =
                                "{$escapedHtmlAttrFieldKey}[installment_min_amount][{$escapedHtmlAttrFlagKey}]";
                            $installmentMinAmountFieldId =
                                "{$escapedHtmlAttrFieldKey}_installment_min_amount_{$escapedHtmlAttrFlagKey}";
                            $interestFieldName =
                                "{$escapedHtmlAttrFieldKey}[interest][{$escapedHtmlAttrFlagKey}]";
                            $interestFieldId =
                                "{$escapedHtmlAttrFieldKey}_interest_{$escapedHtmlAttrFlagKey}";
                            $interestIncreaseFieldName =
                                "{$escapedHtmlAttrFieldKey}[interest_increase][{$escapedHtmlAttrFlagKey}]";
                            $interestIncreaseFieldId =
                                "{$escapedHtmlAttrFieldKey}_interest_increase_{$escapedHtmlAttrFlagKey}";
                            $noInterestFieldName =
                                "{$escapedHtmlAttrFieldKey}[no_interest][{$escapedHtmlAttrFlagKey}]";
                            $noInterestFieldId =
                                "{$escapedHtmlAttrFieldKey}_no_interest_{$escapedHtmlAttrFlagKey}";

                            ?>
                            <tr class="account ui-sortable-handle flag" data-flag="<?php echo esc_attr($flagKey); ?>">
                                <td>
                                    <input class="align"
                                           type="text"
                                           value="<?php echo esc_attr($flag_name); ?>"
                                        <?php disabled(1); ?>
                                    />
                                </td>
                                <td>
                                    <input class="align"
                                           type="number"
                                           min="1"
                                           max="<?php echo $maxInstallment; ?>"
                                           name="<?php echo $maxInstallmentFieldName; ?>"
                                           id="<?php echo $maxInstallmentFieldId; ?>"
                                           value="<?php echo intval($maxInstallmentValue); ?>"
                                           data-field="installments-maximum-by-flag"
                                    />
                                </td>
                                <td>
                                    <input class="align"
                                           type="text"
                                           placeholder="0,00"
                                           data-mask="##0,00"
                                           data-mask-reverse="true"
                                           name="<?php echo $installmentMinAmountFieldName; ?>"
                                           id="<?php echo $installmentMinAmountFieldId; ?>"
                                           value="<?php echo
                                               /*phpcs:ignore*/ wc_format_localized_price($installmentMinAmount)
                                           ?>"
                                    />
                                </td>
                                <td>
                                    <input class="align"
                                           type="text"
                                           placeholder="0,00"
                                           data-mask="##0,00"
                                           data-mask-reverse="true"
                                           name="<?php echo $interestFieldName; ?>"
                                           id="<?php echo $interestFieldId; ?>"
                                           value="<?php echo
                                               /*phpcs:ignore*/ wc_format_localized_price($interest)
                                           ?>"
                                    />
                                </td>
                                <td>
                                    <input class="align"
                                           type="text"
                                           placeholder="0,00"
                                           data-mask="##0,00"
                                           data-mask-reverse="true"
                                           name="<?php echo $interestIncreaseFieldName; ?>"
                                           id="<?php echo $interestIncreaseFieldId; ?>"
                                           value="<?php echo
                                               /*phpcs:ignore*/ wc_format_localized_price($interestIncrease)
                                           ?>"
                                    />
                                </td>
                                <td>
                                    <input class="align"
                                           type="number"
                                           min="0"
                                           max="<?php echo $maxInstallment; ?>"
                                           name="<?php echo $noInterestFieldName; ?>"
                                           id="<?php echo $noInterestFieldId; ?>"
                                           value="<?php echo intval($noInterest); ?>"
                                           data-field="installments-without-interest-by-flag"
                                    />
                                </td>
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
            if ($maxInstallment < $value['no_interest'][$brand]) {
                $value['no_interest'][$brand] = $maxInstallment;
            }
        }
        return $value;
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_cc_soft_descriptor_field($key, $value)
    {
        $maxLength = $this->model->getSoftDescriptorMaxLength($this->isGatewayType());
        $isValueLengthGreaterThanMaxLength = strlen($value) > $maxLength;
        if ($isValueLengthGreaterThanMaxLength) {
            $maximumLengthErrorMessage = sprintf(
                __('%s has exceeded the %d character limit.', 'woo-pagarme-payments'),
                __(self::SOFT_DESCRIPTOR_FIELD_NAME, 'woo-pagarme-payments'),
                $maxLength
            );
            WC_Admin_Settings::add_error($maximumLengthErrorMessage);
            throw new InvalidOptionException(InvalidOptionException::CODE, $maximumLengthErrorMessage);
        }

        return $value;
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_cc_flags_field($key, $value)
    {
        $this->validateRequired($value, self::CARD_BRANDS_FIELD_NAME);
        return $value;
    }

    public function getBrandsList()
    {
        //Some brands are hidden for PSP
        $cardList = array(
            'visa'              => 'Visa',
            'mastercard'        => 'MasterCard',
            'amex'              => 'Amex',
            'hipercard'         => 'HiperCard',
            'elo'               => 'Elo',
        );

        if ($this->isGatewayType()) {
            $cardList = array_merge($cardList, array(
                'diners' => 'Diners',
                'discover' => 'Discover',
                'aura' => 'Aura',
                'jcb' => 'JCB',
                'credz' => 'Credz',
                'banese' => 'Banese',
                'cabal' => 'Cabal'
            ));
        }

        return $cardList;
    }
    
    protected function convertCcInstallmentsInterest($value)
    {
        return $value['interest_rate'] * ($value['free_installments'] + 1);
    }
}
