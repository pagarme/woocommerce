<?php

namespace Woocommerce\Pagarme\Tests\Controller;

use Brain;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Woocommerce\Pagarme\Controller\HubCommand;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class HubCommandTest extends TestCase
{
    /** @var HubCommand */
    private $hubCommand;

    /** @var Mockery\MockInterface */
    private $settingsMock;

    public function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();

        Brain\Monkey\Functions\stubs([
            'add_action' => null,
            'get_option' => false,
        ]);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');
        $coreMock->shouldReceive('getHubCommandName')->andReturn('pagarme-hub-command');

        $utilsMock = Mockery::mock('alias:' . Utils::class);
        $utilsMock->shouldReceive('isCurrentUserAdmin')->andReturn(true);

        $this->settingsMock = Mockery::mock(Config::class);
        $this->hubCommand = new HubCommand();

        $reflection = new ReflectionClass($this->hubCommand);
        $settingsProperty = $reflection->getProperty('settings');
        $settingsProperty->setAccessible(true);
        $settingsProperty->setValue($this->hubCommand, $this->settingsMock);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        Brain\Monkey\tearDown();
    }

    public function testUninstallCommandShouldClearPaymentProfileId()
    {
        // Arrange
        $this->settingsMock
            ->shouldReceive('addData')
            ->once()
            ->withArgs(function (array $keys) {
                return array_key_exists(Config::PAYMENT_PROFILE_ID, $keys)
                    && $keys[Config::PAYMENT_PROFILE_ID] === null;
            })
            ->andReturnSelf();
        $this->settingsMock->shouldReceive('save')->once();

        // Act
        $this->hubCommand->uninstallCommand();

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testUninstallCommandShouldClearPoiType()
    {
        // Arrange
        $this->settingsMock
            ->shouldReceive('addData')
            ->once()
            ->withArgs(function (array $keys) {
                return array_key_exists(Config::POI_TYPE, $keys)
                    && $keys[Config::POI_TYPE] === null;
            })
            ->andReturnSelf();
        $this->settingsMock->shouldReceive('save')->once();

        // Act
        $this->hubCommand->uninstallCommand();

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testUninstallCommandShouldReturnSuccessMessage()
    {
        // Arrange
        $this->settingsMock->shouldReceive('addData')->andReturnSelf();
        $this->settingsMock->shouldReceive('save');

        // Act
        $result = $this->hubCommand->uninstallCommand();

        // Assert
        $this->assertEquals('Hub uninstalled successfully', $result);
    }

    public function testUninstallCommandShouldClearAllHubKeys()
    {
        // Arrange
        $expectedKeys = [
            'hub_install_id',
            'hub_environment',
            'production_secret_key',
            'production_public_key',
            'sandbox_secret_key',
            'sandbox_public_key',
            'environment',
            'account_id',
            'merchant_id',
            'hub_account_errors',
            Config::PAYMENT_PROFILE_ID,
            Config::POI_TYPE,
        ];

        $this->settingsMock
            ->shouldReceive('addData')
            ->once()
            ->withArgs(function (array $keys) use ($expectedKeys) {
                foreach ($expectedKeys as $key) {
                    if (!array_key_exists($key, $keys) || $keys[$key] !== null) {
                        return false;
                    }
                }
                return true;
            })
            ->andReturnSelf();
        $this->settingsMock->shouldReceive('save')->once();

        // Act
        $this->hubCommand->uninstallCommand();

        // Assert
        $this->expectNotToPerformAssertions();
    }
}
