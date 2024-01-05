<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;
use Woocommerce\Pagarme\Model\CardInstallments;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model;

use WC_Order;

class Checkout
{
    protected $cards = array();

    protected $payment_methods = [];

    /** @var CardInstallments */
    protected $cardInstallments;

    /**
     * @var Orders
     */
    protected $ordersController;

    public function __construct(
        CardInstallments $cardInstallments = null
    ) {
        $this->ordersController = new Orders();
        add_action('woocommerce_api_' . Model\Checkout::API_REQUEST, array($this, 'process_checkout_transparent'));
        $paymentDetails = new \Woocommerce\Pagarme\Block\Order\PaymentDetails();
        add_action('woocommerce_view_order', [$paymentDetails, 'render']);
        add_action('wp_ajax_xqRhBHJ5sW', array($this, 'build_installments'));
        add_action('wp_ajax_nopriv_xqRhBHJ5sW', array($this, 'build_installments'));
        $this->payment_methods = [
            'credit_card'     => __('Credit card', 'woo-pagarme-payments'),
            'billet'          => __('Boleto', 'woo-pagarme-payments'),
            'pix'             => __('Pix', 'woo-pagarme-payments'),
            '2_cards'         => __('2 credit cards', 'woo-pagarme-payments'),
            'billet_and_card' => __('Credit card and Boleto', 'woo-pagarme-payments'),
            'voucher'         => __('Voucher', 'woo-pagarme-payments'),
        ];
        $this->cardInstallments = $cardInstallments;
        if (!$this->cardInstallments) {
            $this->cardInstallments = new CardInstallments;
        }
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

        $order  = new Order($wc_order->get_id());
        $order->payment_method   = $fields['payment_method'];
        $order->update_meta('_payment_method_title', $this->payment_methods[$fields['payment_method']]);
        $order->update_meta('_payment_method', AbstractGateway::PAGARME . ' ' . $this->payment_methods[$fields['payment_method']]);
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

    /**
     * @return void
     */
    public function build_installments()
    {
        if (!Utils::is_request_ajax() || Utils::server('REQUEST_METHOD') !== 'GET') {
            exit(0);
        }

        $html = $this->cardInstallments->renderOptions(
            $this->cardInstallments->getInstallmentsByType(
                Utils::get('total', false),
                Utils::get('flag', false, 'esc_html')

        ));
        echo wp_kses_no_null($html);
        exit();
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
            $value = $this->sanitize_field($data['value']);

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

    private function sanitize_field($field)
    {
        if (is_array($field)) {
            $sanitizedData = [];
            foreach ($field as $key => $value) {
                $sanitizedData[$key] = sanitize_text_field($value);
            }
            return $sanitizedData;
        }

        return sanitize_text_field($field);
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
        $config = new Config();
        $brand1  = Utils::get_value_by($fields, 'brand');
        $brand2  = Utils::get_value_by($fields, 'brand2');

        $flags = $config->getCcFlags;

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
