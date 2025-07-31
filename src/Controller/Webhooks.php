<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Webhook\Services\WebhookValidatorService;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Order;
use WP_Error;

class Webhooks
{
    const WEBHOOK_SIGNATURE_HEADER = 'HTTP_X_WEBHOOK_ASYMMETRIC_SIGNATURE';

    private $config;

    public function __construct()
    {
        $this->config = new Config();
        add_action('woocommerce_api_' . Core::getWebhookName(), array($this, 'handle_requests'));
    }

    public function handle_requests()
    {
        $webHookSignature = $_SERVER[self::WEBHOOK_SIGNATURE_HEADER] ?? null;
        // TODO: Uncomment this after tests
//        if (!$webHookSignature) {
//            $this->config->log()->info('Unauthorized Webhook Received: no signature header found!');
//            wp_die('', 'Unauthorized', array('response' => 401));
//        }
        // TODO: Uncomment this after tests
        $payload = file_get_contents('php://input');
//        if (empty($payload)) {
//            $this->config->log()->info('Unauthorized Webhook Received: empty payload!')
//            wp_die('', 'Unauthorized', array('response' => 401));
//        }

        // TODO: Remove this hardcoded signature after tests
        $webHookSignature = "alg=RS256; kid=HxjPxUiSQG8sxf9wGan4GVQXpuuBcIt6WJv1Lznn2iQ; signature=THzazc5dpWd9c2TiFiiqq9F5NzIezuBJjym2dhHK0LV7Q057mnriSIFYLKbHFvyTue7-gxWWLhOCixCQ5xyl0L6KAlH6umiNRt91YQRvGxyyCbf8omNuEVnV_59_oXqaifeirkwip9Tox2j-f9CZIB_A1kgVjx-z0yqXWQsQ2BmeOSCIUoNv-Ld-IIzrKaTAECWV-rAcnQ-Kcm6Sa46RXfVO9ulKR9R0OBo9dJWdv1sqfeKGAQHNgoyEPLqZWpuI7PEpu86FLsZ2qb0wtmj_UgACncVNSF6Nh0PWpvUgTk0ZMvTHQyy999Sa7M_T8GgVluRcbfCXalbbhYCiuXuriQ";
        $payload = '{ "data": { "amount": 35658, "metadata": { "transaction_id": "242D27D1F43F485BA9372BBE92A2F468", "order_code": "1290541", "payment_id": "AA0E74EB441448F6BEF35AA168A8D2EB", "version": "1.42.0", "platform": "Vtex" }, "code": "1290541", "created_at": "2024-11-28T16:37:08.0000000", "gateway_id": "492aa3f2-c7ca-48d5-b82e-01badf9fe185", "last_transaction": { "amount": 35658, "operation_type": "capture", "created_at": "2024-11-28T16:37:11.0000000", "transaction_type": "credit_card", "acquirer_nsu": "134715800", "acquirer_auth_code": "670912", "acquirer_tid": "411958321371328600", "acquirer_message": "GetNet|TRANSACAO EXECUTADA COM SUCESSO", "gateway_id": "c3563573-107d-4f71-90ab-b7c6f5e12398", "gateway_response": { "code": "200" }, "acquirer_name": "getnet", "updated_at": "2024-11-28T16:37:11.0000000", "installments": 6, "success": true, "acquirer_return_code": "00", "id": "tran_Y1wxd8sLMiqkx6OL", "acquirer_affiliation_code": "", "card": { "holder_document": "*****", "exp_month": "*****", "created_at": "2024-09-19T12:18:00.0000000", "billing_address": { "line_1": "sn, Área Rural, Área Rural de Manduri", "number": "*****", "country": "BR", "city": "Manduri", "street": "Área Rural", "neighborhood": "Área Rural de Manduri", "state": "SP", "zip_code": "18787899" }, "exp_year": "*****", "type": "credit", "first_six_digits": "479439", "updated_at": "2024-11-28T16:37:08.0000000", "id": "card_LmKDR3ouwCJAR7wd", "last_four_digits": "9582", "brand": "Visa", "holder_name": "andrea vieira", "status": "active" }, "status": "captured" }, "paid_at": "2024-11-28T16:37:11.0000000", "updated_at": "2024-11-28T16:37:11.0000000", "paid_amount": 35658, "currency": "BRL", "id": "ch_LJVEKDaTgzSkNRO2", "payment_method": "credit_card", "status": "paid", "order": { "amount": 58018, "metadata": { "transaction_id": "242D27D1F43F485BA9372BBE92A2F468", "order_code": "1290541", "payment_id": "AA0E74EB441448F6BEF35AA168A8D2EB", "version": "1.42.0", "platform": "Vtex" }, "code": "1290541", "closed_at": "2024-11-28T16:37:08.0000000", "updated_at": "2024-11-28T16:37:11.0000000", "closed": true, "created_at": "2024-11-28T16:37:08.0000000", "currency": "BRL", "id": "or_kdAKv9DintJ0GOpq", "customer_id": "cus_nyO0zJ2cgsABzGJR", "status": "paid" }, "customer": { "code": "331ae938-7a7d-48e6-85d3-bee9188ea445", "address": { "line_1": "sn, Área Rural, Área Rural de Manduri", "number": "*****", "country": "BR", "updated_at": "2024-11-28T16:37:08.0000000", "city": "Manduri", "street": "Área Rural", "created_at": "2024-11-28T16:37:08.0000000", "id": "addr_9XEPKZ6sziMpremv", "neighborhood": "Área Rural de Manduri", "state": "SP", "zip_code": "18787899", "status": "active" }, "updated_at": "2024-11-28T16:37:08.0000000", "delinquent": false, "document": "*****", "name": "ANDREA VIEIRA", "created_at": "2023-07-12T18:45:22.0000000", "phones": { "mobile_phone": { "country_code": "55", "number": "*****", "area_code": "14" } }, "id": "cus_nyO0zJ2cgsABzGJR", "type": "individual", "email": "andreakinner@hotmail.com" } }, "created_at": "2024-11-28T16:37:11.6500000Z", "id": "hook_9GRgv6LhjuNJgxkv", "type": "charge.paid", "account": { "name": "Dzarm", "id": "acc_k2ngpwlcWLIXAPJ6" } }';

        if (!WebhookValidatorService::validateSignature($payload, $webHookSignature)) {
            $this->config->log()->info('Unauthorized Webhook Received: invalid signature!');
            wp_die('Invalid signature!', 'Unauthorized Webhook Received', array('response' => 401));
        }

        $body = json_decode($payload);

        if (empty($body)) {
            $this->config->log()->info('Webhook Received: empty body!');
            return;
        }
        if (!$this->orderByWoocommerce($body->data->code, $body->data->order->metadata, $body->id) ) {
            return;
        }

        $this->config->log()->info('Webhook Received' . json_encode($body, JSON_PRETTY_PRINT));

        $event = $this->sanitize_event_name($body->type);

        if ($this->was_sent($event, $body->id, $body->data->code)) {
            return;
        }

        if (strpos($event, 'charge') !== false) {
            update_post_meta($body->data->code, "webhook_{$event}_{$body->id}", true);
            do_action("on_pagarme_{$event}", $body);
            do_action("on_pagarme_notes_{$event}", $body);
            return;
        }

        $order_id = Utils::get_order_by_meta_value($body->data->id);

        if (!$order_id) {
            return;
        }

        $order = new Order($order_id);

        update_post_meta($order_id, "webhook_{$event}_{$body->id}", true);
        do_action("on_pagarme_{$event}", $order, $body);
    }

    public function sanitize_event_name($event)
    {
        return str_replace('.', '_', strtolower($event));
    }

    private function orderByWoocommerce($orderId, $metadata, $webhookId)
    {
        if(!wc_get_order($orderId)) {
            if(strpos($this->getMetadata($metadata), "Woocommerce") !== false) {
                $this->config->log()->info('Webhook Received but not proccessed: ' . $webhookId);
            }
            return false;
        }

        return true;
    }
    private function getMetadata($metadata)
    {
        if(empty($metadata)) {
            return "";
        }
        if(property_exists($metadata, 'platformVersion')) {
            return $metadata->platformVersion;
        }
        return "";
    }
    public function was_sent($event_name, $event_id, $order_id)
    {
        $value = get_post_meta($order_id, "webhook_{$event_name}_{$event_id}", true);

        return $value ? true : false;
    }
}
