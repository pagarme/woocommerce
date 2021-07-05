<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Webhook\Factories\WebhookFactory;
use Pagarme\Core\Webhook\Services\ChargeHandlerService;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;
use WC_Order;

class Charge
{
    /**
     * The table name.
     */
    const TABLE = 'woocommerce_pagarme_charges';

    public function insert(array $data)
    {
        global $wpdb;

        return $wpdb->insert(
            $this->get_table_name(),
            array(
                'wc_order_id'   => intval($data['wc_order_id']),
                'order_id'      => esc_sql($data['order_id']),
                'charge_id'     => esc_sql($data['charge_id']),
                'charge_data'   => maybe_serialize($data['charge_data']),
                'charge_status' => esc_sql($data['charge_status']),
            )
        );
    }

    public function update(array $fields, array $where)
    {
        global $wpdb;

        return $wpdb->update(
            $this->get_table_name(),
            $fields,
            $where
        );
    }

    /** phpcs:disable */
    public function is_exists($charge_id)
    {
        global $wpdb;

        $table = $this->get_table_name();
        $query = $wpdb->prepare(
            "SELECT
				`id`
			FROM
				`{$table}`
			WHERE
				`charge_id` = %s
			",
            esc_sql($charge_id)
        );

        return (int) $wpdb->get_var($query);
    }
    /** phpcs:enable */

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
        /*
            TODO: remove this insert and update calls. we need to use charge data
            from core's table, and these methods updates the legacy charge table.
        */
        if (!$this->is_exists($webhook_data->data->id)) {
            return $this->insert([
                'wc_order_id'   => $webhook_data->data->order->code,
                'order_id'      => $webhook_data->data->order->id,
                'charge_id'     => $webhook_data->data->id,
                'charge_data'   => $webhook_data->data,
                'charge_status' => $webhook_data->data->status,
            ]);
        }

        $this->update(
            array(
                'charge_status' => esc_sql($webhook_data->data->status),
                'charge_data'   => maybe_serialize($webhook_data->data),
            ),
            array(
                'charge_id' => esc_sql($webhook_data->data->id),
            )
        );
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

    public function create_from_order($order_id, $charges)
    {
        if (empty($charges)) {
            return;
        }

        foreach ($charges as $charge) {
            if (!$this->is_exists($charge["pagarmeId"])) {
                $this->insert([
                    'wc_order_id'   => $charge["code"],
                    'order_id'      => $order_id,
                    'charge_id'     => $charge["pagarmeId"],
                    'charge_data'   => json_encode($charge),
                    'charge_status' => $charge["status"],
                ]);
            } else {
                $this->update(
                    array(
                        'charge_status' => esc_sql($charge["status"]),
                        'charge_data'   => json_encode($charge),
                    ),
                    array(
                        'charge_id' => esc_sql($charge["pagarmeId"]),
                    )
                );
            }
        }
    }

    /** phpcs:disable */
    public function find_by_wc_order($wc_order_id)
    {
        global $wpdb;

        if (!$wc_order_id) {
            return false;
        }

        $table = $this->get_table_name();
        $query = $wpdb->prepare(
            "SELECT
				id, wc_order_id, order_id, charge_id, charge_data, charge_status, updated_at
			 FROM
				`{$table}`
			WHERE
				`wc_order_id` = %d
			",
            intval($wc_order_id)
        );

        return $wpdb->get_results($query);
    }
    /** phpcs:enable */

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
        $transaction = array_shift($charge->getTransactions());
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
        $transaction = array_shift($charge->getTransactions());
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

    public function get_table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE;
    }
}
