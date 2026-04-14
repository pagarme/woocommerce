<?php

/**
 * PHPUnit bootstrap file for Pagarme Payments for WooCommerce tests
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('WCMP_SLUG')) {
    define('WCMP_SLUG', 'woo-pagarme-payments');
}

if (!defined('WCMP_PREFIX')) {
    define('WCMP_PREFIX', 'pagarme');
}

if (!defined('WCMP_VERSION')) {
    define('WCMP_VERSION', '3.8.0-rc');
}

if (!defined('WCMP_ROOT_PATH')) {
    define('WCMP_ROOT_PATH', dirname(__DIR__) . '/');
}

if (!defined('WCMP_ROOT_SRC')) {
    define('WCMP_ROOT_SRC', WCMP_ROOT_PATH . 'src/');
}

if (!defined('WCMP_ROOT_FILE')) {
    define('WCMP_ROOT_FILE', WCMP_ROOT_PATH . WCMP_SLUG . '.php');
}

if (!defined('WCMP_OPTION_ACTIVATE')) {
    define('WCMP_OPTION_ACTIVATE', 'wcmp_official_activate');
}

if (!defined('WCMP__FILE__')) {
    define('WCMP__FILE__', dirname(__DIR__) . '/constants.php');
}

if (!defined('WCMP_PLUGIN_BASE')) {
    define('WCMP_PLUGIN_BASE', 'woo-pagarme-payments/woo-pagarme-payments.php');
}

if (!defined('WCMP_JS_HANDLER_BASE_NAME')) {
    define('WCMP_JS_HANDLER_BASE_NAME', 'pagarme_scripts_');
}

if (!class_exists('WC_Logger')) {
    /**
     * Mock WC_Logger class for testing
     */
    class WC_Logger
    {
        public function add($handle, $message, $level = 'info')
        {
            return true;
        }

        public function log($level, $message, $context = [])
        {
            return true;
        }

        public function emergency($message, $context = [])
        {
            return $this->log('emergency', $message, $context);
        }

        public function alert($message, $context = [])
        {
            return $this->log('alert', $message, $context);
        }

        public function critical($message, $context = [])
        {
            return $this->log('critical', $message, $context);
        }

        public function error($message, $context = [])
        {
            return $this->log('error', $message, $context);
        }

        public function warning($message, $context = [])
        {
            return $this->log('warning', $message, $context);
        }

        public function notice($message, $context = [])
        {
            return $this->log('notice', $message, $context);
        }

        public function info($message, $context = [])
        {
            return $this->log('info', $message, $context);
        }

        public function debug($message, $context = [])
        {
            return $this->log('debug', $message, $context);
        }
    }
}
