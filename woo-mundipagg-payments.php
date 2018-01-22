<?php
/*
 * Plugin Name: WooCommerce MundiPagg Payments
 * Version:     beta-1.0.2
 * Author:      Mundipagg
 * Author URI:  https://mundipagg.com
 * Text Domain: woo-mundipagg-payments
 * Domain Path: /languages
 * License:     GPL2
 * Description: Enable MundiPagg Gateway for WooCommerce
 */

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

require_once dirname( __FILE__ ) . '/constants.php';

function wcmp_render_admin_notice_html( $message, $type = 'error' ) {
?>
	<div class="<?php echo $type; ?> notice is-dismissible">
		<p>
			<strong><?php _e( 'WooCommerce MundiPagg Payments', WCMP_TEXTDOMAIN ); ?>: </strong>

			<?php echo $message; ?>
		</p>
	</div>
<?php
}

if ( version_compare( PHP_VERSION, '5.5', '<' ) ) {

	function wcmp_admin_notice_php_version() {
		wcmp_render_admin_notice_html(
			__( 'Your PHP version is not supported. Required >= 5.5.', WCMP_TEXTDOMAIN )
		);
	}

	_wcmp_load_notice( 'admin_notice_php_version' );
	return;
}

function wcmp_admin_notice_error() {
	wcmp_render_admin_notice_html(
		__( 'WooCoomerce plugin is required.', WCMP_TEXTDOMAIN )
	);
}

function wcmp_admin_notice_error_wecffb() {
	wcmp_render_admin_notice_html(
		__( 'WooCoomerce Extra Checkout Fields For Brazil plugin is required.',WCMP_TEXTDOMAIN
		)
	);
}

function _wcmp_load_notice( $name ) {
	add_action( 'admin_notices', "wcmp_{$name}" );
}

function _wcmp_load_instances() {
	require_once( 'vendor/autoload.php' );

	Woocommerce\Mundipagg\Core::instance();

	do_action( 'wcmp_init' );
}

function wcmp_plugins_loaded_check() {
	$woocommerce     = class_exists( 'WooCommerce' );
	$checkout_fields = class_exists( 'Extra_Checkout_Fields_For_Brazil' );

	if ( $woocommerce && $checkout_fields ) {
		_wcmp_load_instances();
		return;
	}

	if ( ! $woocommerce ) {
		_wcmp_load_notice( 'admin_notice_error' );
	}

	if ( ! $checkout_fields ) {
		_wcmp_load_notice( 'admin_notice_error_wecffb' );
	}
}

add_action( 'plugins_loaded', 'wcmp_plugins_loaded_check', 0 );

function wcmp_on_activation() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	add_option( WCMP_OPTION_ACTIVATE, true );

	wcmp_create_charges_table();

	register_uninstall_hook( __FILE__, 'wcmp_on_uninstall' );
}

function wcmp_create_charges_table()
{
	global $wpdb;

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$charset    = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'woocommerce_mundipagg_charges';
	$query      = "
		CREATE TABLE IF NOT EXISTS {$table_name} (
			id         		BIGINT(20)   UNSIGNED NOT NULL AUTO_INCREMENT,
			wc_order_id    	BIGINT(20)   NOT NULL DEFAULT 0,
			order_id    	TEXT   		 NOT NULL,
			charge_id  		TEXT   		 NOT NULL,
			charge_data  	LONGTEXT 	 NOT NULL,
			charge_status   VARCHAR(20)  NOT NULL,
			updated_at 		TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
			primary key (id)
		) {$charset};
	";

	dbDelta( $query );
}

function wcmp_on_deactivation() {

}

function wcmp_on_uninstall() {

}

register_activation_hook( __FILE__, 'wcmp_on_activation' );
register_deactivation_hook( __FILE__, 'wcmp_on_deactivation' );
