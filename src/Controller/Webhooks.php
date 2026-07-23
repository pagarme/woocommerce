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
        if (!$webHookSignature) {
            $this->config->log()->info('Unauthorized Webhook Received: no signature header found!');
            wp_die(
                'Unauthorized Webhook Received: no signature header found!',
                'Unauthorized',
                array('response' => 401)
            );
        }
        $payload = file_get_contents('php://input');
        if (empty($payload)) {
            $this->config->log()->info('Unauthorized Webhook Received: empty payload!');
            wp_die(
                "Unauthorized Webhook Received: empty payload!",
                'Unauthorized',
                array('response' => 401)
            );
        }

        if (!WebhookValidatorService::validateSignature($payload, $webHookSignature)) {
            $this->config->log()->info('Unauthorized Webhook Received: invalid signature!');
            wp_die(
                'Unauthorized Webhook Received: invalid signature!',
                'Unauthorized Webhook Received',
                array('response' => 401)
            );
        }

        $body = json_decode($payload);

        if (empty($body)) {
            $this->config->log()->info('Webhook Received: empty body!');
            return;
        }
        if (!$this->orderByWoocommerce($body->data->code, $body->data->order->metadata, $body->id) ) {
            return;
        }

        $this->config->log()->info('Webhook Received: ' . json_encode($body, JSON_PRETTY_PRINT));

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
