<?php

/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Woocommerce\Pagarme\Model;


if (!defined('ABSPATH')) {
    exit(0);
}

use WC_Order;
use WC_Subscription;
use WC_Subscriptions_Cart;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;

class Subscription
{
    /** @var Config */
    private $config;

    /** @var string */
    const API_REQUEST = 'e3hpgavff3cw';

    /** @var Orders */
    private $orders;

    /** @var AbstractGateway */
    private $payment;

    public function __construct(
        AbstractGateway $payment = null
    )
    {
        if (!$this->hasSubscriptionPlugin()) {
            return;
        }
        $this->payment = $payment;
        $this->config = new Config;
        $this->orders = new Orders;
        $this->addSupportToSubscription();
        $this->setPaymentEnabled();
    }

    private function addSupportToSubscription(): void
    {
        if (!$this->payment->hasSubscriptionSupport() || !$this->hasSubscriptionPlugin()) {
            return;
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
            'woocommerce_scheduled_subscription_payment_' . $this->payment->id,
            [$this, 'processSubscription'],
            10,
            2
        );
        add_action(
            'on_pagarme_response',
            [$this, 'addMetaDataCard'],
            10,
            2
        );
        add_filter('woocommerce_subscriptions_update_payment_via_pay_shortcode',
            __CLASS__ . '::maybe_dont_update_payment_method', 10, 3);
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

    public function addMetaDataCard($orderId, $response)
    {
        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        $cardData = $this->getCardDataByResponse($response);
        if (!$cardData) {
            return;
        }
        $paymentInformation = json_encode(
            [
                "brand" => strtolower($cardData->getBrand()->getName()),
                "cardId" => $cardData->getPagarmeId()
            ]
        );
        foreach ($subscriptions as $subs_id => $subscription) {
            $subscription->add_meta_data('_pagarme_payment_subscription', $paymentInformation, true);
            $subscription->save();
        }
    }

    /**
     * @param float $amountToCharge
     * @param WC_Order $order
     * @return bool|void
     * @throws \Exception
     */
    public function processSubscription($amountToCharge, WC_Order $order)
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
            $order->transaction_id = $response->getPagarmeId()->getValue();
            $order->pagarme_id = $response->getPagarmeId()->getValue();
            $order->pagarme_status = $response->getStatus()->getStatus();
            $order->response_data = json_encode($response);
            $order->update_by_pagarme_status($response->getStatus()->getStatus());
            return true;
        }
        $order->pagarme_status = 'failed';
        $order->update_by_pagarme_status('failed');
        return false;
    }

    public function processChangePaymentSubscription(WC_Order $subscription)
    {
        $new_payment_method = wc_clean( $_POST['payment_method'] );;

        if ( 'woo-pagarme-payments-credit_card' == $new_payment_method ) {
            self::update_payment_method($subscription, $new_payment_method);
//            throw new \Exception("Nao Rolou Mesmo!!!!");
        }
        return [
            'result'   => 'success',
            'redirect' => $this->payment->get_return_url($order)
        ];
    }

    private function convertOrderObject(WC_Order $order)
    {

        $paymentMethod = str_replace('woo-pagarme-payments-', '', $order->get_payment_method());
        $paymentMethod = str_replace('-', '_', $paymentMethod);
        $fields = [
            'payment_method' => $paymentMethod
        ];
        $card = $this->getCardSubscriptionData($order);
        if ($card !== null) {
            $fields['card_order_value'] = $order->get_total();
            $fields['brand'] = $card['brand'];
            $fields['installments'] = 1;
            $fields['card_id'] = $card['cardId'];
            $fields['pagarmetoken'] = $card['cardId'];
            $fields['recurrence_cycle'] = "subsequent";
        }
        return $fields;
    }

    private function getCardSubscriptionData($order)
    {
        $cardData = $order->get_meta("_pagarme_payment_subscription", true);
        if (!$cardData) {
            return false;
        }
        return json_decode($cardData, true);
    }


    private function getCardDataByResponse($response)
    {
        $charges = $this->getChargesByResponse($response);
        $transactions = $this->getTransactionsByCharges($charges);
        return $this->getCardDataByTransaction($transactions);
    }

    private function getChargesByResponse($response)
    {
        if (!$response) {
            return false;
        }
        return current($response->getCharges());
    }

    private function getTransactionsByCharges($charge)
    {
        if (!$charge) {
            return false;
        }
        return current($charge->getTransactions());
    }

    private function getCardDataByTransaction($transactions)
    {
        if (!$transactions) {
            return false;
        }
        return $transactions->getCardData();
    }

    /**
     * @return boolean
     */
    public static function hasSubscriptionProductInCart()
    {
        if (!self::hasSubscriptionPlugin()) {
            return false;
        }
        if (WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_renewal()) {
            return true;
        }
        return false;
    }

    /**
     * @return boolean
     */
    public static function hasSubscriptionPlugin()
    {
        return class_exists('WC_Subscriptions');
    }

    public function isChangePaymentSubscription() {
        if (isset($_POST['woocommerce_change_payment'])) {
            return wcs_is_subscription(wc_clean($_POST['woocommerce_change_payment']));
        }
        return false;
    }

    public static function maybe_dont_update_payment_method( $update, $new_payment_method, $subscription ) {
        if ('woo-pagarme-payments-credit_card' == $new_payment_method ) {
            $update = false;
        }
        return $update;
    }
}
