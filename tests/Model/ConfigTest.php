<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\Id\InstallId;
use Pagarme\Core\Middle\Model\Account\PaymentEnum;
use WC_Logger;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\CardInstallments;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\PagarmeCoreConfigManagement;
use Woocommerce\Pagarme\Model\Config\Source\EnvironmentsTypes;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        Brain\Monkey\tearDown();
    }
    public function testConstructorWithDefaultParametersShouldInitializeCorrectly()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->andReturn('pagarme_settings');

        $config = new Config();

        $reflectionClass = new ReflectionClass($config);
        $property = $reflectionClass->getProperty('pagarmeCoreConfigManagement');
        $property->setAccessible(true);
        $value = $property->getValue($config);

        $this->assertInstanceOf(PagarmeCoreConfigManagement::class, $value);
    }

    public function testConstructorWithDataArrayShouldSetDataCorrectly()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->andReturn('pagarme_settings');

        $data = [
            'test_key' => 'test_value',
            'another_key' => 'another_value'
        ];

        $config = new Config(null, null, $data);

        $this->assertEquals('test_value', $config->getData('test_key'));
        $this->assertEquals('another_value', $config->getData('another_key'));
    }

    public function testInitWithValidOptionsShouldLoadDataFromWordPress()
    {
        $optionData = [
            'enable_pix' => 'yes',
            'enable_billet' => 'no',
        ];

        Brain\Monkey\Functions\expect('get_option')
            ->atLeast()->once()
            ->andReturn($optionData);

        Brain\Monkey\Functions\expect('add_action')
            ->once();

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        $config = new Config();

        $this->assertEquals('yes', $config->getData('enable_pix'));
        $this->assertEquals('no', $config->getData('enable_billet'));
    }

    public function testInitWithNoOptionsShouldNotLoadData()
    {
        Brain\Monkey\Functions\expect('get_option')
            ->atLeast()->once()
            ->andReturn(false);

        Brain\Monkey\Functions\expect('add_action')
            ->never();

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        $config = new Config();

        $this->assertEmpty($config->getData());
    }
    public function testSaveWithoutParameterShouldSaveCurrentInstance()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        Brain\Monkey\Functions\expect('update_option')
            ->once()
            ->with('pagarme_settings', Mockery::any())
            ->andReturn(true);

        $configManagementMock = Mockery::mock(PagarmeCoreConfigManagement::class);
        $configManagementMock->shouldReceive('update')
            ->once()
            ->with(Mockery::type(Config::class));

        $config = new Config($configManagementMock);
        $config->setData('test_key', 'test_value');
        $config->save();

        $this->assertEquals('test_value', $config->getData('test_key'));
        $this->addToAssertionCount(1);
    }

    public function testSaveWithConfigParameterShouldSaveProvidedConfig()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        $newConfigData = ['test_key' => 'test_value'];

        Brain\Monkey\Functions\expect('update_option')
            ->once()
            ->with('pagarme_settings', $newConfigData)
            ->andReturn(true);

        $configManagementMock = Mockery::mock(PagarmeCoreConfigManagement::class);
        $configManagementMock->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($arg) use ($newConfigData) {
                return $arg instanceof Config
                    && $arg->getData('test_key') === $newConfigData['test_key'];
            }));

        $config = new Config($configManagementMock);
        $newConfig = new Config(null, null, $newConfigData);

        $config->save($newConfig);

        $this->assertEquals('test_value', $newConfig->getData('test_key'));
        $this->addToAssertionCount(1);
    }

    public function testUpdateOptionWithValidPostDataShouldUpdateConfiguration()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        $_POST['pagarme_settings'] = [
            'enable_pix' => 'yes',
            'enable_billet' => 'no'
        ];

        Brain\Monkey\Functions\expect('sanitize_text_field')
            ->twice()
            ->andReturnUsing(function ($value) {
                return $value;
            });

        Brain\Monkey\Functions\expect('update_option')
            ->once();

        $configManagementMock = Mockery::mock(PagarmeCoreConfigManagement::class);
        $configManagementMock->shouldReceive('update')->once();

        $config = new Config($configManagementMock);
        $config->updateOption();

        $this->assertEquals('yes', $config->getData('enable_pix'));
        $this->assertEquals('no', $config->getData('enable_billet'));

        unset($_POST['pagarme_settings']);
    }

    public function testUpdateOptionWithoutPostDataShouldNotUpdate()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        Brain\Monkey\Functions\expect('update_option')
            ->never();

        $configManagementMock = Mockery::mock(PagarmeCoreConfigManagement::class);
        $configManagementMock->shouldReceive('update')->never();

        $originalPost = $_POST;
        $_POST = [];

        $config = new Config($configManagementMock);
        $config->updateOption();

        $_POST = $originalPost;

        $this->addToAssertionCount(1);
    }

    public function testGetIsSandboxModeWithSandboxEnvironmentShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('hub_environment', Config::HUB_SANDBOX_ENVIRONMENT);

        $result = $config->getIsSandboxMode();

        $this->assertTrue($result);
    }

    public function testGetIsSandboxModeWithTestSecretKeyShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_secret_key', 'sk_test_abc123xyz');
        $config->setData('hub_environment', 'Production');

        $result = $config->getIsSandboxMode();

        $this->assertTrue($result);
    }

    public function testGetIsSandboxModeWithTestPublicKeyShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_public_key', 'pk_test_abc123xyz');
        $config->setData('hub_environment', 'Production');

        $result = $config->getIsSandboxMode();

        $this->assertTrue($result);
    }

    public function testGetIsSandboxModeWithProductionKeysShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_secret_key', 'sk_abc123xyz');
        $config->setData('production_public_key', 'pk_abc123xyz');
        $config->setData('hub_environment', 'Production');

        $result = $config->getIsSandboxMode();

        $this->assertFalse($result);
    }

    public function testGetHubUrlWithoutInstallIdShouldReturnIntegrateUrl()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreSetupMock = Mockery::mock('alias:' . CoreSetup::class);
        $coreSetupMock->shouldReceive('getHubAppPublicAppKey')
            ->andReturn('test_app_id');

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');
        $coreMock->shouldReceive('getHubUrl')
            ->andReturn('https://test.site/hub');

        $installIdMock = Mockery::mock(InstallId::class);
        $installIdMock->shouldReceive('getValue')
            ->andReturn('install_token_123');

        $hubServiceMock = Mockery::mock('overload:' . HubIntegrationService::class);
        $hubServiceMock->shouldReceive('startHubIntegration')
            ->andReturn($installIdMock);

        $config = new Config();
        $config->setData('hub_install_id', null);

        $result = $config->getHubUrl();

        $this->assertStringContainsString('https://hub.pagar.me/apps/test_app_id/authorize', $result);
        $this->assertStringContainsString('install_token=install_token_123', $result);
    }

    public function testGetHubUrlWithInstallIdShouldReturnViewIntegrationUrl()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $coreSetupMock = Mockery::mock('alias:' . CoreSetup::class);
        $coreSetupMock->shouldReceive('getHubAppPublicAppKey')
            ->andReturn('test_app_id');

        $config = new Config();
        $config->setData('hub_install_id', 'install_123');

        $result = $config->getHubUrl();

        $this->assertEquals('https://hub.pagar.me/apps/test_app_id/edit/install_123', $result);
    }

    public function testIsDashConfigAccessibleWithPaymentProfileIdShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData(Config::PAYMENT_PROFILE_ID, 'pp_123');

        $result = $config->isPagarmeDashConfigAccessible();

        $this->assertFalse($result);
    }

    public function testIsDashConfigAccessibleWithMerchantAndAccountIdShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('merchant_id', 'merch_123');
        $config->setData(Config::ACCOUNT_ID, 'acc_123');

        $result = $config->isPagarmeDashConfigAccessible();

        $this->assertTrue($result);
    }

    public function testIsDashConfigAccessibleWithoutRequiredDataShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->isPagarmeDashConfigAccessible();

        $this->assertFalse($result);
    }

    public function testGetDashUrlWithValidConfigShouldReturnFormattedUrl()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
            'esc_url'    => static fn($url) => $url,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('merchant_id', 'merch_123');
        $config->setData(Config::ACCOUNT_ID, 'acc_456');

        $result = $config->getDashboardUrl();

        $this->assertEquals('https://dash.pagar.me/merch_123/acc_456/', $result);
    }

    public function testGetDashUrlWithoutAccessShouldReturnNull()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
            'esc_url'    => static fn($url) => $url,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->getDashboardUrl();

        $this->assertNull($result);
    }

    public function testGetDashUrlWithPaymentProfileIdShouldReturnStoneDashUrl()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
            'esc_url'    => static fn($url) => $url,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('merchant_id', 'merch_123');
        $config->setData(Config::ACCOUNT_ID, 'acc_456');
        $config->setData(Config::PAYMENT_PROFILE_ID, 'pp_789');

        $result = $config->getDashboardUrl();

        $this->assertEquals('https://conta.stone.com.br/pp_789/', $result);
    }

    public function testGetStoneDashUrlWithPaymentProfileIdShouldReturnFormattedUrl()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
            'esc_url'    => static fn($url) => $url,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData(Config::PAYMENT_PROFILE_ID, 'pp_123');

        $result = $config->getStoneDashUrl();

        $this->assertEquals('https://conta.stone.com.br/pp_123/', $result);
    }

    public function testGetStoneDashUrlWithoutPaymentProfileIdShouldReturnNull()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
            'esc_url'    => static fn($url) => $url,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->getStoneDashUrl();

        $this->assertNull($result);
    }

    public function testGetPublicKeyInProductionShouldReturnProductionKey()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_public_key', 'pk_production');
        $config->setData('sandbox_public_key', 'pk_test_sandbox');
        $config->setData('hub_environment', 'Production');

        $result = $config->getPublicKey();

        $this->assertEquals('pk_production', $result);
    }

    public function testGetPublicKeyInSandboxShouldReturnSandboxKey()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_public_key', 'pk_production');
        $config->setData('sandbox_public_key', 'pk_test_sandbox');
        $config->setData('hub_environment', EnvironmentsTypes::SANDBOX_VALUE);

        $result = $config->getPublicKey();

        $this->assertEquals('pk_test_sandbox', $result);
    }

    public function testGetPublicKeyInSandboxWithoutSandboxKeyShouldReturnProductionKey()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_public_key', 'pk_production');
        $config->setData('hub_environment', EnvironmentsTypes::SANDBOX_VALUE);

        $result = $config->getPublicKey();

        $this->assertEquals('pk_production', $result);
    }

    public function testGetSecretKeyInProductionShouldReturnProductionKey()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_secret_key', 'sk_production');
        $config->setData('sandbox_secret_key', 'sk_test_sandbox');
        $config->setData('hub_environment', 'Production');

        $result = $config->getSecretKey();

        $this->assertEquals('sk_production', $result);
    }

    public function testGetSecretKeyInSandboxShouldReturnSandboxKey()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('production_secret_key', 'sk_production');
        $config->setData('sandbox_secret_key', 'sk_test_sandbox');
        $config->setData('hub_environment', EnvironmentsTypes::SANDBOX_VALUE);

        $result = $config->getSecretKey();

        $this->assertEquals('sk_test_sandbox', $result);
    }

    public function testGetCardOperationForCoreWithOperationType2ShouldReturnAuthAndCapture()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_operation_type', 2);

        $result = $config->getCardOperationForCore();

        $this->assertEquals('auth_and_capture', $result);
    }

    public function testGetCardOperationForCoreWithOperationType1ShouldReturnAuthOnly()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_operation_type', 1);

        $result = $config->getCardOperationForCore();

        $this->assertEquals('auth_only', $result);
    }

    public function testGetCcFlagsWithDataShouldReturnArray()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $flags = ['visa', 'mastercard', 'elo'];

        $config = new Config();
        $config->setData('cc_flags', $flags);

        $result = $config->getCcFlags();

        $this->assertEquals($flags, $result);
    }

    public function testGetCcFlagsWithoutDataShouldReturnEmptyArray()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->getCcFlags();

        $this->assertEquals([], $result);
    }

    public function testIsPixEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_pix', Config::ENABLED);

        $result = $config->isPixEnabled();

        $this->assertTrue($result);
    }

    public function testIsPixEnabledWithNoValueShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_pix', 'no');

        $result = $config->isPixEnabled();

        $this->assertFalse($result);
    }

    public function testIsBilletEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_billet', Config::ENABLED);

        $result = $config->isBilletEnabled();

        $this->assertTrue($result);
    }

    public function testIsCreditCardEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_credit_card', Config::ENABLED);

        $result = $config->isCreditCardEnabled();

        $this->assertTrue($result);
    }

    public function testIsVoucherEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_voucher', Config::ENABLED);

        $result = $config->isVoucherEnabled();

        $this->assertTrue($result);
    }

    public function testIsTwoCreditCardEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('multimethods_2_cards', Config::ENABLED);

        $result = $config->isTwoCreditCardEnabled();

        $this->assertTrue($result);
    }

    public function testIsBilletAndCreditCardEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('multimethods_billet_card', Config::ENABLED);

        $result = $config->isBilletAndCreditCardEnabled();

        $this->assertTrue($result);
    }

    public function testIsAnyBilletMethodEnabledWithBilletEnabledShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_billet', Config::ENABLED);

        $result = $config->isAnyBilletMethodEnabled();

        $this->assertTrue($result);
    }

    public function testIsAnyBilletMethodEnabledWithBilletAndCardEnabledShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('multimethods_billet_card', Config::ENABLED);

        $result = $config->isAnyBilletMethodEnabled();

        $this->assertTrue($result);
    }

    public function testIsAnyBilletMethodEnabledWithAllDisabledShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_billet', 'no');
        $config->setData('multimethods_billet_card', 'no');

        $result = $config->isAnyBilletMethodEnabled();

        $this->assertFalse($result);
    }

    public function testIsAnyCreditCardMethodEnabledWithCreditCardEnabledShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_credit_card', Config::ENABLED);

        $result = $config->isAnyCreditCardMethodEnabled();

        $this->assertTrue($result);
    }

    public function testIsAnyCreditCardMethodEnabledWithTwoCardsEnabledShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('multimethods_2_cards', Config::ENABLED);

        $result = $config->isAnyCreditCardMethodEnabled();

        $this->assertTrue($result);
    }

    public function testIsAnyCreditCardMethodEnabledWithBilletAndCardEnabledShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('multimethods_billet_card', Config::ENABLED);

        $result = $config->isAnyCreditCardMethodEnabled();

        $this->assertTrue($result);
    }

    public function testIsAnyCreditCardMethodEnabledWithAllDisabledShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_credit_card', 'no');
        $config->setData('multimethods_2_cards', 'no');
        $config->setData('multimethods_billet_card', 'no');

        $result = $config->isAnyCreditCardMethodEnabled();

        $this->assertFalse($result);
    }

    public function testAvailablePaymentMethodsShouldReturnCorrectArrayAndAllTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_pix', Config::ENABLED);
        $config->setData('enable_billet', Config::ENABLED);
        $config->setData('enable_credit_card', Config::ENABLED);
        $config->setData('enable_voucher', Config::ENABLED);

        $result = $config->availablePaymentMethods();

        $this->assertIsArray($result);
        $this->assertArrayHasKey(PaymentEnum::PIX, $result);
        $this->assertArrayHasKey(PaymentEnum::BILLET, $result);
        $this->assertArrayHasKey(PaymentEnum::CREDIT_CARD, $result);
        $this->assertArrayHasKey(PaymentEnum::VOUCHER, $result);

        $this->assertTrue($result[PaymentEnum::PIX]);
        $this->assertTrue($result[PaymentEnum::BILLET]);
        $this->assertTrue($result[PaymentEnum::CREDIT_CARD]);
        $this->assertTrue($result[PaymentEnum::VOUCHER]);
    }

    public function testAvailablePaymentMethodsWithAllDisabledShouldReturnAllFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_pix', 'no');
        $config->setData('enable_billet', 'no');
        $config->setData('enable_credit_card', 'no');
        $config->setData('enable_voucher', 'no');

        $result = $config->availablePaymentMethods();

        $this->assertFalse($result[PaymentEnum::PIX]);
        $this->assertFalse($result[PaymentEnum::BILLET]);
        $this->assertFalse($result[PaymentEnum::CREDIT_CARD]);
        $this->assertFalse($result[PaymentEnum::VOUCHER]);
    }

    public function testIsTdsEnabledWithYesValueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('tds_enabled', Config::ENABLED);

        $result = $config->isTdsEnabled();

        $this->assertTrue($result);
    }

    public function testIsTdsEnabledWithNoValueShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('tds_enabled', 'no');

        $result = $config->isTdsEnabled();

        $this->assertFalse($result);
    }

    public function testGetTdsMinAmountWithEmptyValueShouldReturnZero()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('tds_min_amount', '');

        $result = $config->getTdsMinAmount();

        $this->assertEquals(0, $result);
    }

    public function testGetTdsMinAmountWithNumericStringShouldReturnInteger()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('tds_min_amount', '100');

        $result = $config->getTdsMinAmount();

        $this->assertEquals(100, $result);
    }

    public function testGetTdsMinAmountWithIntegerShouldReturnInteger()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('tds_min_amount', 150);

        $result = $config->getTdsMinAmount();

        $this->assertEquals(150, $result);
    }

    public function testGetTdsMinAmountWithFormattedStringShouldConvertCorrectly()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $moneyServiceMock = Mockery::mock('overload:' . MoneyService::class);
        $moneyServiceMock->shouldReceive('removeSeparators')
            ->with('1.234,56')
            ->andReturn('123456');
        $moneyServiceMock->shouldReceive('centsToFloat')
            ->with('123456')
            ->andReturn(1234.56);

        $config = new Config();
        $config->setData('tds_min_amount', '1.234,56');

        $result = $config->getTdsMinAmount();

        $this->assertEquals(1234.56, $result);
    }

    public function testSetAccountIdShouldSetDataAndUpdateGooglepay()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => [Config::ACCOUNT_ID => 'acc_old'],
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        Brain\Monkey\Functions\expect('update_option')
            ->once()
            ->with('woocommerce_woo-pagarme-payments-googlepay_settings', Mockery::on(function ($arg) {
                return is_array($arg) && $arg[Config::ACCOUNT_ID] === 'acc_123';
            }));

        $config = new Config();
        $config->setAccountId('acc_123');

        $this->assertEquals('acc_123', $config->getData(Config::ACCOUNT_ID));
    }

    public function testSetAccountIdWithoutGooglepayOptionShouldOnlySetData()
    {
        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        Brain\Monkey\Functions\expect('get_option')
            ->atLeast()->once()
            ->andReturn(false);

        Brain\Monkey\Functions\expect('update_option')
            ->never();

        $config = new Config();
        $config->setAccountId('acc_123');

        $this->assertEquals('acc_123', $config->getData(Config::ACCOUNT_ID));
    }

    public function testSetPaymentProfileIdShouldSetDataCorrectly()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setPaymentProfileId('pp_123');

        $this->assertEquals('pp_123', $config->getData(Config::PAYMENT_PROFILE_ID));
    }

    public function testSetPoiTypeShouldSetDataCorrectly()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setPoiType('ecommerce');

        $this->assertEquals('ecommerce', $config->getData(Config::POI_TYPE));
    }

    public function testGetPaymentProfileIdShouldReturnStoredValue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData(Config::PAYMENT_PROFILE_ID, 'pp_456');

        $result = $config->getPaymentProfileId();

        $this->assertEquals('pp_456', $result);
    }

    public function testGetPoiTypeShouldReturnStoredValue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData(Config::POI_TYPE, 'ecommerce');

        $result = $config->getPoiType();

        $this->assertEquals('ecommerce', $result);
    }

    public function testIsOneStoneEnabledWithPaymentProfileIdShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData(Config::PAYMENT_PROFILE_ID, 'pp_123');

        $result = $config->isOneStoneEnabled();

        $this->assertTrue($result);
    }

    public function testIsOneStoneEnabledWithoutPaymentProfileIdShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->isOneStoneEnabled();

        $this->assertFalse($result);
    }

    public function testGetIsInstallmentsDefaultConfigWithLegacyTypeShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_installment_type', CardInstallments::INSTALLMENTS_LEGACY);

        $result = $config->getIsInstallmentsDefaultConfig();

        $this->assertTrue($result);
    }

    public function testGetIsInstallmentsDefaultConfigWithAllFlagsTypeShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_installment_type', CardInstallments::INSTALLMENTS_FOR_ALL_FLAGS);

        $result = $config->getIsInstallmentsDefaultConfig();

        $this->assertTrue($result);
    }

    public function testGetIsInstallmentsDefaultConfigWithOtherTypeShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_installment_type', CardInstallments::INSTALLMENTS_BY_FLAG);

        $result = $config->getIsInstallmentsDefaultConfig();

        $this->assertFalse($result);
    }

    public function testGetInstallmentTypeShouldReturnStoredValue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_installment_type', CardInstallments::INSTALLMENTS_BY_FLAG);

        $result = $config->getInstallmentType();

        $this->assertEquals(CardInstallments::INSTALLMENTS_BY_FLAG, $result);
    }

    public function testGetMulticustomersShouldCheckMulticustomersFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('multicustomers', Config::ENABLED);

        $result = $config->getMulticustomers();

        $this->assertTrue($result);
    }

    public function testGetModifyAddressShouldCheckModifyAddressFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('modify_address', Config::ENABLED);

        $result = $config->getModifyAddress();

        $this->assertTrue($result);
    }

    public function testGetAllowNoAddressShouldCheckAllowNoAddressFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('allow_no_address', Config::ENABLED);

        $result = $config->getAllowNoAddress();

        $this->assertTrue($result);
    }

    public function testGetCcAllowSaveShouldCheckCcAllowSaveFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('cc_allow_save', Config::ENABLED);

        $result = $config->getCcAllowSave();

        $this->assertTrue($result);
    }

    public function testGetVoucherCardWalletShouldCheckVoucherCardWalletFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('voucher_card_wallet', Config::ENABLED);

        $result = $config->getVoucherCardWallet();

        $this->assertTrue($result);
    }

    public function testGetEnableLogsShouldCheckEnableLogsFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('enable_logs', Config::ENABLED);

        $result = $config->getEnableLogs();

        $this->assertTrue($result);
    }

    public function testGetIsGatewayIntegrationTypeShouldCheckFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('is_gateway_integration_type', Config::ENABLED);

        $result = $config->getIsGatewayIntegrationType();

        $this->assertTrue($result);
    }

    public function testGetAntifraudEnabledShouldCheckAntifraudEnabledFlag()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('antifraud_enabled', Config::ENABLED);

        $result = $config->getAntifraudEnabled();

        $this->assertTrue($result);
    }

    public function testGetIsVoucherPSPWithVoucherTrueShouldReturnTrue()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('is_payment_psp', ['voucher' => true]);

        $result = $config->getIsVoucherPSP();

        $this->assertTrue($result);
    }

    public function testGetIsVoucherPSPWithVoucherFalseShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('is_payment_psp', ['voucher' => false]);

        $result = $config->getIsVoucherPSP();

        $this->assertFalse($result);
    }

    public function testGetIsVoucherPSPWithoutVoucherKeyShouldReturnFalse()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();
        $config->setData('is_payment_psp', ['credit_card' => true]);

        $result = $config->getIsVoucherPSP();

        $this->assertFalse($result);
    }

    public function testLogShouldReturnWCLoggerInstance()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->log();

        $this->assertInstanceOf(WC_Logger::class, $result);
    }

    public function testGetOptionKeyShouldReturnPrefixedSettingsKey()
    {
        Brain\Monkey\Functions\stubs([
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')
            ->with('settings')
            ->andReturn('pagarme_settings');

        $config = new Config();

        $result = $config->getOptionKey();

        $this->assertEquals('pagarme_settings', $result);
    }
}
