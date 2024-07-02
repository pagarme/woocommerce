<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\CardInstallments;
use Woocommerce\Pagarme\Model\Config;

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

        if (defined('WC_VERSION')) {
            $version .= ' Woocommerce/' . WC_VERSION;
        }

        self::$platformVersion = $version;
    }

    protected function setLogPath()
    {
        $uploadPath = wp_upload_dir(null, false);
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

    public static function getDatabaseAccessObject()
    {
        global $wpdb;
        return $wpdb;
    }

    protected static function getPlatformHubAppPublicAppKey()
    {
        return '1e9c3c13-f8ea-4fdd-b2a0-8795b5593397';
    }

    public function _getDashboardLanguage()
    {
        return get_bloginfo('language');
    }

    public function _getStoreLanguage()
    {
        return get_bloginfo('language');
    }

    public static function reloadModuleConfigurationData()
    {
        if (WoocommerceCoreSetup::$instance !== null) {
            $oldIdConfig = self::$moduleConfig->getId() ?? null;
            WoocommerceCoreSetup::$instance->loadModuleConfigurationFromPlatform();
            self::$moduleConfig->setId($oldIdConfig);
        }
    }

    public function loadModuleConfigurationFromPlatform()
    {
        $storeConfig = new Config;
        $configData = new \stdClass();
        $configData = self::fillWithGeneralConfig($configData, $storeConfig);
        $configData = self::fillWithPagarmeKeys($configData, $storeConfig);
        $configData = self::fillWithCardConfig($configData, $storeConfig);
        $configData = self::fillWithBoletoConfig($configData, $storeConfig);
        $configData = self::fillWithBoletoCreditCardConfig($configData, $storeConfig);
        $configData = self::fillWithTwoCreditCardsConfig($configData, $storeConfig);
        $configData = self::fillWithMultiBuyerConfig($configData, $storeConfig);
        $configData = self::fillWithPixConfig($configData, $storeConfig);
        $configData = self::fillWithVoucherConfig($configData, $storeConfig);
        $configData = self::fillWithHubConfig($configData, $storeConfig);
        $configData = self::fillWithMarketplaceConfig($configData);

        // These method calls are commented for now because they are not implemented yet:
        // $configData = self::fillWithAddressConfig($configData, $storeConfig);
        // $configData = self::fillWithDebitConfig($configData, $storeConfig);
        // $configData = self::fillWithRecurrenceConfig($configData, $storeConfig);

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData(
            json_encode($configData)
        );

        self::$moduleConfig = $config;
    }

    /**
     * @override
     * @return string
     */
    public static function getInstallmentType()
    {
        $storeConfig = new Config;
        $installmentType = $storeConfig->getInstallmentType();
        if ( $installmentType == CardInstallments::INSTALLMENTS_LEGACY ) {
            return "1.0";
        }
        return "2.0";
    }

    private static function checkWebSiteExists()
    {
        return true;
    }

    private static function fillWithVoucherConfig($dataObj, $storeConfig)
    {
        $voucherConfig = new \stdClass();
        $voucherConfig->enabled = $storeConfig->getEnableVoucher();
        $voucherConfig->title = null;
        $voucherConfig->cardOperation = null;
        $dataObj->voucherCardStatementDescriptor = $storeConfig->getVoucherSoftDescriptor();
        $dataObj->cardConfigs = self::getBrandConfig($storeConfig);
        $dataObj->voucherConfig = $voucherConfig;
        $dataObj->saveVoucherCards = $storeConfig->getVoucherCardWallet();
        return $dataObj;
    }

    private static function fillWithDebitConfig($dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no debit config
    }

    private static function fillWithCardConfig($dataObj, $storeConfig)
    {
        $moneyService = new MoneyService();

        $dataObj->creditCardEnabled = $storeConfig->getEnableCreditCard();
        $dataObj->installmentsEnabled = true;
        $dataObj->cardOperation = $storeConfig->getCardOperationForCore();
        $dataObj->cardStatementDescriptor = $storeConfig->getIsCardStatementDescriptor();
        $dataObj->antifraudEnabled = (bool) $storeConfig->getAntifraudEnabled();
        $dataObj->antifraudMinAmount = intval($storeConfig->getAntifraudMinValue());
        $dataObj->saveCards = $storeConfig->getCcAllowSave();
        $dataObj->saveVoucherCards = $storeConfig->getVoucherCardWallet();
        $dataObj->installmentsDefaultConfig = $storeConfig->getIsInstallmentsDefaultConfig();

        $dataObj->antifraudMinAmount =
            $moneyService->floatToCents(
                $dataObj->antifraudMinAmount * 1
            );

        $dataObj->cardConfigs = self::getBrandConfig($storeConfig);

        return $dataObj;
    }

    private static function fillWithPixConfig($dataObj, $storeConfig)
    {
        $pixConfig = new \stdClass();
        $pixConfig->enabled = filter_var(
            $storeConfig->getEnablePix(),
            FILTER_VALIDATE_BOOLEAN
        );
        $pixConfig->expirationQrCode = $storeConfig->getPixQrcodeExpirationTime();
        $pixConfig->bankType = 'Pagar.me';
        $pixAdditionalData = $storeConfig->getPixAdditionalData();

        if (
            !empty($pixAdditionalData)
            && is_array($pixAdditionalData)
            && count(array_filter($pixAdditionalData))
            == count($pixAdditionalData)

        ) {
            $pixConfig->additionalInformation = [$pixAdditionalData];
        }

        $dataObj->pixConfig = $pixConfig;

        return $dataObj;
    }

    private static function fillWithBoletoConfig($dataObj, $storeConfig)
    {
        $dataObj->boletoEnabled = $storeConfig->getEnableBillet();
        $dataObj->boletoInstructions = $storeConfig->getBilletInstructions();
        $dataObj->boletoDueDays = $storeConfig->getBilletDeadlineDays();
        $dataObj->boletoBankCode = $storeConfig->getBilletBank();

        return $dataObj;
    }

    private static function fillWithBoletoCreditCardConfig($dataObj, $storeConfig)
    {
        $dataObj->boletoCreditCardEnabled = $storeConfig->getMultimethodsBilletCard();

        return $dataObj;
    }

    private static function fillWithTwoCreditCardsConfig($dataObj, $storeConfig)
    {
        $dataObj->twoCreditCardsEnabled = $storeConfig->getMultimethods2Card();

        return $dataObj;
    }

    private static function fillWithMultiBuyerConfig($dataObj, $storeConfig)
    {
        $dataObj->multibuyer = $storeConfig->getMulticustomers();

        return $dataObj;
    }

    private static function fillWithPagarmeKeys($dataObj, $storeConfig)
    {
        $options = [
            Configuration::KEY_SECRET => $storeConfig->getSecretKey(),
            Configuration::KEY_PUBLIC => $storeConfig->getPublicKey()
        ];

        $options = (object) $options;
        $dataObj->keys = $options;

        return $dataObj;
    }

    private static function fillWithGeneralConfig($dataObj, $storeConfig)
    {
        $dataObj->enabled = (bool)$storeConfig->getEnabled();
        $dataObj->testMode = $storeConfig->getIsSandboxMode();
        $dataObj->allowNoAddress = $storeConfig->getAllowNoAddress();
        $dataObj->sendMail = false;
        $dataObj->createOrder = false;

        if (self::$moduleConfig !== null) {
            $oldStoreId = self::$moduleConfig->getStoreId();
            if (!empty($oldStoreId)) {
                $dataObj->storeId = $oldStoreId;
            }
        }

        return $dataObj;
    }

    private static function fillWithMarketplaceConfig($configData)
    {
        global $wp_filter;
        if (
            !isset($wp_filter['pagarme_marketplace_config'])
        ) {
            return $configData;
        }
        $configSplit = new \stdClass();
        $configSplit->enabled = true;
        $configSplit->responsibilityForProcessingFees = "marketplace_sellers";
        $configSplit->responsibilityForChargebacks = "marketplace_sellers";
        $configSplit->responsibilityForReceivingSplitRemainder = "marketplace_sellers";
        $configSplit->responsibilityForReceivingExtrasAndDiscounts = "marketplace_sellers";
        $configSplit->mainRecipientId = null;

        $configSplit = apply_filters( "pagarme_marketplace_config", $configSplit);
        $configData->marketplaceConfig = $configSplit;

        return $configData;
    }

    private static function fillWithAddressConfig($dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no address configuration
    }

    private static function getBrandConfig($storeConfig)
    {
        $brands = array_merge(
            [''],
            $storeConfig->getCcFlags()
        );

        $cardConfigs = [];

        foreach ($brands as $brand) {
            $brand = strtolower($brand);
            $brandMethod = $brand;

            if ($brandMethod == '') {
                $brandMethod = 'nobrand';
            }

            $settingsByBrand = $storeConfig->getCcInstallmentsByFlag();
            $max = !empty($settingsByBrand) && array_key_exists($brand, $settingsByBrand['max_installment']) ?
                $settingsByBrand['max_installment'][$brand] : 0;

            if (!empty($max)) {
                $initial = Utils::str_to_float($settingsByBrand['interest'][$brand]);
                $incremental = Utils::str_to_float($settingsByBrand['interest_increase'][$brand]);
                $maxWithout = intval($settingsByBrand['no_interest'][$brand]);
            }

            if (empty($max)) {
                $max = $storeConfig->getCcInstallmentsMaximum();
                $initial = $storeConfig->getCcInstallmentsInterest();
                $incremental = $storeConfig->getCcInstallmentsInterestIncrease();
                $maxWithout = $storeConfig->getCcInstallmentsWithoutInterest();
            }

            $minValue = null;
            $cardConfigs[] = new CardConfig(
                true,
                CardBrand::$brandMethod(),
                (!empty($max) ? $max : 1),
                (!empty($maxWithout) ? $maxWithout : 0),
                $initial,
                $incremental,
                (!empty($minValue) ? $minValue : 0) * 100
            );
        }

        return $cardConfigs;
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

    private static function fillWithRecurrenceConfig(&$dataObj, $storeConfig)
    {
        // Not implemented on Woocommerce because there is no recurrence config
    }

    private static function fillWithHubConfig($dataObj, $storeConfig)
    {
        $dataObj->hubInstallId = $storeConfig->getHubInstallId();
        $dataObj->hubEnvironment = $storeConfig->getHubEnvironment();
        return $dataObj;
    }
}
