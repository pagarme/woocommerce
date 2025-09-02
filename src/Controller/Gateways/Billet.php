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

use Woocommerce\Pagarme\Controller\Gateways\Exceptions\InvalidOptionException;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Payment\Billet as BilletModel;
use Woocommerce\Pagarme\Model\Payment\Billet\BankInterface;
use Woocommerce\Pagarme\Model\Payment\Billet\Banks;
use Woocommerce\Pagarme\Model\Subscription;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Billet
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class Billet extends AbstractGateway
{
    const PAYMENT_INSTRUCTIONS_FIELD_NAME = 'Payment instructions';

    const BILLET_BANK_FIELD_NAME = 'Bank';

    const PAYMENT_INSTRUCTIONS_MAX_LENGTH = 255;

    const LEGACY_CONFIG_NAME = "woocommerce_pagarme-banking-ticket_settings";

    const LEGACY_SETTINGS_NAME = [
        "billet_checkout_instructions" => "description",
    ];

    /** @var string */
    protected $method = BilletModel::PAYMENT_CODE;

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
        return wc_string_to_bool($this->config->getData('billet_allowed_in_subscription') ?? true);
    }

    /**
     * @return null|string
     */
    protected function getOldTitleName() 
    {
        if(!empty($this->config->getData("billet_title"))) {
            return $this->config->getData("billet_title");
        }
        $oldData = get_option(self::LEGACY_CONFIG_NAME);
        if (empty($oldData['title'])){
            return null;
        }
        return $oldData['title'];
    }

    /**
     * @return array
     */
    public function append_form_fields()
    {
        $fields = [
            BilletModel::getCheckoutInstructionsKey() => $this->field_billet_checkout_instructions(),
            'billet_deadline_days' => $this->field_billet_deadline_days(),
            'billet_instructions' => $this->field_billet_instructions(),
        ];
        if (Subscription::hasSubscriptionPlugin()) {
            $fields['billet_allowed_in_subscription'] = $this->field_billet_allowed_for_subscription();
        }
        return $fields;
    }

    /**
     * @return array
     */
    protected function gateway_form_fields()
    {
        return [
            'billet_bank' => $this->field_billet_bank()
        ];
    }

    /**
     * @return array
     */
    public function field_billet_bank()
    {
        $options = [];
        $banks = new Banks;
        foreach ($banks->getBanks() as $class) {
            /** @var BankInterface $bank */
            $bank = new $class;
            $options[$bank->getBankId()] = $bank->getName();
        }
        return [
            'type'    => 'select',
            'title'   => __(self::BILLET_BANK_FIELD_NAME, 'woo-pagarme-payments'),
            'class'   => 'wc-enhanced-select',
            'default' => $this->config->getData('billet-bank') ?? 0,
            'options' => $options,
            'custom_attributes' => [
                'data-field' => 'billet-bank',
                'data-field-validate' => 'required',
                'data-error-message-required' => __('This field is required.', 'woo-pagarme-payments'),
            ]
        ];
    }

    /**
     * @return array
     */
    public function field_billet_deadline_days()
    {
        return [
            'title'       => __('Default expiration days', 'woo-pagarme-payments'),
            'type'        => 'text',
            'description' => __(
                'Number of days until the expiration date of the generated billet.',
                'woo-pagarme-payments'
            ),
            'desc_tip'    => true,
            'placeholder' => 5,
            'default'     => $this->config->getData('billet_deadline_days') ?? 5,
            'custom_attributes' => [
                'data-mask'         => '##0',
                'data-mask-reverse' => 'true',
            ],
        ];
    }

    /**
     * @return array
     */
    public function field_billet_instructions()
    {
        return [
            'title' => __(self::PAYMENT_INSTRUCTIONS_FIELD_NAME, 'woo-pagarme-payments'),
            'type' => 'textarea',
            'class' => 'pagarme-option-text-area',
            'default' => $this->config->getData('billet_instructions') ?? '',
            'description' => __('Instructions printed on the billet.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'custom_attributes' => [
                'data-field-validate' => 'alphanumeric-spaces-punctuation|max-length',
                'data-error-message-alphanumeric-spaces-punctuation' =>
                    __(
                        'This field must only contain letters, numbers, spaces and punctuations (except quotation marks).',
                        'woo-pagarme-payments'
                    ),
                'data-max-length' => self::PAYMENT_INSTRUCTIONS_MAX_LENGTH,
                'data-error-message-max-length' => sprintf(
                    __('This field has exceeded the %d character limit.', 'woo-pagarme-payments'),
                    self::PAYMENT_INSTRUCTIONS_MAX_LENGTH
                ),
            ]
        ];
    }

    /**
     * @return array
     */
    private function field_billet_allowed_for_subscription()
    {
        if (!Subscription::hasSubscriptionPlugin()){
            return [];
        }
        return [
            'title' => __('Active for subscription', 'woo-pagarme-payments'),
            'type'     => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label' => __('Enable billet for subscription', 'woo-pagarme-payments'),
            'default'     => $this->config->getData('billet_allowed_for_subscription') ?? strtolower(Yesno::YES),
            'description' => __('Activates billet payment method for subscriptions.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'custom_attributes' => array(
                'data-field' => 'billet-allowed-for-subscription',
            ),
        ];
    }

    /**
     * @return array
     */
    private function field_billet_checkout_instructions()
    {
        return [
            'title' => BilletModel::getCheckoutInstructionsTitle(),
            'type' => 'textarea',
            'class' => 'pagarme-option-text-area',
            'description' => BilletModel::getCheckoutInstructionsDescription(),
            'desc_tip' => true,
            'default' => $this->getOldConfiguration(BilletModel::getCheckoutInstructionsKey()) ??
                BilletModel::getDefaultCheckoutInstructions(),
        ];
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_billet_instructions_field($key, $value)
    {
        $this->validateAlphanumericAndSpacesAndPunctuation($value, self::PAYMENT_INSTRUCTIONS_FIELD_NAME);
        $this->validateMaxLength(
            $value,
            self::PAYMENT_INSTRUCTIONS_FIELD_NAME,
            self::PAYMENT_INSTRUCTIONS_MAX_LENGTH
        );
        return $value;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     * @throws InvalidOptionException
     */
    public function validate_billet_bank_field($key, $value)
    {
        $this->validateRequired($value, self::BILLET_BANK_FIELD_NAME);

        return $value;
    }

    public function hasCheckoutBlocksSupport(): bool
    {
        return true;
    }
}
