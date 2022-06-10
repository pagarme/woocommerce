<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model;
use Woocommerce\Pagarme\Controller\Orders;

use WC_Order;

class Checkout
{
    protected $cards = array();

    protected $payment_methods = [];

    public function __construct()
    {
        $this->ordersController = new Orders();

        add_action('woocommerce_api_' . Model\Checkout::API_REQUEST, array($this, 'process_checkout_transparent'));
        add_action('woocommerce_view_order', array('Woocommerce\Pagarme\View\Checkouts', 'render_payment_details'));
        add_action('wp_ajax_xqRhBHJ5sW', array($this, 'build_installments'));
        add_action('wp_ajax_nopriv_xqRhBHJ5sW', array($this, 'build_installments'));
        add_filter('wcbcf_billing_fields', array($this, 'set_required_fields'));

        $this->payment_methods = [
            'credit_card'     => __('Credit card', 'woo-pagarme-payments'),
            'billet'          => __('Boleto', 'woo-pagarme-payments'),
            'pix'             => __('Pix', 'woo-pagarme-payments'),
            '2_cards'         => __('2 credit cards', 'woo-pagarme-payments'),
            'billet_and_card' => __('Credit card and Boleto', 'woo-pagarme-payments'),
            'voucher'         => __('Voucher', 'woo-pagarme-payments'),
        ];
    }

    public function process_checkout_transparent(WC_Order $wc_order = null): bool
    {
        if (!Utils::is_request_ajax() || Utils::server('REQUEST_METHOD') !== 'POST') {
            exit(0);
        }

        if (!$wc_order) {
            wp_send_json_error(__('Invalid order', 'woo-pagarme-payments'));
        }

        $fields = $this->prepare_fields();

        if (empty($fields)) {
            wp_send_json_error(__('Empty fields', 'woo-pagarme-payments'));
        }

        $this->validate_amount_billet_and_card($fields, $wc_order);
        $this->validate_amount_2_cards($fields, $wc_order);
        $this->validate_brands($fields);

        $response = $this->ordersController->create_order(
            $wc_order,
            $fields['payment_method'],
            $fields
        );

        $order  = new Order($wc_order->get_order_number());
        $order->payment_method   = $fields['payment_method'];
        $order->update_meta('_payment_method_title', $this->payment_methods[$fields['payment_method']]);
        $order->update_meta('_payment_method', Gateways::PAYMENT_METHOD);
        WC()->cart->empty_cart();
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

    public function build_installments()
    {
        if (!Utils::is_request_ajax() || Utils::server('REQUEST_METHOD') !== 'GET') {
            exit(0);
        }

        $flag  = Utils::get('flag', false, 'esc_html');
        $total = Utils::get('total', false);

        $gateway = new Gateway();
        // TODO: get installments from core's installment service;
        $html    = $gateway->get_installments_by_type($total, $flag);

        echo wp_kses_no_null($html);

        exit();
    }

    public function set_required_fields($fields)
    {
        $fields['billing_neighborhood']['required'] = true;

        return $fields;
    }

    public function parse_cards($data, $key = 'card')
    {
        if (isset($data[$key])) {
            $this->cards[$data[$key]['id']] = $data[$key];
        }

        foreach ($data as &$value) :
            if (is_array($value)) {
                $this->parse_cards($value, $key);
            }
        endforeach;
    }

    private function save_customer_card($raw_body, $index)
    {
        $customer = new Customer(get_current_user_id());
        $body     = json_decode($raw_body, true);
        $cards    = $customer->cards;
        $count    = 1;

        $this->parse_cards($body);

        foreach ($this->cards as $card_id => $card) {

            if ($count === $index) {
                if (!array_key_exists($card_id, $cards)) {
                    $cards[$card_id] = $card;
                }
            }

            $count++;
        }

        $customer->cards = $cards;

        if (isset($body['customer']['id'])) {
            $customer->customer_id = $body['customer']['id'];
        }
    }

    private function prepare_fields()
    {
        if (empty($_POST['fields'])) {
            return false;
        }

        $fields = array();

        foreach ($_POST['fields'] as $data) {
            if (!isset($data['name']) || !isset($data['value'])) {
                continue;
            }

            if (empty($data['value'])) {
                continue;
            }

            $name = sanitize_text_field($data['name']);
            $value = sanitize_text_field($data['value']);

            $fields[$name] = Utils::rm_tags($value, true);

            if ($name == 'card_number' || $name == 'card_number2') {
                $fields[$name] = Utils::format_document($value);
            }

            if ($name == 'card_expiry') {
                $this->prepare_expiry_field($data, $fields);
            }

            if ($name == 'card_expiry2') {
                $this->prepare_expiry_field($data, $fields, 2);
            }
        }

        return $fields;
    }

    private function prepare_expiry_field($data, &$fields, $sufix = '')
    {
        $expiry_pieces                         = explode('/', $data['value']);
        $fields["card_expiry_month{$sufix}"] = trim($expiry_pieces[0]);
        $fields["card_expiry_year{$sufix}"]  = trim($expiry_pieces[1]);
    }

    private function validate_amount_billet_and_card($fields, WC_Order $wc_order)
    {
        if ($fields['payment_method'] != 'billet_and_card') {
            return;
        }

        $billet_value = Utils::get_value_by($fields, 'billet_value');
        $card_value   = Utils::get_value_by($fields, 'card_order_value');
        $total        = Utils::format_order_price($wc_order->get_total());
        $billet       = Utils::format_desnormalized_order_price($billet_value);
        $credit_card  = Utils::format_desnormalized_order_price($card_value);
        $amount       = intval($billet) + intval($credit_card);

        if ($amount < $total) {
            wp_send_json_error(__('The sum of boleto and credit card is less than the total', 'woo-pagarme-payments'));
        }

        if ($amount > $total) {
            wp_send_json_error(__('The sum of boleto and credit card is greater than the total', 'woo-pagarme-payments'));
        }
    }

    private function validate_amount_2_cards($fields, WC_Order $wc_order)
    {
        if ($fields['payment_method'] != '2_cards') {
            return;
        }

        $card1  = Utils::get_value_by($fields, 'card_order_value');
        $card2  = Utils::get_value_by($fields, 'card_order_value2');
        $total  = Utils::format_order_price($wc_order->get_total());
        $value1 = Utils::format_desnormalized_order_price($card1);
        $value2 = Utils::format_desnormalized_order_price($card2);
        $amount = intval($value1) + intval($value2);

        if ($amount < $total) {
            wp_send_json_error(__('The sum of the two credit cards is less than the total', 'woo-pagarme-payments'));
        }

        if ($amount > $total) {
            wp_send_json_error(__('The sum of the two credits cards is greater than the total', 'woo-pagarme-payments'));
        }
    }

    private function validate_brands($fields)
    {
        $setting = Setting::get_instance();
        $brand1  = Utils::get_value_by($fields, 'brand');
        $brand2  = Utils::get_value_by($fields, 'brand2');

        $flags = $setting->cc_flags;

        if (empty($flags)) {
            return;
        }

        if ($brand1 && !in_array($brand1, $flags)) {
            wp_send_json_error(sprintf(__('The card brand <b>%s</b> is not supported.', 'woo-pagarme-payments'), $brand1));
        }

        if ($brand2 && !in_array($brand2, $flags)) {
            wp_send_json_error(sprintf(__('The card brand <b>%s</b> is not supported.', 'woo-pagarme-payments'), $brand2));
        }
    }
}
