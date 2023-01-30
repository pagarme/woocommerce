<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Woocommerce\Pagarme\Model;

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config\Source\CheckoutTypes;

if (!defined('ABSPATH')) {
    exit(0);
}

use WC_Order;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequestInterface;

class Checkout
{
    /** @var Setting|null */
    private $setting;

    /** @var Config */
    private $config;

    /** @var string */
    const API_REQUEST = 'e3hpgavff3cw';

    public function __construct(
        Config $config = null
    ) {
        if (!$config) {
            $config = new Config;
        }
        $this->config = $config;
        $this->setting = Setting::get_instance();
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function process(WC_Order $wc_order = null, string $type = CheckoutTypes::TRANSPARENT_VALUE)
    {
        if (!Utils::is_request_ajax() || Utils::server('REQUEST_METHOD') !== 'POST') {
            exit(0);
        }
        if (!$wc_order) {
            wp_send_json_error(__('Invalid order', 'woo-pagarme-payments'));
        }
        if (!isset($_POST[PaymentRequestInterface::PAGARME_PAYMENT_REQUEST_KEY])) {
            wp_send_json_error(__('Invalid payment request', 'woo-pagarme-payments'));
        }
        if ($type === CheckoutTypes::TRANSPARENT_VALUE) {
            $fields = $this->convertCheckoutObject($_POST[PaymentRequestInterface::PAGARME_PAYMENT_REQUEST_KEY]);
        }
    }

    private function convertCheckoutObject(PaymentRequestInterface $paymentRequest)
    {
        $fields = [];
        return $fields;
    }
}
