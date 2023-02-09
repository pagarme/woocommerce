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

use Woocommerce\Pagarme\Model\Payment\Billet\BankInterface;
use Woocommerce\Pagarme\Model\Payment\Billet\Banks;

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Billet
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class Billet extends AbstractGateway
{
    /** @var string */
    protected $method = \Woocommerce\Pagarme\Model\Payment\Billet::PAYMENT_CODE;

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
            'description' => __('Number of days until the expiration date of the generated boleto.', 'woo-pagarme-payments'),
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
            'title'       => __('Payment instructions', 'woo-pagarme-payments'),
            'type'        => 'text',
            'default' => $this->config->getData('billet_instructions') ?? '',
            'description' => __('Instructions printed on the boleto.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
        ];
    }
}
