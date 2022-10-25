<?php
if (!defined('ABSPATH')) {
    exit(0);
}

function wc_pagarme_define($name, $value)
{
    if (!defined($name)) {
        define($name, $value);
    }
}

wc_pagarme_define('WCMP_SLUG', 'woo-pagarme-payments');
wc_pagarme_define('WCMP_PREFIX', 'pagarme');
wc_pagarme_define('WCMP_VERSION', '2.0.14');
wc_pagarme_define('WCMP_ROOT_PATH', dirname(__FILE__) . '/');
wc_pagarme_define('WCMP_ROOT_SRC', WCMP_ROOT_PATH . 'src/');
wc_pagarme_define('WCMP_ROOT_FILE', WCMP_ROOT_PATH . WCMP_SLUG . '.php');
wc_pagarme_define('WCMP_OPTION_ACTIVATE', 'wcmp_official_activate');
