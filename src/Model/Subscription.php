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

use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use WC_Order;
use WC_Subscription;
use WC_Subscriptions_Product;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Service\LogService;
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;
use Woocommerce\Pagarme\Model\SubscriptionMeta;
class Subscription extends SubscriptionMeta
{
    /** @var Config */
    private $config;

    /** @var Orders */
    private $orders;

    /** @var AbstractGateway */
    private $payment;

    private $logger;

    /** @var array */
    const ONE_INSTALLMENT_PERIODS = ['day', 'week'];

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
        $this->logger = new LogService('Renew Subscription');
        parent::__construct($this->logger);
    }

    private function addSupportToSubscription(): void
    {
        if (!$this->payment || !$this->payment->hasSubscriptionSupport() || !$this->hasSubscriptionPlugin()) {
            return;
        }

        array_push($this->payment->supports,
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions'
        );
        add_action(
            'woocommerce_scheduled_subscription_payment_' . $this->payment->id,
            [$this, 'processSubscription'],
            10,
            2
        );
        add_action(
            'on_pagarme_response',
            [$this, 'saveCardInSubscriptionUsingOrderResponse'],
            10,
            1
        );
        add_filter(
            'woocommerce_subscriptions_update_payment_via_pay_shortcode',
            __CLASS__ . '::canUpdatePaymentMethod',
            10,
            3
        );
    }

    private function setPaymentEnabled()
    {
        if (!$this->payment) {
            return;
        }
        if (!$this->payment->isSubscriptionActive() && $this->hasSubscriptionProductInCart()) {
            $this->payment->enabled = "no";
        }
    }

    /**
     * @param float $amountToCharge
     * @param WC_Order $order
     * @return bool|void
     * @throws \Exception
     */
    public function processSubscription($amountToCharge, WC_Order $wc_order)
    {
        try {
            if (!$wc_order) {
                wp_send_json_error(__('Invalid order', 'woo-pagarme-payments'));
            }
            $order = new Order($wc_order->get_id());
            $this->getPagarmeCustomer($wc_order);

            $fields = $this->convertOrderObject($order);
            $response = $this->orders->create_order(
                $wc_order,
                $fields['payment_method'],
                $fields
            );

            $order->update_meta('payment_method', $fields['payment_method']);
            if ($response) {
                $this->addChargeIdInProcessSubscription($response, $wc_order->get_id());
                $order->update_meta('transaction_id', $response->getPagarmeId()->getValue());
                $order->update_meta('pagarme_id', $response->getPagarmeId()->getValue());
                $order->update_meta('pagarme_status', $response->getStatus()->getStatus());
                $order->update_meta('response_data', json_encode($response));
                $order->update_by_pagarme_status($response->getStatus()->getStatus());
                return true;
            }
            $order->update_meta('pagarme_status', 'failed');
            $order->update_by_pagarme_status('failed');
            return false;
        } catch (\Throwable $th) {
            $logger = new LogService('Subscription');
            $logger->log($th);
            if (function_exists('wc_add_notice')) {
                wc_add_notice(
                    __('There was a problem renewing the subscription.'),
                    'error'
                );
            }
            return false;
        }
    }

    public function addChargeIdInProcessSubscription($response, $orderId){
        $subscription = $this->getSubscription($orderId);
        
        if ($subscription->get_payment_method() != 'woo-pagarme-payments-credit_card') {
            return;
        }
        $cardData = $this->getCardToProcessSubscription($subscription);
        
        if (isset($cardData['chargeId']) && !empty($cardData['chargeId'])) {
            return;
        }
        $this->saveCardInSubscriptionUsingOrderResponse($response);
    }

    public function processChangePaymentSubscription($subscription)
    {
        try {
            $subscription = new WC_Subscription($subscription);
            $newPaymentMethod = wc_clean($_POST['payment_method']);
            $this->saveCardInSubscriptionOrder(["payment_method" => $newPaymentMethod], $subscription);
            if ('woo-pagarme-payments-credit_card' == $newPaymentMethod) {
                $pagarmeCustomer = $this->getPagarmeCustomer($subscription);
                $cardResponse = $this->createCreditCard($pagarmeCustomer);
                $this->saveCardInSubscriptionOrder($cardResponse, $subscription);
            }
            \WC_Subscriptions_Change_Payment_Gateway::update_payment_method($subscription, $newPaymentMethod);
            return [
                'result' => 'success',
                'redirect' => $this->payment->get_return_url($subscription)
            ];
        } catch (\Throwable $th) {
            $logger = new LogService('Subscription');
            $logger->log($th);
            if (function_exists('wc_add_notice')) {
                wc_add_notice(
                    __('There was a problem with the payment exchange.'),
                    'error'
                );
            }
            return [
                'result' => 'error',
                'redirect' => $this->payment->get_return_url($subscription)
            ];
        }
    }

    public function processFreeTrialSubscription($wcOrder)
    {
        try {
            $paymentMethod = $this->formatPaymentMethod($_POST['payment_method']);
            if ('credit_card' == $paymentMethod) {
                $pagarmeCustomer = $this->getPagarmeCustomer($wcOrder);
                $cardResponse = $this->createCreditCard($pagarmeCustomer);
                $this->saveCardDataToOrderAndSubscriptions($wcOrder->get_id(), $cardResponse);
            }
            WC()->cart->empty_cart();
            $order = new Order($wcOrder->get_id());
            $order->update_by_pagarme_status(OrderStatus::PROCESSING);
            $redirect = $this->payment->get_return_url($wcOrder);
            return [
                'result' => 'success',
                'redirect' => $redirect
            ];
        } catch (\Throwable $th) {
            $logger = new LogService('Subscription');
            $logger->log($th);
            if (function_exists('wc_add_notice')) {
                wc_add_notice(
                    __('Error creating subscription free trial.'),
                    'error'
                );
            }
            return [
                'result' => 'error',
                'redirect' => $this->payment->get_return_url($wcOrder)
            ];
        }
    }

    /**
     * @return boolean
     */
    public static function hasSubscriptionProductInCart()
    {
        if (!self::hasSubscriptionPlugin()) {
            return false;
        }
        return \WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_renewal();
    }

    /**
     * @return boolean
     */
    public function hasOneInstallmentPeriodInCart()
    {
        if (!$this->hasSubscriptionPlugin()) {
            return false;
        }

        $cartProducts = WC()->cart->cart_contents;
        $productsPeriods = [];
        foreach ($cartProducts ?? [] as $product) {
            $productsPeriods[] = WC_Subscriptions_Product::get_period($product['product_id']);
        }

        $noInstallmentsPeriods = array_intersect(self::ONE_INSTALLMENT_PERIODS, $productsPeriods);
        if (!empty($noInstallmentsPeriods)) {
            return true;
        }

        return false;
    }

    /**
     * @return boolean
     */
    public static function hasSubscriptionFreeTrial()
    {
        if (!self::hasSubscriptionPlugin()) {
            return false;
        }
        return self::hasSubscriptionProductInCart() && \WC_Subscriptions_Cart::all_cart_items_have_free_trial();
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function convertOrderObject(Order $order)
    {
        $fields = [
            'payment_method' => $this->formatPaymentMethod($order->getWcOrder()->get_payment_method())
        ];

        $card = $this->getCardData($order->getWcOrder());

        if (!empty($card)) {
            $fields['card_order_value'] = $order->getWcOrder()->get_total();
            $fields['brand'] = $card['brand'];
            $fields['installments'] = 1;
            $fields['card_id'] = $card['cardId'];
            $fields['pagarmetoken'] = $card['cardId'];
            $fields['recurrence_cycle'] = "subsequent";
            $fields['payment_origin'] = isset($card['chargeId']) ? ["charge_id" => $card['chargeId']] : null;
        }

        return $fields;
    }

    /**
     * @param $paymentMethod
     * @return array
     */
    private function formatPaymentMethod($paymentMethod)
    {
        $paymentMethod = str_replace('woo-pagarme-payments-', '', $paymentMethod);
        return str_replace('-', '_', $paymentMethod);
    }

    /**
     * @param $orderId
     *
     * @return WC_Subscription|null
     */
    protected function getSubscription($orderId)
    {
        $subscription = $this->getAllSubscriptionsForOrder($orderId);
        if(!$subscription) {
            return null;
        }
        return current($subscription);
    }

    /**
     * @param $order int
     * @return array
     */
    protected function getAllSubscriptionsForOrder($orderId)
    {
        $order = wc_get_order($orderId);
        $subscriptions = wcs_get_subscriptions_for_renewal_order($order);
        if (!$subscriptions) {
            $subscriptions = wcs_get_subscriptions_for_order($order);
        }
        return $subscriptions;
    }

    /**
     * @return \Pagarme\Core\Kernel\Aggregates\Charge|boolean;
     */
    protected function getChargesByResponse($response)
    {
        if (!$response) {
            return false;
        }
        return current($response->getCharges());
    }

    /**
     * @param \Pagarme\Core\Kernel\Aggregates\Charge $charge
     * @return \Pagarme\Core\Kernel\Aggregates\Transaction|boolean
     */
    protected function getTransactionsByCharges($charge)
    {
        if (!$charge) {
            return false;
        }
        return current($charge->getTransactions());
    }

    /**
     * @param \Pagarme\Core\Kernel\Aggregates\Transaction $transactions
     * @return \Pagarme\Core\Payment\Aggregates\SavedCard|boolean
     */
    protected function getCardDataByTransaction($transactions)
    {
        if (!$transactions) {
            return false;
        }
        return $transactions->getCardData();
    }

    /**
     * @return string|null
     */
    public static function getRecurrenceCycle()
    {
        if (!self::hasSubscriptionPlugin()) {
            return null;
        }
        if (wcs_cart_contains_renewal()) {
            return "subsequent";
        }
        if (\WC_Subscriptions_Cart::cart_contains_subscription()) {
            return "first";
        }
        return null;
    }

    /**
     * @return boolean
     */
    public static function hasSubscriptionPlugin()
    {
        return class_exists('WC_Subscriptions');
    }

    public static function isChangePaymentSubscription()
    {
        $subsId = $_POST['woocommerce_change_payment'] ?? ($_REQUEST['change_payment_method'] ?? null);
        if ($subsId) {
            return wcs_is_subscription(wc_clean($subsId));
        }
        return false;
    }

    public static function canUpdatePaymentMethod($update, $new_payment_method, $subscription)
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function allowInstallments(): bool {
        return wc_string_to_bool($this->config->getData('cc_subscription_installments'));
    }


    /**
     * @param $fields array
     * @param $order WC_Order
     * @return void
     */
    public function isSameCardInSubscription(&$fields, $order)
    {
        $subscription = $this->getSubscription($order->get_id());
        $dataCard = $this->getCardToProcessSubscription($order);
        if(empty($dataCard)){
            return;
        }
        if($dataCard['cardId'] == $fields['card_id']){
            $fields['payment_origin'] = ["charge_id" => $dataCard['chargeId']];
            return;
        }
        unset($dataCard['chargeId']);
        $this->saveCardInSubscriptionOrder($dataCard, $subscription);
    }

    /**
     * @param \WC_Order $wcOrder
     * @return string|\Exception
     */
    private function getPagarmeCustomer($wcOrder)
    {
        $customer = new Customer($wcOrder->get_customer_id());
        return $customer->getPagarmeCustomerIdByOrder($wcOrder, true);
    }
}
