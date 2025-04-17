<?php
/*
 * Plugin Name: Pagar.me for WooCommerce
 * Version:     3.5.1
 * Author:      Pagar.me
 * Author URI:  https://pagar.me
 * License:     GPL2
 * Description: Enable Pagar.me Gateway for WooCommerce
 * Requires at least: 4.1
 * Tested up to: 6.6.1
 * WC requires at least: 3.9.0
 * WC tested up to: 9.1.4
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Text Domain: woo-pagarme-payments
 */

use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\FeatureCompatibilization;
use Woocommerce\Pagarme\Action\CustomerFieldsActions;

const PAGARME_REQUIREMENTS_URL = 'https://docs.pagar.me/docs/requisitos-de-instala%C3%A7%C3%A3o-woocommerce';

if (!defined('ABSPATH') || !function_exists('add_action')) {
    exit(0);
}

require_once dirname(__FILE__) . '/constants.php';

/**
 * Renders custom Wordpress Notice on every admin pages.
 *
 * @param string $message Message displayed on the notice.
 * @param array $buttons Optional. An array of buttons arrays, generated with the wcmpSingleButtonArray function.
 * @param string $type Optional. The type of the notice.
 * Possible options are: `'error'` (default), `'warning'`, `'success'` or `'info'`.
 * @param bool $includeScript Optional. Message displayed on the notice.
 */
function wcmpRenderAdminNoticeHtml($message, $buttons = [], $type = 'error', $includeScript = false)
{
    wp_enqueue_style(
        'pagarme-notice-css',
        plugins_url('/pagarme-payments-for-woocommerce/assets/stylesheets/admin/notice.css'),
        array(),
        "1.0.1"
    );
    if ($includeScript) {
        $noticesL10n = array(
            'accountInfoUrl' => admin_url('/wc-api/pagarme-account-info')
        );
        wp_enqueue_script(
            'pagarme-notice-js',
            plugins_url('/pagarme-payments-for-woocommerce/assets/javascripts/admin/pagarme_notices.js'),
            array('jquery', 'wp-i18n'),
            "1.0.0"
        );
        wp_set_script_translations(
            'pagarme-notice-js',
            'woo-pagarme-payments',
            plugin_dir_path(__FILE__) . 'languages/'
        );
        wp_localize_script('pagarme-notice-js', 'pagarmeNotice', $noticesL10n);
    }
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
                if (!empty($buttons)) {
                    echo wcmpAddNoticeButton($buttons);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * @param string $label Name displayed on the button.
 * @param string $url URL of destiny on click.
 * If an empty string is provided, the link will have no 'href' attribute.
 * @param string $type Optional. The type of button.
 * Possible options are: `'primary'` (default), `'secondary'` or `''` (empty string).
 * If an empty string is provided, the button will be rendered as a simple link.
 * @param string $target Optional. Target of of the URL link.
 * Possible options are: `'_blank'`, `'_self'`, `'_parent'`, `'_top'`, any framename or `''` (empty string - default).
 * If an empty string is provided, the link will have no 'target' attribute.
 * @param string $class Optional. Additional class value(s) for custom style or script purposes.
 *
 * @return array
 */
function wcmpSingleButtonArray($label, $url, $type = 'primary', $target = '', $class = '')
{
    return array(
        'label'  => $label,
        'url'    => $url,
        'type'   => $type,
        'target' => $target,
        'class'  => $class
    );
}

/**
 * @param array $buttons Array of arrays, each containing the keys `label`, `url`, `type`, `target` and `class`.
 *
 * @return string
 */
function wcmpAddNoticeButton($buttons)
{
    $html = '<p>';
    foreach ($buttons as $button) {
        $buttonHtml = '<a%1$s%2$s class="%3$s%4$s">%5$s</a>';
        $html .= sprintf(
            $buttonHtml,
            $button['url'] !== '' ? ' href="' . esc_url($button['url']) . '"' : '',
            $button['target'] !== '' ? " target='{$button['target']}'" : '',
            $button['type'] !== '' ? "button button-{$button['type']} " : '',
            $button['class'],
            __($button['label'], 'woo-pagarme-payments')
        );
    }
    $html .= '</p>';

    return $html;
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

function wcmpIsPluginInstalled($pluginBasename)
{
    $isInstalled = false;
    if (function_exists('get_plugins')) {
        $allPlugins = get_plugins();
        $isInstalled = !empty($allPlugins[$pluginBasename]);
    }

    return $isInstalled;
}

function wcmpGetPluginButton($pluginBasename, $pluginName)
{
    $button = [];

    if (wcmpIsPluginInstalled($pluginBasename) && current_user_can('install_plugins')) {
        return array(
            wcmpSingleButtonArray(
                __("Activate", 'woo-pagarme-payments') . " {$pluginName}",
                wp_nonce_url(
                    self_admin_url("plugins.php?action=activate&plugin={$pluginBasename}&plugin_status=active"),
                    "activate-plugin_{$pluginBasename}"
                )
            )
        );
    }

    if (current_user_can('install_plugins')) {
        $plugin = explode('/', $pluginBasename)[0];
        $button = array(
            wcmpSingleButtonArray(
                __("Install", 'woo-pagarme-payments') . " {$pluginName}",
                wp_nonce_url(
                    self_admin_url("update.php?action=install-plugin&plugin={$plugin}"),
                    "install-plugin_{$plugin}"
                )
            )
        );
    }

    return $button;
}

function wcmpAdminNoticeWoocommerce()
{
    wcmpRenderAdminNoticeHtml(
        __('Woocommerce plugin is required for Pagar.me module to work.', 'woo-pagarme-payments'),
        wcmpGetPluginButton('woocommerce/woocommerce.php', 'WooCommerce')
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
        array(
            wcmpSingleButtonArray(
                'Permalink Settings',
                self_admin_url('options-permalink.php')
            )
        )
    );
}

function wcmpAdminNoticeCheckoutFields()
{
    if (!function_exists('WC')) {
        return;
    }

    WC()->session = new WC_Session_Handler;
    $billingFields = WC()->checkout->get_checkout_fields()['billing'];

    $missingFields = [];
    $requiredFields = [
        'billing_cpf',
        'billing_cnpj',
        'billing_document',
        'billing_first_name',
        'billing_last_name'
    ];

    if (!(new Config())->getAllowNoAddress()) {
        $requiredFields[] = 'billing_address_1';
        $requiredFields[] = 'billing_address_2';
        $requiredFields[] = 'billing_country';
        $requiredFields[] = 'billing_city';
        $requiredFields[] = 'billing_state';
        $requiredFields[] = 'billing_postcode';
    }

    foreach ($requiredFields as $field) {
        if (!array_key_exists($field, $billingFields)) {
            $missingFields[] = $field;
        }
    }

    if (hasAnyBillingDocument($missingFields)) {
        $missingFields = array_slice($missingFields, 2);
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
    $message .= sprintf(
        __("Please, make sure to include them for Pagar.me plugin to work. If you are customizing the "
            . "checkout, the address fields must have the 'name' attribute exactly as listed above. %sRead "
            . "documentation »%s", "woo-pagarme-payments"),
        sprintf(
            '<a href="%s" target="_blank" rel="noopener">',
            PAGARME_REQUIREMENTS_URL
        ),
        '</a>'
    );
    $message .= '</p>';

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

/**
 * @return void
 * @uses wcmpAdminNoticeWoocommerce()
 * @uses wcmpAdminNoticePermalink()
 * @uses wcmpAdminNoticeCheckoutFields()
 */
function wcmpPluginsLoadedCheck()
{
    $woocommerce = class_exists('WooCommerce');
    add_action('in_plugin_update_message-' . WCMP_PLUGIN_BASE, function ($pluginData) {
        versionUpdateWarning(WCMP_VERSION, $pluginData['new_version']);
    });

    if (!$woocommerce) {
        wcmpLoadNotice('AdminNoticeWoocommerce');
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
add_action('before_woocommerce_init', 'checkCompatibilityWithFeatures', 0);
add_action('woocommerce_blocks_loaded', 'addWoocommerceSupportedBlocks');

function hasAnyBillingDocument($missingFields)
{
    $hasCpf = in_array('billing_cpf', $missingFields);
    $hasCnpj = in_array('billing_cnpj', $missingFields);
    $hasDocument = in_array('billing_document', $missingFields);

    return ($hasCpf && (!$hasCnpj || !$hasDocument))
           || ($hasCnpj && (!$hasCpf || !$hasDocument))
           || ($hasDocument && (!$hasCpf || !$hasCnpj));
}

function checkCompatibilityWithFeatures()
{
    $compatibilization = new FeatureCompatibilization();
    $compatibilization->callCompatibilization();
}

function addWoocommerceSupportedBlocks()
{
    $compatibilization = new FeatureCompatibilization();
    $compatibilization->addSupportedBlocks();
}

function versionUpdateWarning($currentVersion, $newVersion)
{
    $currentVersionMajorPart = explode('.', $currentVersion)[0];
    $newVersionMajorPart = explode('.', $newVersion)[0];

    if ($currentVersionMajorPart >= $newVersionMajorPart) {
        return;
    }
    ?>
    <hr class="pagarme-major-update-warning-separator"/>
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

    $charset = $wpdb->get_charset_collate();
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

    $charset = $wpdb->get_charset_collate();
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

    $charset = $wpdb->get_charset_collate();
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

    $charset = $wpdb->get_charset_collate();
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

    $charset = $wpdb->get_charset_collate();
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

    $charset = $wpdb->get_charset_collate();
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

    $charset = $wpdb->get_charset_collate();
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
