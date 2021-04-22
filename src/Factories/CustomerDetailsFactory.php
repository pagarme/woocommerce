<?php

namespace Woocommerce\Pagarme\Factories;

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Helper\Utils;
use WC_Order;

class CustomerDetailsFactory
{
    private function get_phone_country_code($country)
    {
        $list = array(
            'BR' => 55,
            'US' => 1,
            'UK' => 44,
            'ES' => 34,
        );

        return isset($list[$country]) ? $list[$country] : 55;
    }

    function build_customer_address_from_order(Order $order)
    {

        return array(
            'street'       => substr($order->billing_address_1, 0, 64),
            'number'       => substr($order->billing_number, 0, 15),
            'complement'   => substr($order->billing_address_2, 0, 64),
            'zip_code'     => preg_replace('/[^\d]+/', '', $order->billing_postcode),
            'neighborhood' => substr($order->billing_neighborhood, 0, 64),
            'city'         => substr($order->billing_city, 0, 64),
            'state'        => substr($order->billing_state, 0, 2),
            'country'      => 'BR'
        );
    }

    function build_document_from_order(Order $order)
    {

        $wcbcf_options = get_option('wcbcf_settings'); //WooCommerce Extra Checkout Fields

        $cpf = array(
            'type'  => 'individual',
            'value' => $order->billing_cpf,
        );

        $cnpj = array(
            'type'  => 'company',
            'value' => $order->billing_cnpj,
        );

        switch ($wcbcf_options['person_type']) {
            case 1:
                return ($order->billing_persontype == 1) ? $cpf : $cnpj;
            case 2:
                return $cpf;
            case 3:
                return $cnpj;
            default:
                return array(
                    'type'  => '',
                    'value' => '',
                );
        }
    }

    function build_customer_phones_from_order(Order $order)
    {

        $phones    = array();
        $phone     = $order->billing_phone;
        $cellphone = $order->billing_cellphone;

        if ($phone) {
            $pieces               = Utils::format_phone_number($phone);
            $phones['home_phone'] = array(
                'country_code' => $this->get_phone_country_code($order->billing_country),
                'area_code'    => isset($pieces[0]) ? $pieces[0] : '',
                'number'       => isset($pieces[1]) ? $pieces[1] : '',
            );

            $phones["home_phone"]['complete_phone'] =
                $phones['home_phone']['area_code'] .
                $phones['home_phone']['number'];
        }

        if ($cellphone) {
            $pieces                 = Utils::format_phone_number($cellphone);
            $phones['mobile_phone'] = array(
                'country_code' => $this->get_phone_country_code($order->billing_country),
                'area_code'    => isset($pieces[0]) ? $pieces[0] : '',
                'number'       => isset($pieces[1]) ? $pieces[1] : '',
            );

            $phones["mobile_phone"]['complete_phone'] =
                $phones['mobile_phone']['area_code'] .
                $phones['mobile_phone']['number'];
        } else {
            $phones['mobile_phone'] = $phones['home_phone'];
        }

        return $phones;
    }

    function build_customer_shipping_from_wc_order(WC_Order $wc_order)
    {

        $method = $wc_order->get_shipping_method();
        $order  = new Order($wc_order->get_order_number());

        if (!$method) {
            $method = 'Não informado';
        }

        $total    = Utils::format_order_price($wc_order->get_total_shipping());
        $shipping = $order->get_shipping_info();

        return array(
            'amount'      => $total,
            'description' => $method,
            'address'     => array(
                'street'       => substr($shipping['address_1'], 0, 64),
                'number'       => substr($shipping['number'], 0, 15),
                'complement'   => substr($shipping['address_2'], 0, 64),
                'zip_code'     => substr(preg_replace('/[^\d]+/', '', $shipping['postcode']), 0, 16),
                'neighborhood' => substr($shipping['neighborhood'], 0, 64),
                'city'         => substr($shipping['city'], 0, 64),
                'state'        => substr($shipping['state'], 0, 2),
                'country'      => 'BR',
            ),
        );
    }
}
