<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Woocommerce\Pagarme\Model;

use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config\Source\CheckoutTypes;

if (!defined('ABSPATH')) {
    exit(0);
}

use WC_Order;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequest;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequestInterface;

class Checkout
{
    /** @var Setting|null */
    private $setting;

    /** @var Config */
    private $config;

    /** @var string */
    const API_REQUEST = 'e3hpgavff3cw';

    /** @var Orders*/
    private $orders;

    /** @var Gateway */
    private $gateway;

    /** @var WooOrderRepository*/
    private $wooOrderRepository;

    public function __construct(
        Gateway $gateway = null,
        Config $config = null,
        Orders $orders = null,
        WooOrderRepository $wooOrderRepository = null
    ) {
        if (!$config) {
            $config = new Config;
        }
        if (!$orders) {
            $orders = new Orders;
        }
        if (!$gateway) {
            $gateway = new Gateway;
        }
        if (!$wooOrderRepository) {
            $wooOrderRepository = new WooOrderRepository;
        }
        $this->config = $config;
        $this->setting = Setting::get_instance();
        $this->orders = $orders;
        $this->gateway = $gateway;
        $this->wooOrderRepository = $wooOrderRepository;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param WC_Order|null $wc_order
     * @param string $type
     * @return bool|void
     * @throws \Exception
     */
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
            $response = $this->orders->create_order(
                $wc_order,
                $fields['payment_method'],
                $fields
            );

            $paymentInstance = $this->gateway->getPaymentInstace($fields['payment_method']);

            $order = new Order($wc_order->get_order_number());
            $order->payment_method = $fields['payment_method'];
            $order->update_meta('_payment_method_title', $paymentInstance->getName());
            $order->update_meta('_payment_method', AbstractGateway::PAGARME);
            WC()->cart->empty_cart();
            if ($response) {
                $order->transaction_id     = $response->getPagarmeId()->getValue();
                $order->pagarme_id     = $response->getPagarmeId()->getValue();
                $order->pagarme_status = $response->getStatus()->getStatus();
                $order->response_data    = json_encode($response);
                $order->update_by_pagarme_status($response->getStatus()->getStatus());
                return true;
            }
            $order->pagarme_status = 'failed';
            $order->update_by_pagarme_status('failed');
            return false;
        }
    }

    private function convertCheckoutObject(PaymentRequestInterface $paymentRequest)
    {
        $fields = [
            'payment_method' => $paymentRequest->getPaymentMethod()
        ];
        if ($cards = $paymentRequest->getCards()) {
            foreach ($cards as $card) {
                $fields['brand'] = $card->getBrand();
                $fields['pagarmetoken'] = $card->getToken();
            }
        }
        return $fields;
    }

    private function validate(PaymentRequest $paymentRequest)
    {

    }
}
