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
        $maxLength = $this->isGatewayType() ? 22 : 13;
        return [
            'title' => __('Soft descriptor', 'woo-pagarme-payments'),
            'desc_tip' => __('Description that appears on the voucher bill.', 'woo-pagarme-payments'),
            'description' => sprintf(__("Max length of <span id='woo-pagarme-payments_max_length_span'>%s</span> characters.",
                'woo-pagarme-payments'), $maxLength),
            'custom_attributes' => [
                'data-field' => 'voucher-soft-descriptor',
                'data-action' => 'voucher-soft-descriptor',
                'data-element' => 'validate',
                'maxlength' => $maxLength,
                'data-error-msg' => __('This field is required.', 'woo-pagarme-payments')
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
            'select_buttons' => false,
            'class' => 'wc-enhanced-select',
            'options' => $options,
            'custom_attributes' => [
                'data-field'   => 'voucher-flags-select',
                'data-element' => 'voucher-flags-select',
                'data-action'  => 'flags'
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
            'options' => $this->yesnoOptions->toLabelsArray(),
            'default'  => strtolower(Yesno::NO),
            'custom_attributes' => [
                'data-field'   => 'voucher-card-wallet',
            ]
        ];
    }
}
