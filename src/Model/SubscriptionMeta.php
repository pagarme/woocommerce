<?php

namespace Woocommerce\Pagarme\Model;

use Woocommerce\Pagarme\Service\CardService;
use Woocommerce\Pagarme\Helper\Utils;
class SubscriptionMeta
{
    protected const PAYMENT_DATA_KEY = "_pagarme_payment_subscription";
    private $logger;


    public function __construct($logger)
    {
        $this->logger = $logger;
    }
    /**
     * @param int $orderId
     * @param \Pagarme\Core\Kernel\Aggregates\Order $response
     * @return void
     */
    public function saveCardInSubscriptionUsingOrderResponse($response)
    {
        $platformOrder = $response->getPlatformOrder()->getPlatformOrder();
        $subscription = $this->getSubscription($platformOrder->get_id());
        if($subscription == null) {
            return;
        }
        $subscriptionCard = $this->getCardToProcessSubscription($subscription);
        $cardData = $this->getCardDataByResponse($response);
        if (
            isset($subscriptionCard['chargeId']) &&
            wcs_order_contains_early_renewal($platformOrder) == true
        ) {
            return;
        }
        $this->saveCardDataToOrderAndSubscriptions($platformOrder->get_id(), $cardData);
    }

    public function saveCardDataToOrderAndSubscriptions($orderId, $cardData)
    {
        if (!$cardData) {
            return;
        }

        $order = wc_get_order($orderId);
        $this->saveCardInSubscriptionOrder($cardData, $order);
        $subscriptions = $this->getAllSubscriptionsForOrder($orderId);
        foreach ($subscriptions as $subscription) {
            $this->saveCardInSubscriptionOrder($cardData, $subscription);
        }
    }

    protected function createCreditCard($pagarmeCustomer)
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
     * Save card information on table post_meta or wc_order_meta
     *
     * @param array $card
     * @param WC_Subscription|WC_Order $order
     *
     * @return void
     */
    protected function saveCardInSubscriptionOrder(array $card, $order)
    {
        if (
            empty($card)
            || (!is_a($order, 'WC_Order') && !is_a($order, 'WC_Subscription'))
        ) {
            return;
        }
        $value = json_encode($card);
        if (FeatureCompatibilization::isHposActivated()) {
            $order->update_meta_data(self::PAYMENT_DATA_KEY, Utils::rm_tags($value));
            $order->save();
            return;
        }
        update_metadata('post', $order->get_id(), self::PAYMENT_DATA_KEY, $value);
        $order->save();
    }

    /**
     * @param \WC_Order | \WC_Subscription $order
     * @return array
     */
    protected function getCardToProcessSubscription($order)
    {
        $cardData = get_metadata(
            'post',
            $order->get_id(),
            self::PAYMENT_DATA_KEY,
            true
        );

        if (empty($cardData) && FeatureCompatibilization::isHposActivated()) {
            $cardData = $order->get_meta(self::PAYMENT_DATA_KEY);
        }

        if (empty($cardData)) {
            $this->logger->info('Card data not found in the current order.');
            return null;
        }

        return json_decode($cardData, true);
    }

    /**
     * @param mixed $response
     * @return mixed
     */
    protected function getCardDataByResponse($response)
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
            'last_four_digits' => $cardData->getLastFourDigits()->getValue(),
            'chargeId' => $charges->getPagarmeId(),
        ];
    }

    /**
     * @param $order
     * @return array|null
     */
    protected function getCardData($order)
    {
        $card = $this->getCardToProcessSubscription($order);

        if (empty($card)) {
            $subscription = $this->getSubscription($order->ID);
            return $this->getCardToProcessSubscription($subscription);
        }

        return $card;
    }
}