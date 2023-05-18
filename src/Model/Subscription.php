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
use Woocommerce\Pagarme\Model\Payment\Data\AbstractPayment;
use Woocommerce\Pagarme\Model\Payment\Data\Card;
use Woocommerce\Pagarme\Model\Payment\Data\Cards;
use Woocommerce\Pagarme\Model\Payment\Data\Multicustomers;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequest;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequestInterface;

class Subscription
{
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
        if (!$wc_order) {
            wp_send_json_error(__('Invalid order', 'woo-pagarme-payments'));
        }
        if ($type === CheckoutTypes::TRANSPARENT_VALUE) {
            $fields = $this->convertOrderObject($wc_order);
            $response = $this->orders->create_order(
                $wc_order,
                $fields['payment_method'],
                $fields
            );

            $order = new Order($wc_order->get_id());
            $order->payment_method = $fields['payment_method'];
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

    private function convertOrderObject(WC_Order $order)
    {

        $paymentMethod = str_replace('woo-pagarme-payments-', '', $order->get_payment_method());
        $paymentMethod = str_replace('-', '_', $paymentMethod);
        $fields = [
            'payment_method' => $paymentMethod
        ];

//        if ($cards = $paymentRequest->getCards()) {
//            foreach ($cards as $key => $card) {
//                $key++;
//                if ($key === 1) {
//                    if ($orderValue = $card->getOrderValue()) {
//                        $fields['card_order_value'] = $orderValue;
//                    }
//                    $fields['brand'] = $card->getBrand();
//                    $fields['installments'] = $card->getInstallment() ?? 1;
//                    if ($card->getSaveCard()) {
//                        $fields['save_credit_card'] = 1;
//                    }
//                    if ($value = $card->getWalletId()) {
//                        $fields['card_id'] = $value;
//                    }
//                } else {
//                    if ($orderValue = $card->getOrderValue()) {
//                        $fields['card_order_value' . $key] = $orderValue;
//                    }
//                    $fields['brand' . $key] = $card->getBrand();
//                    $fields['installments' . $key] = $card->getInstallment() ?? 1;
//                    if ($card->getSaveCard()) {
//                        $fields['save_credit_card' . $key] = 1;
//                    }
//                    if ($value = $card->getWalletId()) {
//                        $fields['card_id' . $key] = $value;
//                    }
//                }
//                $fields['pagarmetoken' . $key] = $card->getToken();
//            }
//        }
//        $this->extractMulticustomers($fields, $paymentRequest);
//        $this->extractOrderValue($fields, $paymentRequest);
        return $fields;
    }

    private function extractMulticustomers(array &$fields, PaymentRequestInterface $paymentRequest)
    {
        foreach ($paymentRequest->getData() as $method => $data) {
            if ($data instanceof AbstractPayment) {
                if ($data->getMulticustomers() instanceof Multicustomers) {
                    foreach ($data->getMulticustomers()->getData() as $key => $value) {
                        $fields['multicustomer_' . $method][$key] = $value;
                        $fields['multicustomer_' . $method . '[' . $key . ']'] = $value;
                        $fields['enable_multicustomers_' . $method] = 1;
                    }
                }
            }
            if (is_array($data)) {
                foreach ($data as $sequece => $datum) {
                    if ($datum instanceof Card) {
                        $method = 'card';
                        $sequece++;
                        if (count($data) > 1) {
                            $method .= $sequece;
                        }
                        if ($datum->getMulticustomers() instanceof Multicustomers) {
                            foreach ($datum->getMulticustomers()->getData() as $key => $value) {
                                $fields['multicustomer_' . $method][$key] = $value;
                                $fields['multicustomer_' . $method . '[' . $key . ']'] = $value;
                                $fields['enable_multicustomers_' . $method] = $value;
                            }
                        }
                    }
                }
            }
        }
    }

    private function extractOrderValue(array &$fields, PaymentRequestInterface $paymentRequest)
    {
        foreach ($paymentRequest->getData() as $method => $data) {
            if ($data instanceof AbstractPayment) {
                if ($orderValue = $data->getOrderValue()) {
                    $fields[$method . '_value'] = $orderValue;
                }
            }
        }
    }
}
