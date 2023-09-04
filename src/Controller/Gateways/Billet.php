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
use Woocommerce\Pagarme\Model\Payment\Billet\BankInterface;
use Woocommerce\Pagarme\Model\Payment\Billet\Banks;

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

    const PAYMENT_INSTRUCTIONS_MAX_LENGTH = 255;

    /** @var string */
    protected $method = \Woocommerce\Pagarme\Model\Payment\Billet::PAYMENT_CODE;

    /**
     * @return boolean
     */
    public function hasSubscriptionSupport(): bool
    {
        return true;
    }
    /**
     * @return array
     */
    public function append_form_fields()
    {
        return [
            'billet_deadline_days' => $this->field_billet_deadline_days(),
            'billet_instructions' => $this->field_billet_instructions(),
        ];
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
            'title'   => __('Bank', 'woo-pagarme-payments'),
            'class'   => 'wc-enhanced-select',
            'default' => $this->config->getData('billet-bank') ?? 0,
            'options' => $options,
            'custom_attributes' => [
                'data-field' => 'billet-bank',
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
                        'This field must only contain letters, numbers, spaces and punctuations(Except quotes).',
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
}
