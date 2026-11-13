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
wc_pagarme_define('WCMP_VERSION', '3.10.2');
wc_pagarme_define('WCMP_ROOT_PATH', dirname(__FILE__) . '/');
wc_pagarme_define('WCMP_ROOT_SRC', WCMP_ROOT_PATH . 'src/');
wc_pagarme_define('WCMP_ROOT_FILE', WCMP_ROOT_PATH . WCMP_SLUG . '.php');
wc_pagarme_define('WCMP_OPTION_ACTIVATE', 'wcmp_official_activate');
wc_pagarme_define('WCMP__FILE__', __FILE__ );
wc_pagarme_define('WCMP_PLUGIN_BASE', plugin_basename( WCMP_ROOT_FILE ) );
wc_pagarme_define('WCMP_JS_HANDLER_BASE_NAME', 'pagarme_scripts_' );

// Defaults de produção dos endpoints. Podem ser sobrescritos por ENV (PAGARME_API_URL,
// PAGARME_HUB_API_URL, PAGARME_CDN_URL) ou constante no wp-config.php. Ver Model\ApiEndpoints.
wc_pagarme_define('WCMP_DEFAULT_API_URL', 'https://api.pagar.me');
wc_pagarme_define('WCMP_DEFAULT_HUB_API_URL', 'https://hubapi.pagar.me');
wc_pagarme_define('WCMP_DEFAULT_CDN_URL', 'https://cdn.mundipagg.com');
