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

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Pix
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class Pix extends AbstractGateway
{
    /** @var string */
    protected $method = \Woocommerce\Pagarme\Model\Payment\Pix::PAYMENT_CODE;

    /**
     * @return array
     */
    public function append_form_fields()
    {
        return [
            'pix_qrcode_expiration_time' => $this->field_pix_qrcode_expiration_time(),
            'pix_additional_data' => $this->field_pix_additional_data()
        ];
    }

    public function field_pix_qrcode_expiration_time()
    {
        return [
            'title'       => __('QR code expiration time', 'woo-pagarme-payments'),
            'description' => __('Expiration time in seconds of the generated pix QR code.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'placeholder' => 3500,
            'default'     => 3500,
            'custom_attributes' => [
                'data-mask'         => '##0',
                'data-mask-reverse' => 'true',
            ]
        ];
    }

    public function field_pix_additional_data()
    {
        return [
            'title'       => __('Additional information', 'woo-pagarme-payments'),
            'description' => __('Set of key and value used to add information to the generated pix. This will be visible to the buyer during the payment process.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'type'        => 'pix_additional_data',
        ];
    }

    public function validate_pix_additional_data_field($key, $value)
    {
        return $value;
    }
}
