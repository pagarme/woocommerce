<?php

namespace Woocommerce\Pagarme\Concrete;

require_once WP_PLUGIN_DIR . '/woo-pagarme-payments/constants.php';

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Woocommerce\Pagarme\Concrete\WoocommerceDatabaseDecorator;
use Woocommerce\Pagarme\Concrete\WoocommerceDataService;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformCreditmemoDecorator;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformInvoiceDecorator;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformOrderDecorator;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformPaymentMethodDecorator;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformProductDecorator;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Enum\CreditCardBrandEnum;
use Woocommerce\Pagarme\Model\Setting;

final class WoocommerceCoreSetup extends AbstractModuleCoreSetup
{
    const MODULE_NAME = 'woo-pagarme-payments';

    protected function setModuleVersion()
    {
        self::$moduleVersion = WCMP_VERSION;
    }

    protected function setPlatformVersion()
    {
        $version = ' Wordpress/' . get_bloginfo('version');

        if ( defined( 'WC_VERSION' ) ) {
            $version .= ' Woocommerce/' . WC_VERSION;
        }

        self::$platformVersion = $version;
    }

    protected function setLogPath()
    {
        $uploadPath = wp_upload_dir( null, false );
        $wcLogsPath = $uploadPath['basedir'] . '/wc-logs/';

        self::$logPath = [
            $wcLogsPath,
            $wcLogsPath
        ];
    }

    protected function setConfig()
    {
        self::$config = [
            AbstractModuleCoreSetup::CONCRETE_DATABASE_DECORATOR_CLASS =>
            WoocommerceDatabaseDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS =>
            WoocommercePlatformOrderDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_INVOICE_DECORATOR_CLASS =>
            WoocommercePlatformInvoiceDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_CREDITMEMO_DECORATOR_CLASS =>
            WoocommercePlatformCreditmemoDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_DATA_SERVICE =>
            WoocommerceDataService::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_PAYMENT_METHOD_DECORATOR_CLASS =>
            WoocommercePlatformPaymentMethodDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PRODUCT_DECORATOR_CLASS =>
            WoocommercePlatformProductDecorator::class
        ];
    }

    static public function getDatabaseAccessObject()
    {
        global $wpdb;
        return $wpdb;
    }

    protected static function getPlatformHubAppPublicAppKey()
    {
        // Not implemented on Woocommerce because there is no hub implementation
    }

    public function _getDashboardLanguage()
    {
        return get_bloginfo('language');
    }

    public function _getStoreLanguage()
    {
        return get_bloginfo('language');
    }

    public function loadModuleConfigurationFromPlatform()
    {
        $storeConfig = Setting::get_instance();
        $configData = new \stdClass();

        self::fillWithGeneralConfig($configData, $storeConfig);
        self::fillWithPagarmeKeys($configData, $storeConfig);
        self::fillWithCardConfig($configData, $storeConfig);
        self::fillWithBoletoConfig($configData, $storeConfig);
        //		self::fillWithPixConfig($configData, $storeConfig);
        self::fillWithBoletoCreditCardConfig($configData, $storeConfig);
        self::fillWithTwoCreditCardsConfig($configData, $storeConfig);
        //		self::fillWithVoucherConfig($configData, $storeConfig);
        //		self::fillWithDebitConfig($configData, $storeConfig);
        self::fillWithAddressConfig($configData, $storeConfig);
        self::fillWithMultiBuyerConfig($configData, $storeConfig);
        //		self::fillWithRecurrenceConfig($configData, $storeConfig);

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
    }

    private static function checkWebSiteExists()
    {
        return true;
    }

    static private function fillWithVoucherConfig(&$dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no voucher config
    }

    static private function fillWithDebitConfig(&$dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no debit config
    }

    static private function fillWithCardConfig(&$dataObj, $storeConfig)
    {
        $moneyService = new MoneyService();
        $options = new \stdClass();

        $options->creditCardEnabled = $storeConfig->is_active_credit_card();
        $options->installmentsEnabled = true;
        $options->cardOperation = $storeConfig->get_operation_type();
        $options->cardStatementDescriptor = false;
        $options->antifraudEnabled = $storeConfig->__get( 'antifraud_enabled' ) === 'yes' ? true : false;
        $options->antifraudMinAmount = intval( $storeConfig->__get( 'antifraud_min_value' ) );
        $options->saveCards = $storeConfig->is_allowed_save_credit_card();
        $options->installmentsDefaultConfig = true;

        $dataObj = $options;

        $dataObj->antifraudMinAmount =
        $moneyService->floatToCents(
            $dataObj->antifraudMinAmount * 1
        );

        $dataObj->cardConfigs = self::getBrandConfig($storeConfig);

        return $dataObj;
    }

    private static function fillWithPixConfig()
    {
        // Not implemented on Woocommerce because there is no pix config
    }

    static private function fillWithBoletoConfig(&$dataObj, $storeConfig)
    {
        $options = new \stdClass();

        $options->boletoEnabled = $storeConfig->is_active_billet();
        $options->boletoInstructions = $storeConfig->__get('billet_instructions');
        $options->boletoDueDays = $storeConfig->__get('billet_deadline_days');
        $options->boletoBankCode = $storeConfig->__get('billet_bank');

        $dataObj = $options;
    }

    static private function fillWithBoletoCreditCardConfig(&$dataObj, $storeConfig)
    {
        $options = new \stdClass();

        $options->boletoCreditCardEnabled = $storeConfig->is_active_billet_and_card();

        $dataObj = $options;
    }

    static private function fillWithTwoCreditCardsConfig(&$dataObj, $storeConfig)
    {
        $options = new \stdClass();

        $options->twoCreditCardsEnabled = $storeConfig->is_active_2_cards();

        $dataObj = $options;
    }

    static private function fillWithMultiBuyerConfig(&$dataObj, $storeConfig)
    {
        $options = new \stdClass();

        $options->multibuyer = $storeConfig->is_active_multicustomers();

        $dataObj = $options;
    }

    static private function fillWithPagarmeKeys(&$dataObj, $storeConfig)
    {
        $options = [
            Configuration::KEY_SECRET => $storeConfig->__get( 'production_secret_key' ),
            Configuration::KEY_PUBLIC => $storeConfig->__get( 'production_public_key' )
        ];

        if ($dataObj->testMode) {
            $options[Configuration::KEY_SECRET] .= $storeConfig->__get( 'sandbox_secret_key' );
            $options[Configuration::KEY_PUBLIC] .= $storeConfig->__get( 'sandbox_public_key' );
        }

        $options = (object) $options;

        $dataObj->keys = $options;
    }

    static private function fillWithGeneralConfig(&$dataObj, $storeConfig)
    {
        $options = new \stdClass();

        $options->enabled = $storeConfig->is_enabled();
        $options->testMode = $storeConfig->is_sandbox();
        $options->sendMail = false;
        $options->createOrder = false;

        $dataObj = $options;
    }

    static private function fillWithAddressConfig(&$dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no address config
    }

    static private function getBrandConfig($storeConfig)
    {
        $brands = array_merge(
            [''],
            $storeConfig->get_flags_list()
        );

        $cardConfigs = [];

        foreach ($brands as $brand) {
            $brand = "_" . strtolower($brand);
            $brandMethod = str_replace('_', '', $brand);
            $adapted = self::getBrandAdapter(strtoupper($brandMethod));

            if ($adapted !== false) {
                $brand = "_" . strtolower($adapted);
                $brandMethod = str_replace('_','', $brand);
            }

            if ($brandMethod == '') {
                $brandMethod = 'nobrand';
            }

            $max = $storeConfig->__get( 'cc_installments_maximum' );

            if (empty($max)) {
                $max = $storeConfig->__get( 'cc_installments_maximum' );
            }

            $minValue = null;
            $initial =  $storeConfig->__get( 'cc_installments_interest' );
            $incremental = $storeConfig->__get( 'cc_installments_interest_increase' );
            $maxWithout =  $storeConfig->__get( 'cc_installments_without_interest' );

            $cardConfigs[] = new CardConfig(
                true,
                CardBrand::$brandMethod(),
                ($max !== null ? $max : 1),
                ($maxWithout !== null ? $maxWithout : 1),
                $initial,
                $incremental,
                ($minValue !== null ? $minValue : 0) * 100
            );
        }

        return $cardConfigs;
    }

    private static function getBrandAdapter($brand)
    {
        $fromTo = [
            'VI' => CreditCardBrandEnum::VISA,
            'MC' => CreditCardBrandEnum::MASTERCARD,
            'AE' => CreditCardBrandEnum::AMEX,
            'DI' => CreditCardBrandEnum::DISCOVER,
            'DN' => CreditCardBrandEnum::DINERS,
        ];

        return (isset($fromTo[$brand])) ? $fromTo[$brand] : false;
    }

    protected function _formatToCurrency($price)
    {
        return Utils::format_order_price_with_currency_symbol($price);
    }

    public static function getCurrentStoreId()
    {
        return 1;
    }

    public static function getDefaultStoreId()
    {
        return 1;
    }

    protected function getPlatformStoreTimezone()
    {
        return wp_timezone_string();
    }

    static private function fillWithRecurrenceConfig(&$dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no recurrence config
    }
}
