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
use WC_Subscriptions_Cart;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Model\Config\Source\CheckoutTypes;


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
        if (!$this->hasSubscriptionPlugin()) {
            return;
        }
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
        $card = $this->getCardDataByParent($order);
        if ($card !== null) {
            $fields['card_order_value'] = $order->get_total();
            $fields['brand'] = $card->brand;
            $fields['installments'] = 1;
            $fields['card_id'] = $card->pagarmeId;
            $fields['pagarmetoken'] = $card->pagarmeId;
        }
        return $fields;
    }

    private function getCardDataByParent(WC_Order $order)
    {
        $subscription = $this->getSubscription($order);
        $parentOrder = $this->getParentOrderBySub($subscription);
        return $this->getTransactionData($parentOrder)->cardData;
    }

    private function getTransactionData(WC_Order $order)
    {
        $responseData = json_decode($order->get_meta('_pagarme_response_data'));
        return $responseData->charges[0]->transactions[0];
    }

    private function getSubscription(WC_Order $order)
    {
        return $this->wooOrderRepository->getById($order->get_meta("_subscription_renewal"));
    }

    private function getParentOrderBySub($subscription)
    {
        return $this->wooOrderRepository->getById($subscription->get_parent_id());
    }

    public static function hasSubscriptionProductInCart(): bool
    {
        if (WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_renewal()) {
            return true;
        }
        return false;
    }

    public static function hasSubscriptionPlugin(): bool
    {
		return class_exists('WC_Subscriptions');
	}
}
