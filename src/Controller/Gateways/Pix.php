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

defined('ABSPATH') || exit;

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

    private static $minimumValueQrCodeExpirationTime = 1;

    const QR_CODE_EXPIRATION_TIME_FIELD_NAME = 'QR code expiration time';

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
            'pix_qrcode_expiration_time' => $this->field_pix_qrcode_expiration_time(),
            'pix_additional_data' => $this->field_pix_additional_data()
        ];
    }

    public function field_pix_qrcode_expiration_time()
    {
        return [
            'title' => __(self::QR_CODE_EXPIRATION_TIME_FIELD_NAME, 'woo-pagarme-payments'),
            'type' => 'text',
            'description' => __('Expiration time in seconds of the generated pix QR code.', 'woo-pagarme-payments'),
            'desc_tip' => true,
            'placeholder' => 3600,
            'default' => $this->config->getData('pix_qrcode_expiration_time') ?? 3600,
            'custom_attributes' => [
                'data-mask' => '##0',
                'data-mask-reverse' => 'true',
                'data-field-validate' => 'required|min',
                'data-min' => self::$minimumValueQrCodeExpirationTime,
                'data-error-message-required' => __('This field is required.', 'woo-pagarme-payments'),
                'data-error-message-min' => sprintf(
                    __('This field does not have the minimum value of %d.', 'woo-pagarme-payments'),
                    self::$minimumValueQrCodeExpirationTime
                ),
            ]
        ];
    }

    public function field_pix_additional_data()
    {
        return [
            'title' => __('Additional information', 'woo-pagarme-payments'),
            'description' => __(
                'Set of key and value used to add information to the generated pix. ' .
                'This will be visible to the buyer during the payment process.',
                'woo-pagarme-payments'
            ),
            'desc_tip' => true,
            'default' => $this->config->getData('pix_additional_data') ?? '',
            'type' => 'pix_additional_data',
        ];
    }

    public function generate_pix_additional_data_html($key, $data)
    {
        $fieldKey = $this->get_field_key($key);

        $value = (array)$this->get_option($key, array());
        ob_start();

        ?>
        <style>
            .woocommerce table.form-table fieldset.pix-additional-data input.small-input-pix {
                width: 198px;
            }
        </style>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr($fieldKey); ?>">
                    <?php echo wp_kses(
                              $this->get_tooltip_html($data),
                              ['span' => array('class' => true, 'data-tip' => true)]
                    ); ?>
                    <?php echo esc_attr($data["title"]); ?>
                </label>
            </th>
            <td class="forminp">
                <fieldset class="pix-additional-data" data-field="additional-data">
                    <input name="<?php echo esc_attr($fieldKey); ?>[name]"
                           id=" <?php echo esc_attr($fieldKey); ?>"
                           class="small-input-pix"
                           type="text"
                           value="<?php echo esc_attr($value["name"] ?? ""); ?>"
                           placeholder="<?php _e('Additional Information Name', 'woo-pagarme-payments'); ?>"
                    />
                    <input name="<?php echo esc_attr($fieldKey); ?>[value]"
                           id=" <?php echo esc_attr($fieldKey); ?>"
                           class="small-input-pix" type="text"
                           value="<?php echo esc_attr($value["value"] ?? ""); ?>"
                           placeholder="<?php _e('Additional Information Value', 'woo-pagarme-payments'); ?>"
                    />
                </fieldset>
            </td>
        </tr>
        <?php

        return ob_get_clean();
    }

    public function validate_pix_additional_data_field($key, $value)
    {
        return $value;
    }

    /**
     * @throws InvalidOptionException
     */
    public function validate_pix_qrcode_expiration_time_field($key, $value)
    {
        $this->validateRequired($value, self::QR_CODE_EXPIRATION_TIME_FIELD_NAME);
        $this->validateMinValue(
            $value,
            self::QR_CODE_EXPIRATION_TIME_FIELD_NAME,
            self::$minimumValueQrCodeExpirationTime
        );
        return $value;
    }
}
