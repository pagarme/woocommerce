<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Woocommerce\Pagarme\Model;

if (!defined('ABSPATH')) {
    exit(0);
}

use WC_Order;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config\Source\CheckoutTypes;
use Woocommerce\Pagarme\Model\Payment\Data\AbstractPayment;
use Woocommerce\Pagarme\Model\Payment\Data\Card;
use Woocommerce\Pagarme\Model\Payment\Data\Multicustomers;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequestInterface;

class Checkout
{
    /** @var Config */
    private $config;

    /** @var string */
    const API_REQUEST = 'e3hpgavff3cw';

    /** @var Orders */
    private $orders;

    /** @var Gateway */
    private $gateway;

    /** @var WooOrderRepository */
    private $wooOrderRepository;

    public function __construct(
        Gateway            $gateway = null,
        Config             $config = null,
        Orders             $orders = null,
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
        add_action(
            'woocommerce_after_checkout_validation',
            array($this, 'validateCheckout'),
            10,
            2
        );
    }

    public function validateCheckout($fields, $errors)
    {
        if (
            $fields['billing_number'] === 0 &&
            !key_exists('billing_number_required', $errors->errors)
        ) {
            $errors->add(
                'billing_number_required',
                __("<strong>The billing address &quot;Number&quot; field</strong> is a required field.")
            );
        }
        if (
            $fields['ship_to_different_address'] &&
            $fields['shipping_number'] === 0 &&
            !key_exists('shipping_number_required', $errors->errors)
        ) {
            $errors->add(
                'shipping_number_required',
                __("<strong>The shipping address &quot;Number&quot; field</strong> is a required field.")
            );
        }
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
        if (
            (!Utils::is_request_ajax() && !Utils::isCheckoutRequest())
            || Utils::server('REQUEST_METHOD') !== 'POST'
        ) {
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
            $fields['recurrence_cycle'] = Subscription::getRecurrenceCycle();
            $attempts = intval($wc_order->get_meta('_pagarme_attempts') ?? 0) + 1;
            $wc_order->update_meta_data("_pagarme_attempts", $attempts);
            $response = $this->orders->create_order(
                $wc_order,
                $fields['payment_method'],
                $fields
            );

            $order = new Order($wc_order->get_id());
            $totalWithInstallments = $order->getTotalAmountByCharges();
            $order->update_meta(
                'pagarme_card_tax',
                $order->calculateInstallmentFee($totalWithInstallments, $wc_order->get_total())
            );
            $order->getWcOrder()->set_total($this->getTotalValue($wc_order, $totalWithInstallments));
            $order->update_meta('payment_method', $fields['payment_method']);
            $order->update_meta("attempts", $attempts);
            $this->addAuthenticationOnMetaData($order, $fields);
            WC()->cart->empty_cart();
            if ($response) {
                do_action("on_pagarme_response", $wc_order->get_id(), $response);
                $order->update_meta('transaction_id', $response->getPagarmeId()->getValue());
                $order->update_meta('pagarme_id', $response->getPagarmeId()->getValue());
                $order->update_meta('pagarme_status', $response->getStatus()->getStatus());
                $this->addInstallmentsOnMetaData($order, $fields);
                $order->update_meta('response_data', json_encode($response));
                $order->update_by_pagarme_status($response->getStatus()->getStatus());
                return true;
            }
            $order->update_meta('pagarme_status', 'failed');
            $order->update_by_pagarme_status('failed');
            $order->getWcOrder()->save();
            return false;
        }
    }

    private function convertCheckoutObject(PaymentRequestInterface $paymentRequest)
    {
        $fields = [
            'payment_method' => str_replace('-', '_', $paymentRequest->getPaymentMethod())
        ];
        if ($cards = $paymentRequest->getCards()) {
            foreach ($cards as $key => $card) {
                $key++;
                if ($key === 1) {
                    if ($orderValue = $card->getOrderValue()) {
                        $fields['card_order_value'] = $orderValue;
                    }
                    $fields['brand'] = $card->getBrand();
                    $fields['installments'] = $card->getInstallment() ?? 1;
                    if ($card->getSaveCard()) {
                        $fields['save_credit_card'] = 1;
                    }
                    if ($value = $card->getWalletId()) {
                        $fields['card_id'] = $value;
                    }

                    $authentication = $card->getAuthentication();

                    if (!empty($authentication)) {
                        $fields['authentication'] = json_decode(
                            stripslashes($authentication),
                            true
                        );
                    }
                } else {
                    if ($orderValue = $card->getOrderValue()) {
                        $fields['card_order_value' . $key] = $orderValue;
                    }
                    $fields['brand' . $key] = $card->getBrand();
                    $fields['installments' . $key] = $card->getInstallment() ?? 1;
                    if ($card->getSaveCard()) {
                        $fields['save_credit_card' . $key] = 1;
                    }
                    if ($value = $card->getWalletId()) {
                        $fields['card_id' . $key] = $value;
                    }
                }
                $fields['pagarmetoken' . $key] = $card->getToken();
            }
        }
        $this->extractMulticustomers($fields, $paymentRequest);
        $this->extractOrderValue($fields, $paymentRequest);
        return $fields;
    }

    private function addInstallmentsOnMetaData(&$order, $fields)
    {
        if (!array_key_exists("installments", $fields)) {
            return false;
        }
        $order->update_meta('pagarme_installments_card1', $fields["installments"]);
        if (array_key_exists("installments2", $fields)) {
            $order->update_meta('pagarme_installments_card2', $fields["installments2"]);
        }
    }

    private function addAuthenticationOnMetaData(&$order, $fields)
    {
        if (!array_key_exists('authentication', $fields)) {
            return;
        }
        $order->pagarme_tds_authentication = json_encode($fields["authentication"]);
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

    private function getTotalValue($wc_order, $totalWithInstallments)
    {
        if ($totalWithInstallments > 0) {
            return $totalWithInstallments;
        }
        return $wc_order->get_total();
    }
}
