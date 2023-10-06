<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Webhook\Factories\WebhookFactory;
use Pagarme\Core\Webhook\Services\ChargeHandlerService;
use WC_Order;

class Charge
{
    /**
     * @param object $webHookData
     */
    public function add_notes($webHookData)
    {
        if (!$webHookData) {
            return;
        }

        $messageList = [
            "charge.antifraud_reproved" => "Anti-Fraud reproved.",
            "charge.antifraud_approved" => "Anti-Fraud approved.",
            "charge.antifraud_manual" => "Anti-fraud process in manual analysis.",
            "charge.antifraud_pending" => "Anti-fraud process pending."
        ];

        $message = __(
            $messageList[$webHookData->type],
            'woo-pagarme-payments'
        );

        $messageWebHook = __(
            "Webhook received: ",
            'woo-pagarme-payments'
        );

        $wc_order = new WC_Order($webHookData->data->order->code);
        $wc_order->add_order_note($messageWebHook . $message);
    }

    public function create_from_webhook($webhook_data)
    {
        if (!$webhook_data) {
            return;
        }
        $this->update_core_charge($webhook_data);
    }

    private function update_core_charge($webhook_data)
    {
        $webhook = clone $webhook_data;

        $webhook->data = json_decode(
            json_encode($webhook->data),
            true
        );

        $coreWebhookFactory = new WebhookFactory();
        $coreChargeHandler = new ChargeHandlerService();
        $coreWebhook = $coreWebhookFactory->createFromPostData($webhook);
        $coreChargeHandler->handle($coreWebhook);
    }

    public function get_i18n_status($status)
    {
        if (get_locale() != 'pt_BR') {
            return ucfirst($status);
        }

        $list = array(
            'pending'    => 'pendente',
            'paid'       => 'pago',
            'canceled'   => 'cancelado',
            'processing' => 'processando',
            'failed'     => 'falhou',
        );

        $status = strtolower($status);

        return ucfirst(isset($list[$status]) ? $list[$status] : $status);
    }

    public function is_allowed_capture($charge)
    {
        $transaction = current($charge->getTransactions());
        $method = $transaction->getTransactionType()->getType();
        $chargeStatus = $charge->getStatus()->getStatus();

        if ($method == 'boleto') {
            return false;
        }

        if ($chargeStatus == 'pending') {
            return true;
        }

        return false;
    }

    public function is_allowed_cancel($charge)
    {
        $status = $charge->getStatus()->getStatus();
        $transactions = $charge->getTransactions();
        $transaction = array_shift($transactions);
        $method = $transaction->getTransactionType()->getType();

        if ($method == 'boleto' && in_array($status, ['pending'])) {
            return true;
        }

        if ($method == 'credit_card' && in_array($status, ['pending', 'paid'])) {
            return true;
        }

        if ($method == 'pix' && in_array($status, ['pending', 'paid'])) {
            return true;
        }

        return false;
    }
}
