<?php

namespace Woocommerce\Pagarme\Helper;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Model\Order;
use WC_Order;

class Utils
{
    /**
     * Sanitize value from custom method
     *
     * @since 1.0
     * @param String $name
     * @param Mixed $default
     * @param String|Array $sanitize
     * @return Mixed
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
     * @param String $name
     * @param Mixed $default
     * @param String|Array $sanitize
     * @return Mixed
     */
    public static function post($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_POST, $name, $default, $sanitize);
    }

    /**
     * Sanitize value from methods get
     *
     * @since 1.0
     * @param String $name
     * @param Mixed $default
     * @param String|Array $sanitize
     * @return Mixed
     */
    public static function get($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_GET, $name, $default, $sanitize);
    }

    /**
     * Sanitize value from cookie
     *
     * @since 1.0
     * @param String $name
     * @param Mixed $default
     * @param String|Array $sanitize
     * @return Mixed
     */
    public static function cookie($name, $default = '', $sanitize = 'rm_tags')
    {
        return self::request(INPUT_COOKIE, $name, $default, $sanitize);
    }

    /**
     * Get filtered super global server by key
     *
     * @since 1.0
     * @param String $key
     * @return String
     */
    public static function server($key)
    {
        $value = self::get_value_by($_SERVER, strtoupper($key));

        return self::rm_tags($value, true);
    }

    /**
     * Verify request by nonce
     *
     * @since 1.0
     * @param String $name
     * @param String $action
     * @return Boolean
     */
    public static function verify_nonce_post($name, $action)
    {
        return wp_verify_nonce(self::post($name, false), $action);
    }

    /**
     * Sanitize requests
     *
     * @since 1.0
     * @param String $value
     * @param String|Array $sanitize
     * @return String
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
     * @param Mixed String|Array $value
     * @param Boolean $remove_breaks
     * @return Mixed String|Array
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
     * Find the position of the first occurrence of a substring in a string
     *
     * @since 1.0
     * @param String $value
     * @param String $search
     * @return Boolean
     */
    public static function indexof($value, $search)
    {
        return (false !== strpos($value, $search));
    }

    /**
     * Verify request ajax
     *
     * @since 1.0
     * @param null
     * @return Boolean
     */
    public static function is_request_ajax()
    {
        return (strtolower(self::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
    }

    /**
     * Get charset option
     *
     * @since 1.0
     * @param Null
     * @return String
     */
    public static function get_charset()
    {
        return self::rm_tags(get_bloginfo('charset'));
    }

    /**
     * Descode html entityes
     *
     * @since 1.0
     * @param String $string
     * @return String
     */
    public static function html_decode($string)
    {
        return html_entity_decode($string, ENT_NOQUOTES, self::get_charset());
    }

    /**
     * Get value by array index
     *
     * @since 1.0
     * @param Array $args
     * @param String|int $index
     * @return String
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
     * @param String $path
     * @return String
     */
    public static function get_admin_url($path = '')
    {
        return esc_url(get_admin_url(null, $path));
    }

    /**
     * Site URL
     *
     * @since 1.0
     * @param String $path
     * @return String
     */
    public static function get_site_url($path = '')
    {
        return esc_url(get_site_url(null, $path));
    }

    /**
     * Permalink url sanitized
     *
     * @since 1.0
     * @param Integer $post_id
     * @return String
     */
    public static function get_permalink($post_id = 0)
    {
        return esc_url(get_permalink($post_id));
    }

    /**
     * Add prefix in string
     *
     * @since 1.0
     * @param String $after
     * @param String $before
     * @return String
     */
    public static function add_prefix($after, $before = '')
    {
        return $before . Core::PREFIX . $after;
    }

    /**
     * Component attribute with prefix
     *
     * @since 1.0
     * @param String $name
     * @return String
     */
    public static function get_component($name)
    {
        return self::add_prefix(sprintf('-component="%s"', $name), 'data-');
    }

    /**
     * Check is plugin settings page
     *
     * @since 1.0
     * @param null
     * @return Boolean
     */
    public static function is_settings_page()
    {
        return (self::get('section') === Core::SLUG);
    }

    /**
     * Format and validate phone number with DDD
     *
     * @since 1.0
     * @param String $phone
     * @return String
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
     * @param Mixed String|Float|Int $price
     * @return Integer
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
     * @param Mixed String|Float|Int $price
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
     * @param Mixed String|Float|Int $price
     * @return Integer
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
     * @param Int $price
     * @return String
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
     * Generate log file
     *
     * @since 1.0
     * @param Mixed $data
     * @param String $log_name
     * @return Void
     */
    public static function log($data, $log_name = 'debug')
    {
        $name = sprintf('%s-%s.log', $log_name, date('d-m-Y'));
        $log  = print_r($data, true) . PHP_EOL;
        $log .= "\n=============================\n";

        file_put_contents(Core::get_file_path($name, 'logs/'), $log, FILE_APPEND);
    }

    /**
     * Checks if the CPF is valid.
     *
     * @param  string $cpf
     *
     * @return bool
     */
    public static function is_cpf($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (11 != strlen($cpf) || preg_match('/^([0-9])\1+$/', $cpf)) {
            return false;
        }

        $digit = substr($cpf, 0, 9);

        for ($j = 10; $j <= 11; $j++) {
            $sum = 0;

            for ($i = 0; $i < $j - 1; $i++) {
                $sum += ($j - $i) * ((int) $digit[$i]);
            }

            $summod11 = $sum % 11;
            $digit[$j - 1] = $summod11 < 2 ? 0 : 11 - $summod11;
        }

        return $digit[9] == ((int) $cpf[9]) && $digit[10] == ((int) $cpf[10]);
    }

    /**
     * Checks if the CNPJ is valid.
     *
     * @param  string $cnpj CNPJ to validate.
     *
     * @return bool
     */
    public static function is_cnpj($cnpj = null)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Get the settings option key
     *
     * @since 1.0
     * @param Null
     * @return String
     */
    public static function get_option_key()
    {
        $settings = Setting::get_instance();

        return $settings->get_option_key();
    }

    /**
     * Format document number
     *
     * @since 1.0
     * @param String $document
     * @return String
     */
    public static function format_document($document)
    {
        return preg_replace('/[^0-9]+/', '', $document);
    }

    /**
     * Get the order id by meta value
     *
     * @since 1.0
     * @param String $meta_value
     * @return Integer
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
     * Get date formatted for SQL
     *
     * @param String $date
     * @param String $format
     * @return String
     */
    public static function convert_date_for_sql($date, $format = 'Y-m-d')
    {
        return empty($date) ? '' : self::convert_date($date, $format, '/', '-');
    }

    /**
     * Conversion of date
     *
     * @param String $date
     * @param String $format
     * @param String $search
     * @param String $replace
     * @return String
     */
    public static function convert_date($date, $format = 'Y-m-d', $search = '/', $replace = '-')
    {
        if ($search && $replace) {
            $date = str_replace($search, $replace, $date);
        }

        return date_i18n($format, strtotime($date));
    }

    public static function get_template($file, $args = array())
    {
        if ($args && is_array($args)) {
            extract($args, EXTR_SKIP);
        }

        $locale = Core::plugin_dir_path() . $file . '.php';

        if (!file_exists($locale)) {
            return;
        }

        include $locale;
    }

    public static function get_template_as_string($file, $args = array())
    {
        if ($args && is_array($args)) {
            extract($args, EXTR_SKIP);
        }

        $locale = Core::plugin_dir_path() . $file . '.php';

        if (!file_exists($locale)) {
            return;
        }

        ob_start();
        include $locale;
        return ob_get_clean();
    }

    public static function get_errors($data)
    {
        $messages = '';

        foreach ($data as $key => $errors) {
            foreach ($errors as $error) {
                $messages .= "<p><b>{$key}</b>: {$error}</p>";
            }
        }

        return $messages;
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

    public static function build_document_from_order(Order $order)
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

    public static function build_customer_phones_from_order(Order $order)
    {

        $phones    = array();
        $phone     = $order->billing_phone;
        $cellphone = $order->billing_cellphone;

        if ($phone) {
            $pieces               = self::format_phone_number($phone);
            $phones['home_phone'] = array(
                'country_code' => self::get_phone_country_code($order->billing_country),
                'area_code'    => isset($pieces[0]) ? $pieces[0] : '',
                'number'       => isset($pieces[1]) ? $pieces[1] : '',
            );

            $phones["home_phone"]['complete_phone'] =
                $phones['home_phone']['area_code'] .
                $phones['home_phone']['number'];
        }

        if ($cellphone) {
            $pieces                 = self::format_phone_number($cellphone);
            $phones['mobile_phone'] = array(
                'country_code' => self::get_phone_country_code($order->billing_country),
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

    public static function build_customer_shipping_from_wc_order(WC_Order $wc_order)
    {

        $method = $wc_order->get_shipping_method();
        $order  = new Order($wc_order->get_order_number());

        if (!$method) {
            $method = 'Não informado';
        }

        $total    = self::format_order_price($wc_order->get_total_shipping());
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
