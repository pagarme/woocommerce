<?php

namespace Woocommerce\Pagarme\Tests\Controller;

use Brain;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Woocommerce\Pagarme\Controller\HubAccounts;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Core;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class HubAccountsTest extends TestCase
{
    /** @var HubAccounts */
    private $hubAccounts;

    /** @var Mockery\MockInterface */
    private $configMock;

    public function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();

        Brain\Monkey\Functions\stubs([
            'add_action' => null,
            'get_option' => false,
        ]);

        $utilsMock = Mockery::mock('alias:' . Utils::class);
        $utilsMock->shouldReceive('is_request_ajax')->andReturn(true);

        $coreMock = Mockery::mock('alias:' . Core::class);
        $coreMock->shouldReceive('tag_name')->andReturn('pagarme_settings');

        $this->configMock = Mockery::mock(Config::class);
        $this->hubAccounts = new HubAccounts();

        $reflection = new ReflectionClass($this->hubAccounts);
        $configProperty = $reflection->getProperty('config');
        $configProperty->setAccessible(true);
        $configProperty->setValue($this->hubAccounts, $this->configMock);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        Brain\Monkey\tearDown();
    }

    // saveIdentifiersFromWebhook

    public function testSaveIdentifiersFromWebhookWithEmptyBodyShouldReturn()
    {
        // Arrange
        $this->configMock->shouldNotReceive('setPaymentProfileId');
        $this->configMock->shouldNotReceive('setPoiType');

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook(null);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWithoutIdentifierShouldReturn()
    {
        // Arrange
        $body = new \stdClass();
        $this->configMock->shouldNotReceive('setPaymentProfileId');
        $this->configMock->shouldNotReceive('setPoiType');

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWithNonEcommerceIdentifierShouldReturn()
    {
        // Arrange
        $body = $this->buildWebhookBody('POS', 'pp_123');
        $this->configMock->shouldNotReceive('setPaymentProfileId');
        $this->configMock->shouldNotReceive('setPoiType');

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWithEcommerceIdentifierShouldSaveBothFields()
    {
        // Arrange
        $body = $this->buildWebhookBody('Ecommerce', 'pp_123');
        $this->configMock->shouldReceive('getPaymentProfileId')->andReturn(null);
        $this->configMock->shouldReceive('setPaymentProfileId')->once()->with('pp_123');
        $this->configMock->shouldReceive('setPoiType')->once()->with('Ecommerce');
        $this->configMock->shouldReceive('save')->twice();

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWithEcommerceUppercaseShouldSaveBothFields()
    {
        // Arrange
        $body = $this->buildWebhookBody('ECOMMERCE', 'pp_456');
        $this->configMock->shouldReceive('getPaymentProfileId')->andReturn(null);
        $this->configMock->shouldReceive('setPaymentProfileId')->once()->with('pp_456');
        $this->configMock->shouldReceive('setPoiType')->once()->with('ECOMMERCE');
        $this->configMock->shouldReceive('save')->twice();

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWhenPaymentProfileIdAlreadyExistsShouldNotOverwrite()
    {
        // Arrange
        $body = $this->buildWebhookBody('Ecommerce', 'pp_new');
        $this->configMock->shouldReceive('getPaymentProfileId')->andReturn('pp_existing');
        $this->configMock->shouldNotReceive('setPaymentProfileId');
        $this->configMock->shouldReceive('setPoiType')->once()->with('Ecommerce');
        $this->configMock->shouldReceive('save')->once();

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWhenPaymentProfileIdIsMissingShouldSaveOnlyPoiType()
    {
        // Arrange
        $body = $this->buildWebhookBodyWithoutPaymentProfileId('Ecommerce');
        $this->configMock->shouldReceive('getPaymentProfileId')->andReturn(null);
        $this->configMock->shouldNotReceive('setPaymentProfileId');
        $this->configMock->shouldReceive('setPoiType')->once()->with('Ecommerce');
        $this->configMock->shouldReceive('save')->once();

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    public function testSaveIdentifiersFromWebhookWhenPoiTypeIsMissingShouldReturn()
    {
        // Arrange
        $identifier = new \stdClass();
        $identifier->{Config::PAYMENT_PROFILE_ID} = 'pp_123';

        $body = new \stdClass();
        $body->identifier = $identifier;

        $this->configMock->shouldNotReceive('setPaymentProfileId');
        $this->configMock->shouldNotReceive('setPoiType');

        // Act
        $this->hubAccounts->saveIdentifiersFromWebhook($body);

        // Assert
        $this->expectNotToPerformAssertions();
    }

    // helpers

    private function buildWebhookBody(string $poiType, string $paymentProfileId): object
    {
        $identifier = new \stdClass();
        $identifier->{HubAccounts::IDENTIFIER_POI_TYPE} = $poiType;
        $identifier->{Config::PAYMENT_PROFILE_ID} = $paymentProfileId;

        $body = new \stdClass();
        $body->identifier = $identifier;

        return $body;
    }

    private function buildWebhookBodyWithoutPaymentProfileId(string $poiType): object
    {
        $identifier = new \stdClass();
        $identifier->{HubAccounts::IDENTIFIER_POI_TYPE} = $poiType;

        $body = new \stdClass();
        $body->identifier = $identifier;

        return $body;
    }
}
