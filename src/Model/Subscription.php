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
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;

class Subscription
{
    /** @var Config */
    private $config;

    /** @var string */
    const API_REQUEST = 'e3hpgavff3cw';

    /** @var Orders*/
    private $orders;

    /** @var AbstractGateway */
    private $payment;

    /** @var WooOrderRepository*/
    private $wooOrderRepository;

    public function __construct(
        AbstractGateway $payment = null
    ) {
        if (!$this->hasSubscriptionPlugin()) {
            return;
        }
        $this->payment = $payment;
        $this->config = new Config;
        $this->orders = new Orders;
        $this->wooOrderRepository = new WooOrderRepository;
        $this->addSupportToSubscription();
        $this->setPaymentEnabled();
    }

    private function addSupportToSubscription(): void
    {
        if (!$this->payment->hasSubscriptionSupport() || !$this->hasSubscriptionPlugin()) {
            return ;
        }
        
        $this->payment->supports = array(
            'products',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions',
        );
        add_action(
            'woocommerce_scheduled_subscription_payment_'.$this->payment->id,
            [$this, 'process'],
            10,
            2
        );
    }

    private function setPaymentEnabled()
    {
        if (!$this->payment->hasSubscriptionSupport() && $this->hasSubscriptionProductInCart()) {
            $this->payment->enabled = "no";
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
     * @param float $amountToCharge
     * @param WC_Order $order
     * @return bool|void
     * @throws \Exception
     */
    public function process($amountToCharge, WC_Order $order)
    {
        if (!$order) {
            wp_send_json_error(__('Invalid order', 'woo-pagarme-payments'));
        }
        $fields = $this->convertOrderObject($order);
        $response = $this->orders->create_order(
            $order,
            $fields['payment_method'],
            $fields
        );

        $order = new Order($order->get_id());
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

    public function hasSubscriptionPlugin(): bool
    {
		return class_exists('WC_Subscriptions');
	}
}
