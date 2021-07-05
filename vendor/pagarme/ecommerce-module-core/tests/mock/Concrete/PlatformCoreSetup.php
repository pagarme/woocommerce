<?php

namespace Pagarme\Core\Test\Mock\Concrete;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use PDO;
use PDOException;

class PlatformCoreSetup extends AbstractModuleCoreSetup
{

    protected function setConfig()
    {
        self::$config = [
            AbstractModuleCoreSetup::CONCRETE_DATABASE_DECORATOR_CLASS =>
                PlatformDatabaseDecorator::class,
        ];
    }

    public function loadModuleConfigurationFromPlatform()
    {
        $configData = $this->getConfigMock();

        $configurationFactory = new ConfigurationFactory();
        $config = $configurationFactory->createFromJsonData($configData);

        self::$moduleConfig = $config;
    }

    protected function setModuleVersion()
    {
        return "1.0.0";
    }

    protected function setPlatformVersion()
    {
        return "1.0.0";
    }

    protected function setLogPath()
    {
        return "/tmp/logs";
    }

    public static function getDatabaseAccessObject()
    {
        $memory_db = new PDO('sqlite:./tests/mock/db_test.sqlite');
        $memory_db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        try {
            $migrate = new Migrate($memory_db);
            $migrate->setUpConfiguration();
            $migrate->up();
        }
        catch (PDOException $e)
        {
            echo "Db Prepare Error: " . $e->getMessage();
            die();
        }

        return $memory_db;
    }

    /**
     *
     * @return string
     **/
    protected static function getPlatformHubAppPublicAppKey()
    {
        // TODO: Implement getPlatformHubAppPublicAppKey() method.
    }

    protected function _getDashboardLanguage()
    {
        // TODO: Implement _getDashboardLanguage() method.
    }

    protected function _getStoreLanguage()
    {
        // TODO: Implement _getStoreLanguage() method.
    }

    protected function _formatToCurrency($price)
    {
        // TODO: Implement _formatToCurrency() method.
    }

    /**
     * @since 1.6.1
     */
    public static function getCurrentStoreId()
    {
        return 1;
    }

    /**
     * @since 1.6.1
     */
    public static function getDefaultStoreId()
    {
        return 1;
    }

    /**
     * @return \DateTimeZone
     * @since 1.7.1
     *
     */
    protected function getPlatformStoreTimezone()
    {
        // TODO: Implement getPlatformStoreTimezone() method.
    }

    public function getConfigMock()
    {
        $config = [
            'enabled' => true,
            'antifraudEnabled' => false,
            'antifraudMinAmount' => '0',
            'boletoEnabled' => true,
            'creditCardEnabled' => true,
            'saveCards' => false,
            'multibuyer' => true,
            'twoCreditCardsEnabled' => true,
            'boletoCreditCardEnabled' => true,
            'testMode' => true,
            'hubInstallId' => NULL,
            'addressAttributes' => [
                    'street' => 'street_1',
                    'number' => 'street_2',
                    'neighborhood' => 'street_4',
                    'complement' => 'street_3',
            ],
            'keys' => [
                    'KEY_SECRET' => 'sk_test_0004RBxs0RhQP4qZ',
                    'KEY_PUBLIC' => 'pk_test_0006gbVi8iEgb4oB',
            ],
            'cardOperation' => 'auth_and_capture',
            'installmentsEnabled' => true,
            'installmentsDefaultConfig' => true,
            'cardStatementDescriptor' => 'Loja Magento 2 stg',
            'boletoInstructions' => 'Pagar ate o vencimento',
            'boletoDueDays' => 5,
            'boletoBankCode' => '002',
            'cardConfigs' => [
                [
                    'enabled' => true,
                    'brand' => 'noBrand',
                    'incrementalInterest' => 1,
                    'initialInterest' => 10,
                    'maxInstallment' => 12,
                    'maxInstallmentWithoutInterest' => 6,
                    'minValue' => 1000,
                ],
                [
                    'enabled' => true,
                    'brand' => 'Visa',
                    'incrementalInterest' => 1,
                    'initialInterest' => 10,
                    'maxInstallment' => 12,
                    'maxInstallmentWithoutInterest' => 6,
                    'minValue' => 1000,
                ],
                [
                    'enabled' => true,
                    'brand' => 'Mastercard',
                    'incrementalInterest' => 1,
                    'initialInterest' => 10,
                    'maxInstallment' => 12,
                    'maxInstallmentWithoutInterest' => 6,
                    'minValue' => 1000,
                ]
            ],
            'storeId' => '1',
            'methodsInherited' =>[],
            'parentId' => NULL,
            'parent' => NULL,
            'inheritAll' => false,
            'recurrenceConfig' => [
                'enabled' => false,
                'planSubscription' => false,
                'singleSubscription' => false,
                'paymentUpdateCustomer' => false,
                'creditCardUpdateCustomer' => false,
                'subscriptionInstallment' => false,
                'checkoutConflictMessage' => false,
                'showRecurrenceCurrencyWidget' => false,
            ]
        ];

        return json_encode($config);
    }
}