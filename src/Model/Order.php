<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Kernel\Services\OrderService;
use WC_Order;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;

class Order extends Meta
{
    protected $response_data;
    protected $payment_method;
    protected $pagarme_status;
    protected $pagarme_id;
    protected $wc_order;
    protected $settings;

    // == BEGIN WC ORDER ==
    protected $billing_persontype;
    protected $billing_cnpj;
    protected $billing_first_name;
    protected $billing_last_name;
    protected $billing_email;
    protected $billing_birthdate;
    protected $billing_country;
    protected $billing_phone;
    protected $billing_cellphone;
    protected $billing_address_1;
    protected $billing_address_2;
    protected $billing_number;
    protected $billing_neighborhood;
    protected $billing_city;
    protected $billing_state;
    protected $billing_postcode;
    protected $billing_cpf;
    protected $shipping_address_1;
    protected $shipping_number;
    protected $shipping_address_2;
    protected $shipping_postcode;
    protected $shipping_neighborhood;
    protected $shipping_city;
    protected $shipping_state;
    // == END WC ORDER ==

    public $with_prefix = array(
        'payment_method'    => 1,
        'response_data'     => 1,
        'pagarme_status'    => 1,
        'pagarme_id'        => 1,
        'attempts'          => 1
    );

    /** phpcs:disable */
    public function __construct($ID = false)
    {
        parent::__construct($ID);
        $this->wc_order = $this->getWcOrderById($ID);
        $this->settings = new Config();
    }
    /** phpcs:enable */

    public function payment_on_hold()
    {
        $current_status = $this->wc_order->get_status();

        if (!in_array($current_status, ['on-hold', 'completed', 'canceled', 'cancelled', 'processing'])) {
            $this->wc_order->update_status('on-hold', __('Pagar.me: Awaiting payment confirmation.', 'woo-pagarme-payments'));
            wc_maybe_reduce_stock_levels($this->wc_order->get_id());
        }

        $statusArray = [
            'previous_status' => $current_status,
            'new_status' => $this->wc_order->get_status()
        ];
        $this->wc_order->save();
        $this->log($statusArray);
    }

    public function payment_paid()
    {
        $current_status = $this->wc_order->get_status();

        if (!in_array($current_status, ['completed', 'processing'])) {
            $this->wc_order->add_order_note(__('Pagar.me: Payment has already been confirmed.', 'woo-pagarme-payments'));
            $this->wc_order->payment_complete();
        }

        if (!$this->needs_processing()) {
            $this->wc_order->set_status('completed');
        }

        $statusArray = [
            'previous_status' => $current_status,
            'new_status' => $this->wc_order->get_status()
        ];
        $this->wc_order->save();
        $this->log($statusArray);
    }

    /**
     * @return void
     */
    public function payment_canceled()
    {
        $current_status = $this->wc_order->get_status();

        if (!in_array($current_status, ['cancelled', 'canceled'])) {
            $this->wc_order->update_status(
                'cancelled',
                __('Pagar.me: Payment canceled.', 'woo-pagarme-payments')
            );
        }

        $statusArray = [
            'previous_status' => $current_status,
            'new_status' => $this->wc_order->get_status()
        ];

        $this->log($statusArray);
    }

    public function paymentFailed()
    {
        $current_status = $this->wc_order->get_status();

        if ($current_status !== 'failed') {
            $this->wc_order->update_status('failed', __('Pagar.me: Payment failed.', 'woo-pagarme-payments'));
        }

        $statusArray = [
            'previous_status' => $current_status,
            'new_status' => $this->wc_order->get_status()
        ];

        $this->log($statusArray);
    }

    public function update_by_pagarme_status($pagarme_status)
    {
        switch ($pagarme_status) {
            case 'pending':
                $this->payment_on_hold();
                break;
            case 'paid':
            case OrderStatus::PROCESSING:
                $this->payment_paid();
                break;
            case 'failed':
                $this->paymentFailed();
                break;
            case 'canceled':
                $this->payment_canceled();
                break;
        }
    }

    public function get_charges()
    {
        $orderService = new OrderService();

        $order = $orderService->getOrderByPlatformId(
            strval($this->ID)
        );

        if (!$order) {
            return false;
        }

        $charges = $order->getCharges();

        if (!$charges) {
            return false;
        }

        return $charges;
    }

    /**
     * Returns the shipping data. If it is empty then returns billing data as fallback.
     *
     * @return array
     */
    public function get_shipping_info()
    {
        return array(
            'address_1'    => $this->handle_shipping_properties('address_1'),
            'number'       => $this->handle_shipping_properties('number'),
            'address_2'    => $this->handle_shipping_properties('address_2'),
            'postcode'     => $this->handle_shipping_properties('postcode'),
            'neighborhood' => $this->handle_shipping_properties('neighborhood'),
            'city'         => $this->handle_shipping_properties('city'),
            'state'        => $this->handle_shipping_properties('state'),
        );
    }

    private function handle_shipping_properties($prop)
    {
        $method_get_shipping = 'get_shipping_' . $prop;
        if (method_exists($this->wc_order, $method_get_shipping)) {
            $shipping_prop = $this->wc_order->{$method_get_shipping}();
            if ( ! empty( $shipping_prop ) ) {
                return $shipping_prop;
            }
        }

        $method_get_billing = 'get_billing_' . $prop;
        if (method_exists($this->wc_order, $method_get_billing)) {
            $shipping_prop = $this->wc_order->{$method_get_billing}();
            if ( ! empty( $shipping_prop ) ) {
                return $shipping_prop;
            }
        }

        $shipping_prop = $this->__get("shipping_{$prop}");

        if (empty($shipping_prop)) {
            return $this->__get("billing_{$prop}");
        }

        return $shipping_prop;
    }

    private function log($content)
    {

        $file = 'woo-pagarme';
        $message =
            'ORDER STATUS UPDATE: #' .
            $this->wc_order->get_id() .
            json_encode($content, JSON_PRETTY_PRINT);

        if (!empty($this->settings)) {
            $this->settings->log()->add($file, $message);
        }
    }

    /**
     * See if the order needs processing before it can be completed.
     * @return bool
     */
    public function needs_processing() {
        $needs_processing = false;
        if ( count( $this->wc_order->get_items() ) > 0 ) {
            foreach ( $this->wc_order->get_items() as $item ) {
                if ( $item->is_type( 'line_item' ) ) {
                    $product = $item->get_product();
                    if ( !$product ) {
                        continue;
                    }
                    if ( !$product->is_downloadable() && !$product->is_virtual() ) {
                        $needs_processing = true;
                        break;
                    }
                }
            }
        }
        return $needs_processing;
    }

    /**
     * @return float
     */
    public function getTotalAmountByCharges()
    {
        if(!$this->get_charges()) {
            return false;
        }
        $valueTotal = 0;
        foreach($this->get_charges() as $charge) {
            $valueTotal += $charge->getAmount();
        }
        return $valueTotal/100;
    }

    /**
     * @param mixed $totalWithInstallmentFee
     * @param mixed $totalWithoutInstallmentsFee
     * @return float
     */
    public function calculateInstallmentFee($totalWithInstallmentFee, $totalWithoutInstallmentsFee)
    {
        return Utils::str_to_float($totalWithInstallmentFee) - Utils::str_to_float($totalWithoutInstallmentsFee);
    }

    public function isPagarmePaymentMethod()
    {
        if (property_exists($this, 'wc_order')) {
            $paymentMethod = $this->wc_order->get_payment_method();
            return $paymentMethod === AbstractGateway::PAGARME
                || 0 === strpos($paymentMethod, AbstractGateway::WC_PAYMENT_PAGARME);
        }
        return false;
    }

    public function getWcOrder()
    {
        return $this->wc_order;
    }

    private function getWcOrderById($id = false)
    {
        global $theorder;
        if(empty($theorder) || ((int)$id !== $theorder->get_id() && $id !== false)) {
            return new WC_Order($id);
        }
        return $theorder;
    }
}
