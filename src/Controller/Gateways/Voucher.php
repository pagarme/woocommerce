<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Controller\Gateways;

use WC_Admin_Settings;
use Woocommerce\Pagarme\Controller\Gateways\Exceptions\InvalidOptionException;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Payment\Voucher\Brands;
use Woocommerce\Pagarme\Model\Payment\Voucher\BrandsInterface;

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Voucher
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class Voucher extends AbstractGateway
{
    /** @var string */
    protected $method = \Woocommerce\Pagarme\Model\Payment\Voucher::PAYMENT_CODE;

    const SOFT_DESCRIPTOR_FIELD_NAME = "Soft descriptor";

    const VOUCHER_CARD_BRANDS_FIELD_NAME = 'Voucher Card Brands';

    const DEFAULT_BRANDS = ['alelo', 'sodexo', 'ticket', 'vr'];

    /**
     * @return void
     */
    public function addRefundSupport()
    {
        $this->supports[] = 'refunds';
    }

    /**
     * @return array
     */
    public function append_form_fields()
    {
        return [
            'voucher_soft_descriptor' => $this->field_voucher_soft_descriptor(),
            'field_voucher_flags' => $this->field_voucher_flags()
        ];
    }

    /**
     * @return array
     */
    protected function gateway_form_fields()
    {
        return [
            'voucher_card_wallet' => $this->field_voucher_card_wallet()
        ];
    }

    /**
     * @return array
     */
    public function field_voucher_soft_descriptor()
    {
        $maxLength = $this->model->getSoftDescriptorMaxLength($this->isGatewayType());
        return [
            'title' => __(self::SOFT_DESCRIPTOR_FIELD_NAME, 'woo-pagarme-payments'),
            'type' => 'text',
            'desc_tip' => __("Name that will appear on the buyer's voucher bill.", 'woo-pagarme-payments'),
            'description' => sprintf(
                __("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.",
                    'woo-pagarme-payments'),
                $maxLength),
            'default' => $this->config->getData('voucher_soft_descriptor') ?? '',
            'custom_attributes' => [
                'data-field' => 'voucher-soft-descriptor',
                'data-field-validate' => 'max-length',
                'data-max-length' => $maxLength,
                'data-error-message-max-length' => sprintf(
                    __('This field has exceeded the %d character limit.', 'woo-pagarme-payments'),
                    $maxLength
                ),
            ]
        ];
    }

    /**
     * @return array
     */
    public function field_voucher_flags()
    {
        $options = [];
        $brands = new Brands;
        foreach ($brands->getBrands() as $class) {
            /** @var BrandsInterface $bank */
            $brand = new $class;
            $options[$brand->getBrandCode()] = $brand->getName();
        }
        return [
            'type' => 'multiselect',
            'title' => __('Voucher Card Brands', 'woo-pagarme-payments'),
            'default'     => $this->config->getData('field_voucher_flags') ?? self::DEFAULT_BRANDS,
            'select_buttons' => false,
            'class' => 'wc-enhanced-select',
            'options' => $options,
            'custom_attributes' => [
                'data-field'   => 'voucher-flags-select',
                'data-element' => 'voucher-flags-select',
                'data-action'  => 'flags',
                'data-field-validate' => 'required',
                'data-error-message-required' => __('This field is required.', 'woo-pagarme-payments'),
            ]
        ];
    }

    /**
     * @return array
     */
    public function field_voucher_card_wallet()
    {
        return [
            'title'    => __('Card Wallet', 'woo-pagarme-payments'),
            'desc_tip' => __('Enable Card Wallet', 'woo-pagarme-payments'),
            'type'     => 'select',
            'label'    => __('Card Wallet', 'woo-pagarme-payments'),
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'default' => $this->config->getData('voucher_card_wallet') ?? Yesno::NO,
            'custom_attributes' => [
                'data-field'   => 'voucher-card-wallet',
            ]
        ];
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_voucher_soft_descriptor_field($key, $value)
    {
        $maxLength = $this->model->getSoftDescriptorMaxLength($this->isGatewayType());
        $this->validateMaxLength($value, self::SOFT_DESCRIPTOR_FIELD_NAME, $maxLength);
        return $value;
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_field_voucher_flags_field($key, $value)
    {
        $this->validateRequired($value, self::VOUCHER_CARD_BRANDS_FIELD_NAME);
        return $value;
    }
}
