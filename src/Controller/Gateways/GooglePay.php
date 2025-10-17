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

use Woocommerce\Pagarme\Model\Payment\GooglePay as ModelGooglePay;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}
class GooglePay extends AbstractGateway
{
    /** @var string */
    protected $method = ModelGooglePay::PAYMENT_CODE;
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
        $fields = [
            'account_id' => $this->field_account_id(),
            'googlepay_google_merchant_id' => $this->field_googlepay_google_merchant_id(),
            'googlepay_google_merchant_name' => $this->field_googlepay_google_merchant_name(),
        ];
        return $fields;
    }

    public function field_account_id()
    {
        return [
            'title' => __('Pagar.me account ID', 'woo-pagarme-payments'),
            'default' => $this->config->getData('account_id') ?? '',
            'type' => 'text',
            'description' => __('Check the Pagar.me Dashboard at: Settings &rarr; Keys &rarr; Account ID', 'woo-pagarme-payments'),
        ];
    }

    public function field_googlepay_google_merchant_id()
    {
        return [
            'title' => __('MerchantId Google Pay', 'woo-pagarme-payments'),
            'default' => '',
            'type' => 'text',
            'description' => sprintf(
                __(
                    'Google Merchant Identifier, get yours <a href="%s">here</a>.',
                    'woo-pagarme-payments'
                ),
                "https://pay.google.com/business/console/?hl=pt-br"
            ),
        ];
    }

    public function field_googlepay_google_merchant_name()
    {
        return [
            'title' => __('Store name on Google Pay', 'woo-pagarme-payments'),
            'default' => '',
            'desc_tip' => true,
            'type' => 'text',
            'desc' => __(
                'Your store name that will be displayed to the customer while purchasing through Google Pay.',
                'woo-pagarme-payments'
            ),
        ];
    }

    public function hasCheckoutBlocksSupport(): bool
    {
        return true;
    }
}
