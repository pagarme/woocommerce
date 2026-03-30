<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain\Monkey;
use Mockery;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\PagarmeCoreConfigManagement;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        Monkey\Functions\when('get_option')->justReturn(false);
        Monkey\Functions\when('esc_url')->returnArg(1);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        Monkey\tearDown();
    }

    private function makeConfig(array $data = []): Config
    {
        $management = Mockery::mock(PagarmeCoreConfigManagement::class);
        return new Config($management, null, $data);
    }

    // hasIdentifiersSaved() tests

    public function testHasIdentifiersSavedReturnsTrueWhenMerchantAndAccountAreSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'merchant_id' => 'merchant_123',
            'account_id'  => 'account_456',
        ]);

        // Act
        $result = $config->hasIdentifiersSaved();

        // Assert
        $this->assertTrue($result);
    }

    public function testHasIdentifiersSavedReturnsFalseWhenOnlyMerchantIdIsSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'merchant_id' => 'merchant_123',
        ]);

        // Act
        $result = $config->hasIdentifiersSaved();

        // Assert
        $this->assertFalse($result);
    }

    public function testHasIdentifiersSavedReturnsFalseWhenOnlyAccountIdIsSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'account_id' => 'account_456',
        ]);

        // Act
        $result = $config->hasIdentifiersSaved();

        // Assert
        $this->assertFalse($result);
    }

    public function testHasIdentifiersSavedReturnsTrueWhenPaymentProfileIdIsSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'payment_profile_id' => 'profile_789',
        ]);

        // Act
        $result = $config->hasIdentifiersSaved();

        // Assert
        $this->assertTrue($result);
    }

    public function testHasIdentifiersSavedReturnsFalseWhenNoIdentifiersAreSet()
    {
        // Arrange
        $config = $this->makeConfig();

        // Act
        $result = $config->hasIdentifiersSaved();

        // Assert
        $this->assertFalse($result);
    }

    public function testHasIdentifiersSavedReturnsTrueWhenAllIdentifiersAreSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'merchant_id'        => 'merchant_123',
            'account_id'         => 'account_456',
            'payment_profile_id' => 'profile_789',
        ]);

        // Act
        $result = $config->hasIdentifiersSaved();

        // Assert
        $this->assertTrue($result);
    }

    // getDashUrl() tests

    public function testGetDashUrlReturnsNullWhenNoIdentifiersAreSet()
    {
        // Arrange
        $config = $this->makeConfig();

        // Act
        $result = $config->getDashUrl();

        // Assert
        $this->assertNull($result);
    }

    public function testGetDashUrlReturnsPagarMeUrlWhenMerchantAndAccountAreSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'merchant_id' => 'merchant_123',
            'account_id'  => 'account_456',
        ]);

        // Act
        $result = $config->getDashUrl();

        // Assert
        $this->assertEquals(
            'https://dash.pagar.me/merchant_123/account_456/',
            $result
        );
    }

    public function testGetDashUrlReturnsStoneUrlWhenPaymentProfileIdIsSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'payment_profile_id' => 'profile_789',
        ]);

        // Act
        $result = $config->getDashUrl();

        // Assert
        $this->assertEquals(
            'https://dash.stone.com.br/profile_789/',
            $result
        );
    }

    public function testGetDashUrlPrioritizesStoneUrlWhenAllIdentifiersAreSet()
    {
        // Arrange
        $config = $this->makeConfig([
            'merchant_id'        => 'merchant_123',
            'account_id'         => 'account_456',
            'payment_profile_id' => 'profile_789',
        ]);

        // Act
        $result = $config->getDashUrl();

        // Assert
        $this->assertEquals(
            'https://dash.stone.com.br/profile_789/',
            $result
        );
    }
}
