<?php
/*
 * Plugin Name: Pagar.me module for Woocommerce
 * Version:     3.1.4
 * Author:      Pagar.me
 * Author URI:  https://pagar.me
 * License:     GPL2
 * Description: Enable Pagar.me Gateway for WooCommerce
 * Requires at least: 4.1
 * Tested up to: 6.3
 * WC requires at least: 3.9.0
 * WC tested up to: 7.9
 * Domain Path: /languages
 * Text Domain: woo-pagarme-payments
 */

if (!defined('ABSPATH') || !function_exists('add_action')) {
    exit(0);
}

require_once dirname(__FILE__) . '/constants.php';

/**
 * Renders custom Wordpress Notice on every admin pages.
 * @param string $message Message displayed on the notice.
 * @param string $path The plugin basename or the configuration page file name.
 * If string, it's used to generate the message button. Exemple: plugin-name/plugin-name.php or config-page.php
 * @param bool $isConfig If defined true, the button link points to the configuration page.
 * Otherwise, it will genetare a Install or Activate button for the missing plugin.
 * @param string $type The type of the notice. Possible options are: 'error' (default), 'warning', 'success' or 'info'.
 */
function wcmpRenderAdminNoticeHtml($message, $path = '', $isConfig = false, $type = 'error')
{
    wp_enqueue_style(
        'pagarme-notice',
        plugins_url('/pagarme-payments-for-woocommerce/assets/stylesheets/admin/notice.css'),
        array(),
        filemtime(__FILE__ . '/../assets/stylesheets/admin/notice.css')
    );
?>
    <div class="notice <?= esc_html($type); ?> is-dismissible">
        <div class="pagarme-notice">
            <div class="pagarme-notice-avatar-container">
                <img alt="Pagarme Avatar" class="pagarme-notice-avatar"
                     src="<?= plugins_url('/pagarme-payments-for-woocommerce/assets/images/pagarme-avatar.svg') ?>">
            </div>
            <div class="pagarme-notice-message-container">
                <p><strong><?= __('Pagar.me module for Woocommerce', 'woo-pagarme-payments'); ?>:</strong></p>
                <p><?= $message ?></p>
                <?php
                if (is_string($path) && $path !== '') {
                    echo wcmpAddNoticeButton($path, $isConfig);
                }
                ?>
            </div>
        </div>
    </div>
<?php
}

function wcmpAddNoticeButton($path, $isConfig)
{
    $buttonHtml = '<p><a href="%1$s" class="button button-primary">%2$s</a></p>';

    if ($isConfig) {
        $pageName = explode('.', ucwords(str_replace('-', ' ', $path)))[0];
        return sprintf(
            $buttonHtml,
            esc_url(self_admin_url($path)),
            $pageName
        );
    }

    $isInstalled = false;
    if (function_exists('get_plugins')) {
        $allPlugins  = get_plugins();
        $isInstalled = !empty($allPlugins[$path]);
    }

    $plugin = explode('/', $path)[0];
    $pluginName = ucwords(str_replace('-', ' ', $plugin));

    if ($isInstalled && current_user_can('install_plugins')) {
        return sprintf(
            $buttonHtml,
            wp_nonce_url(
                self_admin_url("plugins.php?action=activate&plugin={$path}&plugin_status=active"),
                "activate-plugin_{$path}"
            ),
            __("Activate", 'woo-pagarme-payments') . " {$pluginName}"
        );
    }

    $url = 'https://wordpress.org/plugins/' . $plugin;

    if (current_user_can('install_plugins')) {
        $url = wp_nonce_url(
            self_admin_url("update.php?action=install-plugin&plugin={$plugin}"),
            "install-plugin_{$plugin}"
        );
    }

    return sprintf(
        $buttonHtml,
        esc_url($url),
        __("Install", 'woo-pagarme-payments') . " {$pluginName}"
    );
}


function wcmpAdminNoticePhpVersion()
{
    wcmpRenderAdminNoticeHtml(
        __('Your PHP version is not supported. Required >= 7.1.', 'woo-pagarme-payments')
    );
}

if (version_compare(PHP_VERSION, '7.1', '<')) {
    wcmpLoadNotice('AdminNoticePhpVersion');
    return;
}

function wcmpAdminNoticeWoocommerce()
{
    wcmpRenderAdminNoticeHtml(
        __('Woocommerce plugin is required for Pagar.me module to work.', 'woo-pagarme-payments'),
        'woocommerce/woocommerce.php'
    );
}

function wcmpAdminNoticeExtraCheckouts()
{
    wcmpRenderAdminNoticeHtml(
        __(
            'WooCoomerce Extra Checkout Fields For Brazil plugin is required for Pagar.me module to work.',
            'woo-pagarme-payments'
        ),
        'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php'
    );
}


function wcmpAdminNoticePermalink()
{
    wcmpRenderAdminNoticeHtml(
        __(
            'Permalink structure in Wordpress Settings must be different from &ldquo;<b>Plain</b>&rdquo;. ' .
                'Please correct this setting to be able to transact with Pagar.me.',
            'woo-pagarme-payments'
        ),
        'options-permalink.php',
        true
    );
}

function wcmpAdminNoticeCheckoutFields()
{
    if (!function_exists('WC')) {
        return;
    }

    $missingFields = [];
    $requiredFields = [
        'billing_cpf',
        'billing_cnpj',
        'billing_address_1',
        'billing_number',
        'billing_address_2',
        'billing_neighborhood',
    ];
    $checkoutFields = WC()->countries->get_address_fields(WC()->countries->get_base_country());

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $checkoutFields)) {
            $missingFields[] = $field;
        }
    }

    if ((in_array('billing_cpf', $missingFields) && !in_array('billing_cnpj', $missingFields)) ||
        (in_array('billing_cnpj', $missingFields) && !in_array('billing_cpf', $missingFields))
    ) {
        array_shift($missingFields);
    }

    if (empty($missingFields)) {
        return;
    }

    $message = __('The following checkout fields are required, but were not found:', 'woo-pagarme-payments');
    $message .= '</p><ul>';

    foreach ($missingFields as $field) {
        $message .= "<li>{$field}</li>";
    }

    $message .= '</ul><p>';
    $message .= __('Please, make sure to include them for Pagar.me module to work.', 'woo-pagarme-payments');

    wcmpRenderAdminNoticeHtml($message);
}

function wcmpLoadNotice($name)
{
    add_action('admin_notices', "wcmp{$name}");
}

function wcmpLoadInstances()
{
    require_once __DIR__ . '/vendor/autoload.php';

    Woocommerce\Pagarme\Core::instance();
    (new Woocommerce\Pagarme\DB\Migration\Migrator)->execute();
    do_action('wcmp_init');
}

function wcmpPluginsLoadedCheck()
{
    $woocommerce = class_exists('WooCommerce');
    $checkoutFields = class_exists('Extra_Checkout_Fields_For_Brazil');
    add_action('in_plugin_update_message-' . WCMP_PLUGIN_BASE, function ($pluginData) {
        versionUpdateWarning(WCMP_VERSION, $pluginData['new_version']);
    });

    if (!$woocommerce) {
        wcmpLoadNotice('AdminNoticeWoocommerce');
    }

    if (!$checkoutFields) {
        wcmpLoadNotice('AdminNoticeExtraCheckouts');
    }

    if ($woocommerce) {
        wcmpLoadInstances();
    }

    if (get_option('permalink_structure') === '') {
        wcmpLoadNotice('AdminNoticePermalink');
    }

    wcmpLoadNotice('AdminNoticeCheckoutFields');
}

add_action('plugins_loaded', 'wcmpPluginsLoadedCheck', 0);

function versionUpdateWarning($currentVersion, $newVersion)
{
    $currentVersionMajorPart = explode('.', $currentVersion)[0];
    $newVersionMajorPart = explode('.', $newVersion)[0];

    if ($currentVersionMajorPart >= $newVersionMajorPart) {
        return;
    }
?>
    <hr class="pagarme-major-update-warning-separator" />
    <div class="pagarme-major-update-warning">
        <p></p>
        <div>
            <div class="pagarme-major-update-title">
                <?= __('We recommend backup before upgrading!', 'woo-pagarme-payments'); ?>
            </div>
            <div class="pagarme-major-update-message">
                <?php
                printf(
                    esc_html__(
                        'This new release contains crucial architecture and functionality updates. ' .
                            'We highly recommend you %1$sbackup your site before upgrading%2$s. ' .
                            'It is highly recommended to perform and validate the update first in the staging ' .
                            'environment before performing the update in production.',
                        'woo-pagarme-payments'
                    ),
                    '<a href="https://woocommerce.com/pt-br/posts/how-to-easily-backup-and-restore-woocommerce/">',
                    '</a>'
                );
                ?>
            </div>
        </div>
    </div>
<?php
}

function wcmpOnActivation()
{
    if (!class_exists('WooCommerce')) {
        return;
    }

    add_option(WCMP_OPTION_ACTIVATE, true);

    $upgradePath = ABSPATH . 'wp-admin/includes/upgrade.php';

    wcmpCreateCoreConfigurationTable($upgradePath);
    wcmpCreateCoreCustomerTable($upgradePath);
    wcmpCreateCoreChargeTable($upgradePath);
    wcmpCreateCoreOrderTable($upgradePath);
    wcmpCreateCoreSavedCardTable($upgradePath);
    wcmpCreateCoreTransactionTable($upgradePath);
    wcmpCreateCoreHubInstallToken($upgradePath);

    register_uninstall_hook(__FILE__, 'wcmpOnUninstall');
}

function wcmpCreateCoreConfigurationTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_configuration';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
    (
        id       int unsigned auto_increment comment 'ID' primary key,
        data     text not null comment 'data',
        store_id varchar(50)  not null comment 'Store id'
    ) comment 'Configuration Table' {$charset};";

    dbDelta($query);
}

function wcmpCreateCoreCustomerTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_customer';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
    (
        id         int unsigned auto_increment comment 'ID' primary key,
        code       varchar(100) not null comment 'platform customer id',
        pagarme_id varchar(20)  not null comment 'format: cus_xxxxxxxxxxxxxxxx'
    ) comment 'Customer Table' {$charset};";

    dbDelta($query);
}

function wcmpCreateCoreChargeTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_charge';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
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

function wcmpCreateCoreOrderTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_order';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
    (
        id           int unsigned auto_increment comment 'ID' primary key,
        pagarme_id   varchar(19)  not null comment 'format: or_xxxxxxxxxxxxxxxx',
        code         varchar(100) not null comment 'Code',
        status       varchar(30)  not null comment 'Status'
    ) comment 'Order Table' {$charset};";

    dbDelta($query);
}

function wcmpCreateCoreTransactionTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_transaction';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
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

function wcmpCreateCoreSavedCardTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_saved_card';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
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

function wcmpCreateCoreHubInstallToken($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset    = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_hub_install_token';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
    (
        id                   int unsigned auto_increment comment 'ID' primary key,
        token                varchar(255) not null comment 'hub install token',
        used                 tinyint      not null comment 'ensures token was used or not',
        created_at_timestamp int          not null comment 'Token Created timestap',
        expire_at_timestamp  int          not null comment 'Token Expiration timestamp'
    ) comment 'Hub Install Token Table' {$charset};";

    dbDelta($query);
}

function wcmpOnDeactivation()
{
    // @todo
}

function wcmpOnUninstall()
{
    // @todo
}

register_activation_hook(__FILE__, 'wcmpOnActivation');
register_deactivation_hook(__FILE__, 'wcmpOnDeactivation');
