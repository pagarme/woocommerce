<?php
/*
 * Plugin Name: Pagar.me module for Woocommerce
 * Version:     2.0.14
 * Author:      Pagar.me
 * Author URI:  https://pagar.me
 * Text Domain: woo-pagarme-payments
 * Domain Path: /languages
 * License:     GPL2
 * Description: Enable Pagar.me Gateway for WooCommerce
 * WC requires at least: 3.9.0
 * WC tested up to: 5.4.0
 */

if (!function_exists('add_action')) {
    exit(0);
}

require_once dirname(__FILE__) . '/constants.php';

function wcmp_render_admin_notice_html($message, $type = 'error')
{
?>
    <div class="<?php echo esc_html($type); ?> notice is-dismissible">
        <p>
            <strong><?php esc_html_e('Pagar.me module for Woocommerce', 'woo-pagarme-payments'); ?>: </strong>

            <?php echo /*phpcs:ignore*/ esc_attr($message); ?>
        </p>
    </div>
<?php
}

if (version_compare(PHP_VERSION, '7.1', '<')) {

    function wcmp_admin_notice_php_version()
    {
        wcmp_render_admin_notice_html(
            __('Your PHP version is not supported. Required >= 7.1.', 'woo-pagarme-payments')
        );
    }

    _wcmp_load_notice('admin_notice_php_version');
    return;
}

function wcmp_admin_notice_error()
{
    wcmp_render_admin_notice_html(
        __('WooCoomerce plugin is required.', 'woo-pagarme-payments')
    );
}

function wcmp_admin_notice_error_wecffb()
{
    wcmp_render_admin_notice_html(
        __(
            'WooCoomerce Extra Checkout Fields For Brazil plugin is required.',
            'woo-pagarme-payments'
        )
    );
}

function _wcmp_load_notice($name)
{
    add_action('admin_notices', "wcmp_{$name}");
}

function _wcmp_load_instances()
{
    require_once 'vendor/autoload.php';

    Woocommerce\Pagarme\Core::instance();
    (new Woocommerce\Pagarme\DB\Migration\Migrator)->execute();
    do_action('wcmp_init');
}

function wcmp_plugins_loaded_check()
{
    $woocommerce     = class_exists('WooCommerce');
    $checkout_fields = class_exists('Extra_Checkout_Fields_For_Brazil');

    if ($woocommerce && $checkout_fields) {
        _wcmp_load_instances();
        return;
    }

    if (!$woocommerce) {
        _wcmp_load_notice('admin_notice_error');
    }

    if (!$checkout_fields) {
        _wcmp_load_notice('admin_notice_error_wecffb');
    }

}

add_action('plugins_loaded', 'wcmp_plugins_loaded_check', 0);

function wcmp_on_activation()
{
    if (!class_exists('WooCommerce')) {
        return;
    }

    add_option(WCMP_OPTION_ACTIVATE, true);

    wcmp_create_core_configuration_table();
    wcmp_create_core_customer_table();
    wcmp_create_core_charge_table();
    wcmp_create_core_order_table();
    wcmp_create_core_saved_card_table();
    wcmp_create_core_transaction_table();
    wcmp_create_core_hub_install_token();

    register_uninstall_hook(__FILE__, 'wcmp_on_uninstall');
}

function wcmp_create_core_configuration_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_configuration';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id       int unsigned auto_increment comment 'ID' primary key,
        data     text not null comment 'data',
        store_id varchar(50)  not null comment 'Store id'
    ) comment 'Configuration Table' {$charset};";

    dbDelta($query);
}

function wcmp_create_core_customer_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_customer';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id         int unsigned auto_increment comment 'ID' primary key,
        code       varchar(100) not null comment 'platform customer id',
        pagarme_id varchar(20)  not null comment 'format: cus_xxxxxxxxxxxxxxxx'
    ) comment 'Customer Table' {$charset};";

    dbDelta($query);
}

function wcmp_create_core_charge_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_charge';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id              int unsigned auto_increment comment 'ID' primary key,
        pagarme_id      varchar(19)  not null comment 'format: ch_xxxxxxxxxxxxxxxx',
        order_id        varchar(19)  not null comment 'format: or_xxxxxxxxxxxxxxxx',
        code            varchar(100) not null comment 'Code',
        amount          int unsigned not null comment 'amount',
        paid_amount     int unsigned not null comment 'Paid Amount',
        canceled_amount int unsigned not null comment 'Canceled Amount',
        refunded_amount int unsigned not null comment 'Refunded Amount',
        status          varchar(30)  not null comment 'Status',
        metadata        text         null comment 'Charge metadata',
        customer_id     varchar(50)  null comment 'Charge customer id'
    ) comment 'Charge Table' {$charset};";

    dbDelta($query);
}

function wcmp_create_core_order_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_order';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id           int unsigned auto_increment comment 'ID' primary key,
        pagarme_id   varchar(19)  not null comment 'format: or_xxxxxxxxxxxxxxxx',
        code         varchar(100) not null comment 'Code',
        status       varchar(30)  not null comment 'Status'
    ) comment 'Order Table' {$charset};";

    dbDelta($query);
}

function wcmp_create_core_transaction_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_transaction';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id                 int unsigned auto_increment comment 'ID' primary key,
        pagarme_id         varchar(21)  not null comment 'format: tran_xxxxxxxxxxxxxxxx',
        charge_id          varchar(19)  not null comment 'format: ch_xxxxxxxxxxxxxxxx',
        amount             int unsigned not null comment 'amount',
        paid_amount        int unsigned not null comment 'paid amount',
        acquirer_tid       text         null,
        acquirer_nsu       text         null,
        acquirer_auth_code text         null,
        acquirer_name      text         not null comment 'Type',
        acquirer_message   text         not null comment 'Type',
        type               varchar(30)  not null comment 'Type',
        status             varchar(30)  not null comment 'Status',
        created_at         datetime     not null comment 'Created At',
        boleto_url         text         null comment 'Boleto url',
        card_data          text         null comment 'Card data',
        transaction_data   text         null comment 'Transaction Data'
    ) comment 'Transaction Table' {$charset};";

    dbDelta($query);
}

function wcmp_create_core_saved_card_table()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_saved_card';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id               int unsigned auto_increment comment 'ID' primary key,
        pagarme_id       varchar(21) not null comment 'format: card_xxxxxxxxxxxxxxxx',
        owner_id         varchar(21) not null comment 'format: cus_xxxxxxxxxxxxxxxx',
        first_six_digits varchar(6)  not null comment 'card first six digits',
        last_four_digits varchar(4)  not null comment 'card last four digits',
        brand            varchar(30) not null comment 'card brand',
        owner_name       varchar(50) null comment 'Card owner name',
        created_at       datetime    not null comment 'Card createdAt'
    ) comment 'Saved Card Table' {$charset};";

    dbDelta($query);
}

function wcmp_create_core_hub_install_token()
{
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset    = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'pagarme_module_core_hub_install_token';

    $query = "CREATE TABLE IF NOT EXISTS {$table_name}
    (
        id                   int unsigned auto_increment comment 'ID' primary key,
        token                varchar(255) not null comment 'hub install token',
        used                 tinyint      not null comment 'ensures token was used or not',
        created_at_timestamp int          not null comment 'Token Created timestap',
        expire_at_timestamp  int          not null comment 'Token Expiration timestamp'
    ) comment 'Hub Install Token Table' {$charset};";

    dbDelta($query);
}

function wcmp_on_deactivation()
{
}

function wcmp_on_uninstall()
{
}

register_activation_hook(__FILE__, 'wcmp_on_activation');
register_deactivation_hook(__FILE__, 'wcmp_on_deactivation');
