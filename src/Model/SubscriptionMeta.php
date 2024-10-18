<?php

namespace Woocommerce\Pagarme\Model;

class SubscriptionMeta
{
    protected const PAYMENT_DATA_KEY = '_pagarme_payment_subscription';

    /**
     * @param int $orderId
     * @param \Pagarme\Core\Kernel\Aggregates\Order $response
     * @return void
     */
    protected function saveCardInSubscriptionUsingOrderResponse($response)
    {
        $platformOrderId = $response->getPlatformOrder()->getId();
        $cardData = $this->getCardDataByResponse($response);
        $this->addMetaDataCard($platformOrderId, $cardData);
    }

    public function addMetaDataCard($orderId, $cardData)
    {
        if (!$cardData) {
            return;
        }

        $order = wc_get_order($orderId);
        $this->saveCardInSubscriptionOrder($cardData, $order);

        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        foreach ($subscriptions as $subscription) {
            $this->saveCardInSubscriptionOrder($cardData, $subscription);
        }
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
     *
     * @param array $card
     * @param WC_Subscription|WC_Order $order
     *
     * @return void
     */
    private function saveCardInSubscriptionOrder(array $card, $order)
    {
        if (
            empty($card)
            || (!is_a($order, 'WC_Order') && !is_a($order, 'WC_Subscription'))
        ) {
            return;
        }

        $key = '_pagarme_payment_subscription';
        $value = json_encode($card);
        if (FeatureCompatibilization::isHposActivated()) {
            $order->update_meta_data($key, Utils::rm_tags($value));
            $order->save();
            return;
        }
        update_metadata('post', $order->get_id(), $key, $value);
        $order->save();
    }
}