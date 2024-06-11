<?php

namespace Woocommerce\Pagarme\Helper;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Order;
use WC_Order;

class Utils
{
    /**
     * Sanitize value from custom method
     *
     * @since 1.0
     * @param string $name
     * @param mixed $default
     * @param string|array $sanitize
     * @return mixed
     */
    public static function request($type, $name, $default, $sanitize = 'rm_tags')
    {
        $request = filter_input_array($type, FILTER_SANITIZE_SPECIAL_CHARS);

        if (!isset($request[$name]) || empty($request[$name])) {
            return $default;
        }

        return self::sanitize($request[$name], $sanitize);
    }

    /**
     * Sanitize value from methods post
     *
     * @since 1.0
     * @param string $name
     * @param mixed $default
     * @param string|array $sanitize
     * @return mixed
     */
    public static function post($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_POST, $name, $default, $sanitize);
    }

    /**
     * Sanitize value from methods get
     *
     * @since 1.0
     * @param string $name
     * @param mixed $default
     * @param string|array $sanitize
     * @return mixed
     */
    public static function get($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_GET, $name, $default, $sanitize);
    }

    /**
     * Get filtered super global server by key
     *
     * @since 1.0
     * @param string $key
     * @return string
     */
    public static function server($key)
    {
        $value = self::get_value_by($_SERVER, strtoupper($key));

        return self::rm_tags($value, true);
    }

    /**
     * Sanitize requests
     *
     * @since 1.0
     * @param string $value
     * @param string|array $sanitize
     * @return string
     */
    public static function sanitize($value, $sanitize)
    {
        if (!is_callable($sanitize)) {
            return (false === $sanitize) ? $value : self::rm_tags($value);
        }

        if (is_array($value)) {
            return array_map($sanitize, $value);
        }

        return call_user_func($sanitize, $value);
    }

    /**
     * Properly strip all HTML tags including script and style
     *
     * @since 1.0
     * @param mixed string|array $value
     * @param bool $remove_breaks
     * @return mixed string|array
     */
    public static function rm_tags($value, $remove_breaks = false)
    {
        if (empty($value) || is_object($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(__METHOD__, $value);
        }

        return wp_strip_all_tags($value, $remove_breaks);
    }

    /**
     * Verify request ajax
     *
     * @since 1.0
     * @return bool
     */
    public static function is_request_ajax()
    {
         return ( strtolower(self::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest'
                || (0 === strpos(self::server('QUERY_STRING'), 'wc-ajax'))
                || strtolower(self::server('HTTP_X_REQUEST_TYPE')) === 'ajax');
                // || ;
    }

    /**
     * Verify if request is from checkout
     *
     * @since 1.0
     * @return boolean
     */
    public static function isCheckoutRequest()
    {
        if(function_exists('is_checkout')) {
            return is_checkout();
        }
        return false;
    }
    /**
     * Get value by array index
     *
     * @since 1.0
     * @param array $args
     * @param string|int $index
     * @return string
     */
    public static function get_value_by($args, $index, $default = '')
    {
        if (!array_key_exists($index, $args) || empty($args[$index])) {
            return $default;
        }

        return $args[$index];
    }

    /**
     * Admin sanitize url
     *
     * @since 1.0
     * @param string $path
     * @return string
     */
    public static function get_admin_url($path = '')
    {
        return esc_url(get_admin_url(null, $path));
    }

    /**
     * Site URL
     *
     * @since 1.0
     * @param string $path
     * @return string
     */
    public static function get_site_url($path = '')
    {
        return esc_url(get_site_url(null, $path));
    }

    /**
     * Add prefix in string
     *
     * @since 1.0
     * @param string $after
     * @param string $before
     * @return string
     */
    public static function add_prefix($after, $before = '')
    {
        return $before . Core::PREFIX . $after;
    }

    /**
     * Component attribute with prefix
     *
     * @since 1.0
     * @param string $name
     * @return string
     */
    public static function get_component($name)
    {
        return self::add_prefix(sprintf('-component="%s"', $name), 'data-');
    }

    /**
     * Format and validate phone number with DDD
     *
     * @since 1.0
     * @param string $phone
     * @return string
     */
    public static function format_phone_number($phone)
    {
        $phone = preg_replace(array('/[^\d]+/', '/^(?![1-9])0/'), '', $phone);

        if (strlen($phone) < 10) {
            return '';
        }

        return array(substr($phone, 0, 2), substr($phone, 2));
    }

    /**
     * Format order price with amount
     *
     * @since 1.0
     * @param mixed string|float|int $price
     * @return int
     */
    public static function format_order_price($price)
    {
        if (empty($price)) {
            return;
        }

        return @(int)number_format($price, 2, '', '');
    }

    /**
     * Format order price with a currency symbol
     *
     * @since 1.0
     * @param mixed string|float|int $price
     * @return string
     */
    public static function format_order_price_with_currency_symbol($price)
    {
        if (empty($price)) {
            return;
        }

        return 'R$' . (string)number_format($price, 2, ',', '.');
    }

    /**
     * Format desnormalized order price with amount
     *
     * @since 1.0
     * @param mixed string|float|int $price
     * @return int
     */
    public static function format_desnormalized_order_price($price)
    {
        if (empty($price)) {
            return;
        }

        $price = self::normalize_price($price);

        return self::format_order_price($price);
    }

    public static function normalize_price($price)
    {
        if (empty($price)) {
            return;
        }

        $price = str_replace('.', '', $price);
        $price = str_replace(',', '.', $price);

        return $price;
    }

    /**
     * Format order price to current currency
     *
     * @since 1.0
     * @param int $price
     * @return string
     */
    public static function format_order_price_to_view($price)
    {
        if (empty($price)) {
            return;
        }

        $value = $price / 100;
        $value = wc_price($value);

        return $value;
    }

    /**
     * Format document number
     *
     * @since 1.0
     * @param string $document
     * @return string
     */
    public static function format_document($document)
    {
        return preg_replace('/[^0-9]+/', '', $document);
    }

    /**
     * Get the order id by meta value
     *
     * @since 1.0
     * @param string $meta_value
     * @return int
     */
    public static function get_order_by_meta_value($meta_value)
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT
				`post_id`
			 FROM
			 	`{$wpdb->postmeta}`
			 WHERE
			 	`meta_value` = %s
			 LIMIT 1
			",
            $meta_value
        );

        return (int) $wpdb->get_var($query);
    }

    /**
     * Conversion of date
     *
     * @param string $date
     * @param string $format
     * @param string $search
     * @param string $replace
     * @return string
     */
    public static function convert_date($date, $format = 'Y-m-d', $search = '/', $replace = '-')
    {
        if ($search && $replace) {
            $date = str_replace($search, $replace, $date);
        }

        return date_i18n($format, strtotime($date));
    }

    public static function str_to_float($string)
    {
        return floatval(str_replace(',', '.', $string));
    }

    public static function calc_percentage($percentage, $total)
    {
        if (!$percentage) {
            return 0;
        }

        $percentage = self::str_to_float($percentage);

        return ($percentage / 100) * $total;
    }

    public static function get_json_post_data()
    {
        $post_data = file_get_contents('php://input');

        return empty($post_data) ? false : json_decode($post_data);
    }

    public static function error_server_json($code, $message = 'Generic Message Error', $echo = true)
    {
        $response = json_encode(
            array(
                'status'  => 'error',
                'code'    => $code,
                'message' => $message,
            )
        );

        if (!$echo) {
            return $response;
        }

        echo esc_attr($response);
    }


    public static function get_phone_country_code($country)
    {
        $list = array(
            'BR' => 55,
            'US' => 1,
            'UK' => 44,
            'ES' => 34,
        );

        return isset($list[$country]) ? $list[$country] : 55;
    }

    public static function build_customer_address_from_order(Order $order)
    {

        return array(
            'street'       => substr($order->getWcOrder()->get_billing_address_1(), 0, 64),
            'number'       => substr($order->get_meta('billing_number'), 0, 15),
            'complement'   => substr($order->getWcOrder()->get_billing_address_2(), 0, 64),
            'zip_code'     => preg_replace('/[^\d]+/', '', $order->getWcOrder()->get_billing_postcode()),
            'neighborhood' => substr($order->get_meta('billing_neighborhood'), 0, 64),
            'city'         => substr($order->get_meta('billing_city'), 0, 64),
            'state'        => substr($order->get_meta('billing_state'), 0, 2),
            'country'      => 'BR'
        );
    }

    public static function build_document_from_order(Order $order)
    {
        if (!empty($order->get_meta('billing_cpf'))) {
            return array(
                'type'  => 'individual',
                'value' => $order->get_meta('billing_cpf'),
            );
        }

        if (!empty($order->get_meta('billing_cnpj'))) {
            return array(
                'type'  => 'company',
                'value' => $order->get_meta('billing_cnpj'),
                );
        }

        return array(
            'type'  => '',
            'value' => '',
        );
    }

    public static function build_customer_phones_from_order(Order $order)
    {

        $phones    = array();
        $phone     = $order->get_meta('billing_phone');
        $cellphone = $order->get_meta('billing_cellphone');

        if ($phone) {
            $phoneParts               = self::format_phone_number($phone);
            $phones['home_phone'] = array(
                'country_code' => self::get_phone_country_code($order->getWcOrder()->get_billing_country()),
                'area_code'    => isset($phoneParts[0]) ? $phoneParts[0] : '',
                'number'       => isset($phoneParts[1]) ? $phoneParts[1] : '',
            );

            $phones["home_phone"]['complete_phone'] =
                $phones['home_phone']['area_code'] .
                $phones['home_phone']['number'];
        }

        if ($cellphone) {
            $phoneParts                 = self::format_phone_number($cellphone);
            $phones['mobile_phone'] = array(
                'country_code' => self::get_phone_country_code($order->getWcOrder()->get_billing_country()),
                'area_code'    => isset($phoneParts[0]) ? $phoneParts[0] : '',
                'number'       => isset($phoneParts[1]) ? $phoneParts[1] : '',
            );

            $phones["mobile_phone"]['complete_phone'] =
                $phones['mobile_phone']['area_code'] .
                $phones['mobile_phone']['number'];
        } else {
            $phones['mobile_phone'] = $phones['home_phone'];
        }

        return $phones;
    }

    public static function build_customer_shipping_from_wc_order(WC_Order $wc_order)
    {

        $method = $wc_order->get_shipping_method();
        $order  = new Order($wc_order->get_id());

        if (!$method) {
            $method = 'Não informado';
        }

        $total    = self::format_order_price($wc_order->get_shipping_total());
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
