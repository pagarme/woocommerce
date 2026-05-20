<?php

namespace Pagarme\Core\Kernel\Abstractions;

use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;
use PagarmeCoreApiLib\Configuration as PagarmeCoreAPIConfiguration;
use ReflectionClass;

abstract class AbstractModuleCoreSetup
{
    const CONCRETE_MODULE_CORE_SETUP_CLASS = 0;

    const CONCRETE_CART_DECORATOR_CLASS = 10;
    const CONCRETE_DATABASE_DECORATOR_CLASS = 11;
    const CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS = 12;
    const CONCRETE_PLATFORM_INVOICE_DECORATOR_CLASS = 13;
    const CONCRETE_PLATFORM_CREDITMEMO_DECORATOR_CLASS = 14;
    const CONCRETE_PRODUCT_DECORATOR_CLASS = 15;
    const CONCRETE_PLATFORM_PAYMENT_METHOD_DECORATOR_CLASS = 16;

    const CONCRETE_DATA_SERVICE = 100;

    const CONCRETE_FORMAT_SERVICE = 1000;

    protected static $moduleVersion;
    protected static $platformVersion;
    protected static $logPath;
    protected static $instance;
    protected static $config;
    protected static $platformRoot;
    protected static $moduleConcreteDir;
    /**
     *
     * @var Configuration
     */
    protected static $moduleConfig;
    /**
     *
     * @var string
     */
    protected static $dashboardLanguage;
    /**
     *
     * @var string
     */
    protected static $storeLanguage;

    /**
     *
     * @return mixed
     */
    public static function getPlatformRoot()
    {
        return static::$platformRoot;
    }

    /**
     *
     * @param  mixed $platformRoot
     * @throws \Exception
     */
    public static function bootstrap($platformRoot = null)
    {
        if (static::$instance === null) {
            static::$instance = new static();
            static::$instance->setConfig();
            static::$instance->setModuleVersion();
            static::$instance->setPlatformVersion();
            static::$instance->setLogPath();
            static::$config[self::CONCRETE_MODULE_CORE_SETUP_CLASS] = static::class;

            static::$platformRoot = $platformRoot;

            static::updateModuleConfiguration();

            static::$instance->setApiBaseUrl();
        }
    }

    protected static function setApiBaseUrl()
    {
        if (static::$moduleConfig->isHubEnabled()) {
            PagarmeCoreAPIConfiguration::$BASEURI = 'https://hubapi.pagar.me/core/v1';
        }
    }

    protected static function updateModuleConfiguration()
    {
        static::loadSavedConfiguration();
        $savedConfig = static::$moduleConfig;
        static::$instance->loadModuleConfigurationFromPlatform();
        static::$moduleConfig->setStoreId(static::getCurrentStoreId());
        if (
            $savedConfig !== null &&
            ($savedConfigId = $savedConfig->getId()) !== null
        ) {
            static::$moduleConfig->setid($savedConfigId);
        }
        if (self::getDefaultConfigSaved() === null) {
            static::$moduleConfig->setStoreId(static::getDefaultStoreId());
            static::saveModuleConfig();
            static::$moduleConfig->setStoreId(static::getCurrentStoreId());
        }
        if (
            static::$moduleConfig->getStoreId() != static::getDefaultStoreId() &&
            $savedConfig === null
        ) {
            static::$moduleConfig->setParentConfiguration(self::getDefaultConfigSaved());
            static::$moduleConfig->setInheritAll(true);
            static::$moduleConfig->setId(null);
        }
        static::saveModuleConfig();
    }

    protected static function saveModuleConfig()
    {
        if (strpos(static::$instance->getPlatformVersion(), 'Wordpress') === false) {
            $configurationRepository = new ConfigurationRepository;
            $configurationRepository->save(static::$moduleConfig);
        }
    }
    protected static function loadSavedConfiguration()
    {
        $store = static::getCurrentStoreId();

        $configurationRepository = new ConfigurationRepository;

        $savedConfig = $configurationRepository->findByStore($store);
        if ($savedConfig !== null) {
            self::$moduleConfig = $savedConfig;
            self::$moduleConfig->setStoreId(static::getCurrentStoreId());

            return;
        }
    }

    /**
     * @return Configuration|null
     */
    private static function getDefaultConfigSaved()
    {
        $configurationRepository = new ConfigurationRepository;

        $defaultSavedConfiguration = $configurationRepository->findByStore(
            static::getDefaultStoreId()
        );

        while (
            $defaultSavedConfiguration !== null &&
            ($parentId = $defaultSavedConfiguration->getParentId()) !== null
        ) {
            $defaultSavedConfiguration = $configurationRepository->find($parentId);
        }

        return $defaultSavedConfiguration;
    }

    /**
     *
     * @return Configuration
     */
    public static function getModuleConfiguration()
    {
        return static::$moduleConfig;
    }

    public static function setModuleConfiguration(Configuration $moduleConfig)
    {
        static::$moduleConfig = $moduleConfig;
    }

    public static function get($configId)
    {
        self::bootstrap();

        if (!isset(static::$config[$configId])) {
            throw new Exception("Configuration $configId wasn't set!");
        }

        return static::$config[$configId];
    }

    public static function getAll()
    {
        self::bootstrap();

        return static::$config;
    }

    public static function getHubAppPublicAppKey()
    {
        $moduleCoreSetupClass = self::get(self::CONCRETE_MODULE_CORE_SETUP_CLASS);
        return $moduleCoreSetupClass::getPlatformHubAppPublicAppKey();
    }

    public static function getDatabaseAccessDecorator()
    {
        $concreteCoreSetupClass = self::get(self::CONCRETE_MODULE_CORE_SETUP_CLASS);
        $DBDecoratorClass = $concreteCoreSetupClass::get(self::CONCRETE_DATABASE_DECORATOR_CLASS);

        return new $DBDecoratorClass($concreteCoreSetupClass::getDatabaseAccessObject());
    }

    public static function getModuleVersion()
    {
        return self::$moduleVersion;
    }

    public static function getPlatformVersion()
    {
        return self::$platformVersion;
    }

   public static function getInstallmentType() 
   {
        if(method_exists(self::$instance, 'getInstallmentType')) {
            return self::$instance->getInstallmentType();
        }
        return null;
   }

    public static function getLogPath()
    {
        return self::$logPath;
    }

    public static function getDashboardLanguage()
    {
        return self::$instance->_getDashboardLanguage();
    }
    public static function getStoreLanguage()
    {
        return self::$instance->_getStoreLanguage();
    }

    public static function formatToCurrency($price)
    {
        return self::$instance->_formatToCurrency($price);
    }

    public static function getModuleConcreteDir()
    {
        if (isset(self::$moduleConcreteDir)) {
            return self::$moduleConcreteDir;
        }

        $concretePlatformCoreSetupClass = self::get(self::CONCRETE_MODULE_CORE_SETUP_CLASS);

        $moduleCoreSetupReflection = new ReflectionClass($concretePlatformCoreSetupClass);
        $concreteCoreSetupFilename = $moduleCoreSetupReflection->getFileName();
        $concreteDir = explode(DIRECTORY_SEPARATOR, $concreteCoreSetupFilename ?? '');
        array_pop($concreteDir);

        self::$moduleConcreteDir = implode(DIRECTORY_SEPARATOR, $concreteDir);

        return self::$moduleConcreteDir;
    }

    public static function setModuleConcreteDir($concreteModuleDir)
    {
        if (!isset(self::$moduleConcreteDir)) {
            self::$moduleConcreteDir = $concreteModuleDir;
        }
    }

    abstract protected function setConfig();
    abstract public function loadModuleConfigurationFromPlatform();
    abstract protected function setModuleVersion();
    abstract protected function setPlatformVersion();
    abstract protected function setLogPath();
    abstract public static function getDatabaseAccessObject();
    /**
     *
     * @return string
     **/
    abstract protected static function getPlatformHubAppPublicAppKey();
    abstract protected function _getDashboardLanguage();
    abstract protected function _getStoreLanguage();
    abstract protected function _formatToCurrency($price);

    /**
     * @since 1.6.1
     */
    abstract public static function getCurrentStoreId();

    /**
     * @since 1.6.1
     */
    abstract public static function getDefaultStoreId();

    /**
     * @since 1.7.1
     *
     * @return \DateTimeZone
     */
    public static function getStoreTimezone()
    {
        return self::$instance->getPlatformStoreTimezone();
    }

    /**
     * @since 1.7.1
     *
     * @return \DateTimeZone
     */
    abstract protected function getPlatformStoreTimezone();
}
