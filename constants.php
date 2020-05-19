<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

function wc_mundipagg_define( $name, $value )
{
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

wc_mundipagg_define( 'WCMP_SLUG', 'woo-mundipagg-payments' );
wc_mundipagg_define( 'WCMP_PREFIX', 'mundipagg' );
wc_mundipagg_define( 'WCMP_VERSION', 'beta-1.0.22' );
wc_mundipagg_define( 'WCMP_ROOT_PATH', dirname( __FILE__ ) . '/' );
wc_mundipagg_define( 'WCMP_ROOT_SRC', WCMP_ROOT_PATH . 'src/' );
wc_mundipagg_define( 'WCMP_ROOT_FILE', WCMP_ROOT_PATH . WCMP_SLUG . '.php' );
wc_mundipagg_define( 'WCMP_OPTION_ACTIVATE', 'wcmp_official_activate' );
