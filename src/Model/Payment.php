<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Api;
use Woocommerce\Pagarme\Resource\Tokens;

class Payment
{
    /**
     * Payment methods : billet, billet_and_card, 2_cards, credit_card, pix
     * @var string
     */
    public $payment_method;

    public function __construct($payment_method)
    {
        $this->payment_method = $payment_method;
        $this->settings       = Setting::get_instance();
    }

    /**
     * Return the payment array for API request
     *
     * @param $wc_order object order from woocommerce
     * @param $form_fields array Sent form fields
     * @param $customer object response of Woocommerce\Pagarme\Resource\Customer
     *
     * @return array
     */
    public function get_payment_data($wc_order, $form_fields, $customer)
    {
        $method_name = $this->get_method_name();

        if (!method_exists($this, $method_name)) {
            throw new \Exception('Payment method name not exists');
        }

        $data = $this->{$method_name}($wc_order, $form_fields, $customer);

        if ($method_name == 'pay_billet_and_card' || $method_name == 'pay_2_cards') {
            return $data;
        }

        return array($data);
    }

    /**
     * Return method name according to the selected payment method
     *
     * @return string
     */
    public function get_method_name()
    {
        return "pay_{$this->payment_method}";
    }

    /**
     * Return payment data for "boleto"
     *
     * @return array
     */
    public function pay_billet($wc_order, $form_fields)
    {
        $billet           = $this->pay_billet_base();
        $billet['amount'] = Utils::format_order_price($wc_order->get_total());
        $multicustomer    = $this->get_multicustomer_data('billet', $form_fields);

        if ($multicustomer) {
            $billet['customer'] = $multicustomer;
        }

        return $billet;
    }

    /**
     * Return payment data for "pix"
     *
     * @return array
     */
    public function pay_pix($wc_order, $form_fields)
    {
        $pix           = $this->pay_pix_base();
        $pix['amount'] = Utils::format_order_price($wc_order->get_total());
        $multicustomer    = $this->get_multicustomer_data('pix', $form_fields);

        if ($multicustomer) {
            $pix['customer'] = $multicustomer;
        }

        return $pix;
    }

    /**
     * Return payment data for "voucher"
     *
     * @return array
     */
    public function pay_voucher($wc_order, $form_fields, $customer)
    {
        $card              = $this->pay_voucher_base($wc_order, $form_fields, $customer);
        $card_amount       = $wc_order->get_total();
        $card['amount']    = Utils::format_order_price($card_amount);
        return $card;
    }

    /**
     * Return payment data for "voucher" base
     *
     * @param $wc_order object order from woocommerce
     * @param $form_fields array Sent form fields
     * @param $customer object response of Woocommerce\Pagarme\Resource\Customer
     *
     * @return array
     */
    private function pay_voucher_base($wc_order, $form_fields, $customer)
    {
        $card_data = array(
            'payment_method' => 'voucher',
            'voucher'    => array(
                'statement_descriptor' => $this->settings->voucher_soft_descriptor,
                'card' => array(
                    'billing_address' => $this->get_billing_address_from_customer($customer, $wc_order)
                )
            ),
        );

        return $this->handle_credit_card_type($form_fields, $card_data, '');
    }

    /**
     * Return payment data for "credit_card"
     *
     * @param $wc_order object order from woocommerce
     * @param $form_fields array Sent form fields
     * @param $customer object response of Woocommerce\Pagarme\Resource\Customer
     *
     * @return array
     */
    public function pay_credit_card($wc_order, $form_fields, $customer)
    {
        $card              = $this->pay_credit_card_base($wc_order, $form_fields, $customer);
        $card_installments = Utils::get_value_by($form_fields, 'installments');
        $card_brand        = Utils::get_value_by($form_fields, 'brand');
        $card_amount       = $this->get_price_with_interest($wc_order->get_total(), $card_installments, $card_brand);
        $card['amount']    = Utils::format_order_price($card_amount);
        $multicustomer     = $this->get_multicustomer_data('card', $form_fields);

        if ($multicustomer) {
            $card['customer'] = $multicustomer;
        }

        if (!$multicustomer && !empty($customer->email)) {
            $card['customer'] = $customer;
        }

        return $card;
    }

    /**
     * Return payment data for "billet_and_card"
     *
     * @param $wc_order object order from woocommerce
     * @param $form_fields array Sent form fields
     * @param $customer object response of Woocommerce\Pagarme\Resource\Customer
     *
     * @return array
     */
    public function pay_billet_and_card($wc_order, $form_fields, $customer)
    {
        $billet_amount        = Utils::get_value_by($form_fields, 'billet_value');
        $billet               = $this->pay_billet_base();
        $billet['amount']     = Utils::format_desnormalized_order_price($billet_amount);
        $billet_multicustomer = $this->get_multicustomer_data('billet', $form_fields);

        if ($billet_multicustomer) {
            $billet['customer'] = $billet_multicustomer;
        }

        $card = $this->pay_credit_card_base($wc_order, $form_fields, $customer);

        if (!is_array($card) && $card->code != 200) {
            return $card;
        }

        $card_amount       = Utils::normalize_price(Utils::get_value_by($form_fields, 'card_order_value'));
        $card_installments = Utils::get_value_by($form_fields, 'installments');
        $card_brand        = Utils::get_value_by($form_fields, 'brand');

        $card_amount    = $this->get_price_with_interest($card_amount, $card_installments, $card_brand);
        $card['amount'] = Utils::format_order_price($card_amount);
        $multicustomer  = $this->get_multicustomer_data('card', $form_fields);

        if ($multicustomer) {
            $card['customer'] = $multicustomer;
        }

        return array($billet, $card);
    }

    /**
     * Return payment data for "2_cards"
     *
     * @param $wc_order object order from woocommerce
     * @param $form_fields array Sent form fields
     * @param $customer object response of Woocommerce\Pagarme\Resource\Customer
     *
     * @return array
     */
    public function pay_2_cards($wc_order, $form_fields, $customer)
    {
        $card1_amount = Utils::normalize_price(Utils::get_value_by($form_fields, 'card_order_value'));
        $card2_amount = Utils::normalize_price(Utils::get_value_by($form_fields, 'card_order_value2'));

        $card1 = $this->pay_credit_card_base($wc_order, $form_fields, $customer);
        $card2 = $this->pay_credit_card_base($wc_order, $form_fields, $customer, true);

        $card1_installments = Utils::get_value_by($form_fields, 'installments');
        $card2_installments = Utils::get_value_by($form_fields, 'installments2');

        $card1_brand = Utils::get_value_by($form_fields, 'brand');
        $card2_brand = Utils::get_value_by($form_fields, 'brand2');

        $card1_amount = $this->get_price_with_interest($card1_amount, $card1_installments, $card1_brand);
        $card2_amount = $this->get_price_with_interest($card2_amount, $card2_installments, $card2_brand);

        $card1['amount'] = Utils::format_order_price($card1_amount);
        $card2['amount'] = Utils::format_order_price($card2_amount);

        $multicustomer_card1 = $this->get_multicustomer_data('card1', $form_fields);
        $multicustomer_card2 = $this->get_multicustomer_data('card2', $form_fields);

        if ($multicustomer_card1) {
            $card1['customer'] = $multicustomer_card1;
        }

        if ($multicustomer_card2) {
            $card2['customer'] = $multicustomer_card2;
        }

        return array($card1, $card2);
    }

    /**
     * Return payment data for "credit_card" base (without amount)
     *
     * @param $wc_order object order from woocommerce
     * @param $form_fields array Sent form fields
     * @param $customer object response of Woocommerce\Pagarme\Resource\Customer
     *
     * @return array
     */
    private function pay_credit_card_base($wc_order, $form_fields, $customer, $is_second_card = false)
    {
        $suffix    = $is_second_card ? '2' : '';
        $card_data = array(
            'payment_method' => 'credit_card',
            'credit_card'    => array(
                'installments'         => Utils::get_value_by($form_fields, "installments{$suffix}"),
                'statement_descriptor' => $this->settings->cc_soft_descriptor,
                'capture'              => $this->settings->is_active_capture(),
                'card' => array(
                    'billing_address' => $this->get_billing_address_from_customer($customer, $wc_order)
                )
            ),
        );

        return $this->handle_credit_card_type($form_fields, $card_data, $suffix);
    }

    private function get_billing_address_from_customer($customer, $wc_order)
    {
        $addressArray = (array) $customer->address;

        if (empty($addressArray)) {
            $addressArray = $this->get_customer_address_from_wc_order($wc_order);
        }

        return array(
            'street' => $addressArray["street"],
            'complement' => $addressArray["complement"],
            'number' => $addressArray["number"],
            'zip_code' => $addressArray["zip_code"],
            'neighborhood' => $addressArray["neighborhood"],
            'city' => $addressArray["city"],
            'state' => $addressArray["state"],
            'country' => $addressArray["country"]
        );
    }

    private function get_customer_address_from_wc_order($wc_order)
    {
        $order = new Order($wc_order->get_order_number());

        return Utils::build_customer_address_from_order($order);
    }

    /**
     * Return payment data for "boleto"
     *
     * @return array
     */
    private function pay_billet_base()
    {
        $expiration_date = new \DateTime();
        $days            = (int) $this->settings->billet_deadline_days;

        if ($days) {
            $expiration_date->modify("+{$days} day");
        }

        return array(
            'payment_method' => 'boleto',
            'boleto' => array(
                'bank'         => $this->settings->billet_bank,
                'instructions' => $this->settings->billet_instructions,
                'due_at'       => $expiration_date->format('c'),
            ),
        );
    }

    /**
     * Return payment data for "pix"
     *
     * @return array
     */
    private function pay_pix_base()
    {
        return array(
            'payment_method' => 'pix'
        );
    }

    /**
     * Return which credit card type will be send. (card ou card_token)
     *
     * @param  array $form_fields
     * @param  array $card_data
     * @param  string $suffix Suffix for each attribute
     *
     * @return array
     */
    private function handle_credit_card_type($form_fields, $card_data, $suffix = '')
    {
        $card_id    = Utils::get_value_by($form_fields, "card_id{$suffix}", false);
        $pagarmetoken = !$suffix ? 'pagarmetoken1' : "pagarmetoken{$suffix}";

        if ($card_id) {
            $card_data['credit_card']['card_id'] = $card_id;
        } else {
            $card_data['credit_card']['card_token'] = Utils::get_value_by($form_fields, $pagarmetoken);
        }

        return $card_data;
    }

    private function get_price_with_interest($price, $installments, $flag = '')
    {
        $type   = intval($this->settings->cc_installment_type);
        $amount = $price;

        if ($type === Gateway::CC_TYPE_SINGLE) { // Parcelamento único
            $no_interest       = intval($this->settings->cc_installments_without_interest);
            $interest          = Utils::str_to_float($this->settings->cc_installments_interest);
            $interest_increase = Utils::str_to_float($this->settings->cc_installments_interest_increase);
            $max_installments  = intval($this->settings->cc_installments_maximum);
        } else { // Parcelamento por bandeira
            $settings_by_flag  = $this->settings->cc_installments_by_flag;
            $no_interest       = intval($settings_by_flag['no_interest'][$flag]);
            $interest          = Utils::str_to_float($settings_by_flag['interest'][$flag]);
            $interest_increase = Utils::str_to_float($settings_by_flag['interest_increase'][$flag]);
            $max_installments  = intval($settings_by_flag['max_installment'][$flag]);
        }

        if ($installments <= $no_interest) {
            return $amount;
        }

        if ($interest) {

            if ($interest_increase && $installments > $no_interest + 1) {
                $interest += ($interest_increase * ($installments - ($no_interest + 1)));
            }

            $amount += Utils::calc_percentage($interest, $price);
        }

        return $amount;
    }

    private function get_multicustomer_data($type, $form_fields)
    {
        $prefix     = "multicustomer_{$type}";
        $is_enabled = Utils::get_value_by($form_fields, "enable_multicustomers_{$type}");

        if (!$is_enabled) {
            return false;
        }

        $cpf      = Utils::get_value_by($form_fields, $prefix . '[cpf]');
        $zip_code = Utils::get_value_by($form_fields, $prefix . '[zip_code]');

        return array(
            'name'     => Utils::get_value_by($form_fields, $prefix . '[name]'),
            'email'    => Utils::get_value_by($form_fields, $prefix . '[email]'),
            'document' => Utils::format_document($cpf),
            'type'     => 'individual',
            'address' => array(
                'street'       => Utils::get_value_by($form_fields, $prefix . '[street]'),
                'number'       => Utils::get_value_by($form_fields, $prefix . '[number]'),
                'complement'   => Utils::get_value_by($form_fields, $prefix . '[complement]'),
                'neighborhood' => Utils::get_value_by($form_fields, $prefix . '[neighborhood]'),
                'zip_code'     => preg_replace('/[^\d]+/', '', $zip_code),
                'city'         => Utils::get_value_by($form_fields, $prefix . '[city]'),
                'state'        => Utils::get_value_by($form_fields, $prefix . '[state]'),
                'country'      => 'BR',
            ),
        );
    }
}
