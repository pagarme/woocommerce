<?php

/**
 * PHPUnit bootstrap file for Pagarme Payments for WooCommerce tests
 */

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define WordPress constants if not already defined
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}

// Define plugin constants needed for tests
if (!defined('WCMP_SLUG')) {
    define('WCMP_SLUG', 'woo-pagarme-payments');
}

if (!defined('WCMP_PREFIX')) {
    define('WCMP_PREFIX', 'pagarme');
}

if (!defined('WCMP_VERSION')) {
    define('WCMP_VERSION', '3.6.1');
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

// Initialize Brain Monkey for WordPress function mocking
\Brain\Monkey\setUp();
