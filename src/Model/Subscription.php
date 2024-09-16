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
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use WC_Order;
use WC_Subscriptions_Product;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Service\LogService;
use Woocommerce\Pagarme\Service\CardService;
use Woocommerce\Pagarme\Service\CustomerService;
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;

class Subscription
{
    /** @var Config */
    private $config;

    /** @var Orders */
    private $orders;

    /** @var AbstractGateway */
    private $payment;

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
            [$this, 'addMetaDataCardByResponse'],
            10,
            2
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
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function addMetaDataCardByResponse($orderId, $response)
    {
        $cardData = $this->getCardDataByResponse($response);
        $this->addMetaDataCard($orderId, $cardData);
    }

    public function addMetaDataCard($orderId, $cardData)
    {
        if (!$cardData) {
            return;
        }
        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        foreach ($subscriptions as $subscription) {
            $this->saveCardInSubscription($cardData, $subscription);
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
            $this->createCustomerPagarmeIdOnPlatformIfNotExists($wc_order->get_customer_id(),
                $order->get_meta('subscription_renewal'));

            $fields = $this->convertOrderObject($order);
            $response = $this->orders->create_order(
                $wc_order,
                $fields['payment_method'],
                $fields
            );

            $order->payment_method = $fields['payment_method'];
            if ($response) {
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

    public function processChangePaymentSubscription($subscription)
    {
        try {
            $subscription = new \WC_Subscription($subscription);
            $newPaymentMethod = wc_clean($_POST['payment_method']);
            if ('woo-pagarme-payments-credit_card' == $newPaymentMethod) {
                $pagarmeCustomer = $this->getPagarmeCustomer($subscription);
                $cardResponse = $this->createCreditCard($pagarmeCustomer);
                $this->saveCardInSubscription($cardResponse, $subscription);
                \WC_Subscriptions_Change_Payment_Gateway::update_payment_method($subscription, $newPaymentMethod);
            }
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
                $this->addMetaDataCard($wcOrder->get_id(), $cardResponse);
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

    private function createCustomerPagarmeIdOnPlatformIfNotExists($customerCode, $subscriptionId)
    {
        $customer = new Customer($customerCode, new SavedCardRepository(), new CustomerRepository());
        if($customer->getPagarmeCustomerId() !== false) {
            return;
        }
        $subscription = new \WC_Subscription($subscriptionId);
        $customerId = $this->getPagarmeIdFromLastValidOrder($subscription);
        $customer->savePagarmeCustomerId($customerCode, $customerId);
    }

    private function getPagarmeIdFromLastValidOrder($subscription)
    {
        foreach ($subscription->get_related_orders() as $orderId) {
            $order = new Order($orderId);
            if(!$order->get_meta('pagarme_response_data')){
                continue;
            }
            $pagarmeResponse = json_decode($order->get_meta('pagarme_response_data'), true);
            if(!array_key_exists('customer', $pagarmeResponse)) {
                continue;
            }
            return $pagarmeResponse['customer']['pagarmeId'];
        }
        throw new \Exception("Unable to find a PagarId in previous request responses");
    }

    private function getPagarmeCustomer($subscription)
    {
        $customer = new Customer($subscription->get_user_id(), new SavedCardRepository(), new CustomerRepository());
        if (!$customer->getPagarmeCustomerId()) {
            $customer = new CustomerService();
            return $customer->createCustomerByOrder($subscription);

        }
        return $customer->getPagarmeCustomerId();
    }

    private function createCreditCard($pagarmeCustomer)
    {
        $data = wc_clean($_POST['pagarme']);
        $card = new CardService();
        if ($data['credit_card']['cards'][1]['wallet-id']) {
            $cardId = $data['credit_card']['cards'][1]['wallet-id'];
            return $card->getCard($cardId, $pagarmeCustomer);
        }
        $cardInfo = $data['credit_card']['cards'][1];
        $response = $card->create($cardInfo['token'], $pagarmeCustomer);
        if (array_key_exists('save-card', $cardInfo) && $cardInfo['save-card'] === "1") {
            $card->saveOnWalletPlatform($response);
        }
        return $response;
    }


    /**
     * Save card information on table post_meta
     * @param array $card
     * @param \WC_Subscription $subscription
     * @return void
     */
    private function saveCardInSubscription(array $card, \WC_Subscription $subscription)
    {
        $key = '_pagarme_payment_subscription';
        $value = json_encode($card);
        if (FeatureCompatibilization::isHposActivated()) {
            $subscription->update_meta_data($key, Utils::rm_tags($value));
            $subscription->save();
            return;
        }
        update_metadata('post', $subscription->get_id(), $key, $value);
        $subscription->save();
    }

    /**
     * @param WC_Order $order
     * @return array
     */
    private function convertOrderObject(Order $order)
    {
        $fields = [
            'payment_method' => $this->formatPaymentMethod($order->wc_order->get_payment_method())
        ];
        $card = $this->getCardSubscriptionData($order);
        if ($card !== null) {
            $fields['card_order_value'] = $order->wc_order->get_total();
            $fields['brand'] = $card['brand'];
            $fields['installments'] = 1;
            $fields['card_id'] = $card['cardId'];
            $fields['pagarmetoken'] = $card['cardId'];
            $fields['recurrence_cycle'] = "subsequent";
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

    private function getCardSubscriptionData($order)
    {
        $cardData = $order->get_meta("pagarme_payment_subscription");
        if (!$cardData) {
            return false;
        }
        return json_decode($cardData, true);
    }


    private function getCardDataByResponse($response)
    {
        $charges = $this->getChargesByResponse($response);
        $transactions = $this->getTransactionsByCharges($charges);
        $cardData = $this->getCardDataByTransaction($transactions);
        if (!$cardData) {
            return $cardData;
        }
        return [
            'cardId' => $cardData->getPagarmeId(),
            'brand' => $cardData->getBrand()->getName(),
            'holder_name' => $cardData->getOwnerName(),
            'first_six_digits' => $cardData->getFirstSixDigits()->getValue(),
            'last_four_digits' => $cardData->getLastFourDigits()->getValue()
        ];
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
        return \WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_renewal();
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
     * @return boolean
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
        if ('woo-pagarme-payments-credit_card' === $new_payment_method) {
            $update = false;
        }
        return $update;
    }

    /**
     * @return boolean
     */
    public function allowInstallments(): bool {
        return wc_string_to_bool($this->config->getData('cc_subscription_installments'));
    }

    /**
     * @return boolean
     */
    public function hasOneInstallmentPeriodInCart(): bool {
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
}
