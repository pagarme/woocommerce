<?php

namespace Woocommerce\Pagarme\Helper;

if (!function_exists('add_action')) {
    exit(0);
}

use WC_Blocks_Utils;
use WC_Order;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Order;

class Utils
{
    /**
     * Sanitize value from custom method
     *
     * @param string $name
     * @param mixed $default
     * @param string|array $sanitize
     *
     * @return mixed
     * @since 1.0
     */
    public static function request($type, $name, $default, $sanitize = 'rm_tags')
    {
        $request = filter_input_array($type, FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($request[$name])) {
            return $default;
        }

        return self::sanitize($request[$name], $sanitize);
    }

    /**
     * Sanitize value from methods post
     *
     * @param string $name
     * @param mixed $default
     * @param string|array $sanitize
     *
     * @return mixed
     * @since 1.0
     */
    public static function post($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_POST, $name, $default, $sanitize);
    }

    /**
     * Sanitize value from methods get
     *
     * @param string $name
     * @param mixed $default
     * @param string|array $sanitize
     *
     * @return mixed
     * @since 1.0
     */
    public static function get($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_GET, $name, $default, $sanitize);
    }

    /**
     * Get filtered super global server by key
     *
     * @param string $key
     *
     * @return string
     * @since 1.0
     */
    public static function server($key)
    {
        $value = self::get_value_by($_SERVER, strtoupper($key));

        return self::rm_tags($value, true);
    }

    /**
     * Sanitize requests
     *
     * @param string $value
     * @param string|array $sanitize
     *
     * @return string
     * @since 1.0
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
     * @param string|array $value
     * @param bool $remove_breaks
     *
     * @return string|array
     * @since 1.0
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
     * @return bool
     * @since 1.0
     */
    public static function is_request_ajax()
    {
        return (strtolower(self::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest'
                || (0 === strpos(self::server('QUERY_STRING'), 'wc-ajax'))
                || strtolower(self::server('HTTP_X_REQUEST_TYPE')) === 'ajax');
    }

    /**
     * Verify if request is from checkout
     * @return boolean
     * @since 1.0
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
     * @param array $args
     * @param string|int $index
     *
     * @return string
     * @since 1.0
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
     * @param string $path
     *
     * @return string
     * @since 1.0
     */
    public static function get_admin_url($path = '')
    {
        return esc_url(get_admin_url(null, $path));
    }

    /**
     * Site URL
     *
     * @param string $path
     *
     * @return string
     * @since 1.0
     */
    public static function get_site_url($path = '')
    {
        return esc_url(get_site_url(null, $path));
    }

    /**
     * Add prefix in string
     *
     * @param string $after
     * @param string $before
     *
     * @return string
     * @since 1.0
     */
    public static function add_prefix($after, $before = '')
    {
        return $before . Core::PREFIX . $after;
    }

    /**
     * Component attribute with prefix
     *
     * @param string $name
     *
     * @return string
     * @since 1.0
     */
    public static function get_component($name)
    {
        return self::add_prefix(sprintf('-component="%s"', $name), 'data-');
    }

    /**
     * Format and validate phone number with DDD
     *
     * @param string $phone
     *
     * @return array
     * @since 1.0
     */
    public static function format_phone_number($phone)
    {
        $phone = preg_replace(array('/[^\d]+/', '/^(?![1-9])0/'), '', $phone);

        if (strlen($phone) < 10) {
            return [];
        }

        return array(substr($phone, 0, 2), substr($phone, 2));
    }

    /**
     * Format order price with amount
     *
     * @param string|float|int $price
     *
     * @return int|void
     * @since 1.0
     */
    public static function format_order_price($price)
    {
        if (empty($price)) {
            return;
        }

        return @(int) number_format($price, 2, '', '');
    }

    /**
     * Format order price with a currency symbol
     *
     * @param string|float|int $price
     *
     * @return string
     * @since 1.0
     */
    public static function format_order_price_with_currency_symbol($price, $currency = 'BRL')
    {
        if (empty($price)) {
            return '';
        }

        return get_woocommerce_currency_symbol($currency) . (string) number_format($price, 2, ',', '.');
    }

    /**
     * Format desnormalized order price with amount
     *
     * @param string|float|int $price
     *
     * @return int|void
     * @since 1.0
     */
    public static function format_desnormalized_order_price($price)
    {
        if (empty($price)) {
            return;
        }

        $price = self::normalize_price($price);

        return self::format_order_price($price);
    }

    /**
     * @param $price
     *
     * @return array|string|string[]|void
     */
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
     * @param int $price
     *
     * @return string|void
     * @since 1.0
     */
    public static function format_order_price_to_view($price)
    {
        if (empty($price)) {
            return;
        }

        $value = $price / 100;

        return wc_price($value);
    }

    /**
     * Format document number
     *
     * @param string $document
     *
     * @return string
     * @since 1.0
     */
    public static function format_document($document)
    {
        return preg_replace('/[^0-9]+/', '', $document);
    }

    /**
     * Get the order id by meta value
     *
     * @param string $meta_value
     *
     * @return int
     * @since 1.0
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
     *
     * @return string
     */
    public static function convert_date($date, $format = 'Y-m-d', $search = '/', $replace = '-')
    {
        if ($search && $replace) {
            $date = str_replace($search, $replace, $date);
        }

        return date_i18n($format, strtotime($date));
    }

    /**
     * @param $string
     *
     * @return float
     */
    public static function str_to_float($string)
    {
        return floatval(str_replace(',', '.', $string));
    }

    /**
     * @param $percentage
     * @param $total
     *
     * @return float|int
     */
    public static function calc_percentage($percentage, $total)
    {
        if (!$percentage) {
            return 0;
        }

        $percentage = self::str_to_float($percentage);

        return ($percentage / 100) * $total;
    }

    /**
     * @return false|mixed
     */
    public static function get_json_post_data()
    {
        $post_data = file_get_contents('php://input');

        return empty($post_data) ? false : json_decode($post_data);
    }

    /**
     * @param $code
     * @param $message
     * @param $echo
     *
     * @return false|string|void
     */
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

    /**
     * @param $country
     *
     * @return int
     */
    public static function get_phone_country_code($country)
    {
        $list = array(
            'BR' => 55,
            'US' => 1,
            'UK' => 44,
            'ES' => 34,
        );

        return $list[$country] ?? 55;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
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

    /**
     * @param Order $order
     *
     * @return array|string[]
     */
    public static function build_document_from_order(Order $order): array
    {
        $documentFields = [
            'billing_cpf',
            'billing_cnpj',
            'billing_document',
            'wc_billing/address/document'
        ];

        foreach ($documentFields as $field) {
            if (!empty($order->get_meta($field))) {
                $document = $order->get_meta($field);
                return [
                    'type'  => self::getCustomerTypeByDocumentNumber($document),
                    'value' => $document
                ];
            }
        }

        return array(
            'type'  => '',
            'value' => ''
        );
    }

    public static function getCustomerTypeByDocumentNumber($document): string
    {
        $documentNumber = preg_replace('/\D/', '', $document ?? '');
        return strlen($documentNumber) === 14 ? 'company' : 'individual';
    }

    public static function getDocumentTypeByDocumentNumber($document): string
    {
        $documentNumber = preg_replace('/\D/', '', $document ?? '');
        return strlen($documentNumber) === 14 ? 'cnpj' : 'cpf';
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public static function build_customer_phones_from_order(Order $order)
    {

        $phones = array();
        $phone = $order->get_meta('billing_phone');
        $cellphone = $order->get_meta('billing_cellphone');

        if ($phone) {
            $phoneParts = self::format_phone_number($phone);
            $phones['home_phone'] = array(
                'country_code' => self::get_phone_country_code($order->getWcOrder()->get_billing_country()),
                'area_code'    => $phoneParts[0] ?? '',
                'number'       => $phoneParts[1] ?? '',
            );

            $phones["home_phone"]['complete_phone'] =
                $phones['home_phone']['area_code'] .
                $phones['home_phone']['number'];
        }

        if ($cellphone) {
            $phoneParts = self::format_phone_number($cellphone);
            $phones['mobile_phone'] = array(
                'country_code' => self::get_phone_country_code($order->getWcOrder()->get_billing_country()),
                'area_code'    => $phoneParts[0] ?? '',
                'number'       => $phoneParts[1] ?? '',
            );

            $phones["mobile_phone"]['complete_phone'] =
                $phones['mobile_phone']['area_code'] .
                $phones['mobile_phone']['number'];
        } else {
            $phones['mobile_phone'] = $phones['home_phone'];
        }

        return $phones;
    }

    /**
     * @param WC_Order $wc_order
     *
     * @return array
     */
    public static function build_customer_shipping_from_wc_order(WC_Order $wc_order)
    {

        $method = $wc_order->get_shipping_method();
        $order = new Order($wc_order->get_id());

        if (!$method) {
            $method = 'Não informado';
        }

        $total = self::format_order_price($wc_order->get_shipping_total());
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

    /**
     * @param $value
     *
     * @return string
     */
    public static function snakeToPascalCase($value)
    {
        return ucfirst(str_replace('_', '', ucwords($value, '_')));
    }

    /**
     * @return bool
     */
    public static function isCheckoutBlock() {
        if (!class_exists(WC_Blocks_Utils::class)) {
            return false;
        }

        return WC_Blocks_Utils::has_block_in_page(wc_get_page_id('checkout'), 'woocommerce/checkout');
    }

    /**
     * @param $path
     * @param $fileName
     *
     * @return string
     */
    private static function getScriptUrl($path, $fileName): string
    {
        return Core::plugins_url('assets/javascripts/' . $path . '/' . $fileName . '.js');
    }

    /**
     * @param array $deps
     *
     * @return array
     */
    private static function setScriptDeps(array $deps = []): array
    {
        $defaultDeps = ['jquery'];
        $mergedDeps = array_merge($defaultDeps, $deps);

        return array_unique($mergedDeps);
    }

    /**
     * @param $path
     * @param $fileName
     *
     * @return false|int
     */
    private static function getScriptVersion($path, $fileName)
    {
        return Core::filemtime('assets/javascripts/' . $path . '/' . $fileName . '.js');
    }

    /**
     *
     *
     * @param string $path The path to the script file, starting after the folders `assets/javascript/[path]`
     * @param string $fileName The file name, without the extension `.js`
     * @param array $deps Array of dependencies. `jquery` is added automatically
     *
     * @return array Returns an array with three keys: `src`, `deps` and `ver`, to be used with `wp_register_script`
     */
    public static function getRegisterScriptParameters(string $path, string $fileName, array $deps = []): array
    {
        $path = rtrim($path, '/');
        $fileName = trim($fileName);

        return [
            'src'  => self::getScriptUrl($path, $fileName),
            'deps' => self::setScriptDeps($deps),
            'ver'  => self::getScriptVersion($path, $fileName)
        ];
    }

    /**
     * @return bool
     */
    public static function isCheckoutBlocksActive()
    {
        if (!class_exists('\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils')) {
            return false;
        }
        return \Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
    }
}
