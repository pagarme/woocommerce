<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\MarketplaceConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\PixConfig;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestSecretKey;
use PHPUnit\Framework\TestCase;

class ConfigurationTests extends TestCase
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
    }

    public function testIsEnabled()
    {
        $this->configuration->setEnabled(true);
        $this->assertIsBool($this->configuration->isEnabled());
        $this->assertEquals(true, $this->configuration->isEnabled());
    }

    public function testIsUnabled()
    {
        $this->configuration->setEnabled(false);
        $this->assertIsBool($this->configuration->isEnabled());
        $this->assertEquals(false, $this->configuration->isEnabled());
    }

    public function testHubEnvironmentStartsNull()
    {
        $this->assertNull($this->configuration->getHubEnvironment());
        $this->assertEquals('', $this->configuration->getHubEnvironment());
    }

    public function testHubEnvironmentIsSandbox()
    {
        $this->configuration->setHubEnvironment('Sandbox');
        $this->assertIsString($this->configuration->getHubEnvironment());
        $this->assertEquals('Sandbox', $this->configuration->getHubEnvironment());
    }

    public function testHubEnvironmentIsProduction()
    {
        $this->configuration->setHubEnvironment('Production');
        $this->assertIsString($this->configuration->getHubEnvironment());
        $this->assertEquals('Production', $this->configuration->getHubEnvironment());
    }

    // ========== NOVOS TESTES ADICIONADOS ==========

    // Configurações de Pagamento

    public function testSetAndGetBoletoEnabled()
    {
        $this->configuration->setBoletoEnabled(true);
        $this->assertTrue($this->configuration->isBoletoEnabled());
        $this->assertIsBool($this->configuration->isBoletoEnabled());
    }

    public function testSetAndGetCreditCardEnabled()
    {
        $this->configuration->setCreditCardEnabled(true);
        $this->assertTrue($this->configuration->isCreditCardEnabled());
        $this->assertIsBool($this->configuration->isCreditCardEnabled());
    }

    public function testSetAndGetPixEnabled()
    {
        $pixConfig = new PixConfig();
        $pixConfig->setEnabled(true);
        $this->configuration->setPixConfig($pixConfig);
        
        $this->assertInstanceOf(PixConfig::class, $this->configuration->getPixConfig());
        $this->assertTrue($this->configuration->getPixConfig()->isEnabled());
    }

    public function testSetAndGetTwoCreditCardsEnabled()
    {
        $this->configuration->setTwoCreditCardsEnabled(true);
        $this->assertTrue($this->configuration->isTwoCreditCardsEnabled());
    }

    public function testSetAndGetBoletoCreditCardEnabled()
    {
        $this->configuration->setBoletoCreditCardEnabled(true);
        $this->assertTrue($this->configuration->isBoletoCreditCardEnabled());
    }

    public function testSetAndGetGooglepayEnabled()
    {
        $this->configuration->setGooglepayEnabled(true);
        $this->assertTrue($this->configuration->isGooglepayEnabled());
    }

    // Modo de Operação

    public function testSetAndGetTestMode()
    {
        $this->configuration->setTestMode(true);
        $this->assertTrue($this->configuration->isTestMode());
        
        $this->configuration->setTestMode(false);
        $this->assertFalse($this->configuration->isTestMode());
    }

    public function testSetAndGetCardOperation()
    {
        $this->configuration->setCardOperation(Configuration::CARD_OPERATION_AUTH_ONLY);
        $this->assertEquals(Configuration::CARD_OPERATION_AUTH_ONLY, $this->configuration->getCardOperation());
        
        $this->configuration->setCardOperation(Configuration::CARD_OPERATION_AUTH_AND_CAPTURE);
        $this->assertEquals(Configuration::CARD_OPERATION_AUTH_AND_CAPTURE, $this->configuration->getCardOperation());
    }

    public function testSetAndGetHubInstallId()
    {
        $guid = new GUID('12345678-1234-1234-1234-123456789abc');
        $this->configuration->setHubInstallId($guid);
        
        $this->assertInstanceOf(GUID::class, $this->configuration->getHubInstallId());
        $this->assertEquals($guid, $this->configuration->getHubInstallId());
    }

    // Chaves de API

    public function testSetAndGetSecretKey()
    {
        $secretKey = new TestSecretKey('sk_test_1234567890abcdef');
        $this->configuration->setSecretKey($secretKey);
        
        $this->assertInstanceOf(TestSecretKey::class, $this->configuration->getSecretKey());
    }

    public function testSetAndGetPublicKey()
    {
        $publicKey = new TestPublicKey('pk_test_1234567890abcdef');
        $this->configuration->setPublicKey($publicKey);
        
        $this->assertInstanceOf(TestPublicKey::class, $this->configuration->getPublicKey());
    }

    public function testTestModeWithTestKeys()
    {
        $secretKey = new TestSecretKey('sk_test_1234567890abcdef');
        $publicKey = new TestPublicKey('pk_test_1234567890abcdef');
        
        $this->configuration->setSecretKey($secretKey);
        $this->configuration->setPublicKey($publicKey);
        $this->configuration->setTestMode(true);
        
        $this->assertTrue($this->configuration->isTestMode());
        $this->assertInstanceOf(TestSecretKey::class, $this->configuration->getSecretKey());
        $this->assertInstanceOf(TestPublicKey::class, $this->configuration->getPublicKey());
    }

    // Antifraude

    public function testSetAndGetAntifraudEnabled()
    {
        $this->configuration->setAntifraudEnabled(true);
        $this->assertTrue($this->configuration->isAntifraudEnabled());
        
        $this->configuration->setAntifraudEnabled(false);
        $this->assertFalse($this->configuration->isAntifraudEnabled());
    }

    public function testSetAndGetAntifraudMinAmount()
    {
        $this->configuration->setAntifraudMinAmount(10000);
        $this->assertEquals(10000, $this->configuration->getAntifraudMinAmount());
        $this->assertIsInt($this->configuration->getAntifraudMinAmount());
    }

    // Configurações de Cartão

    public function testSetAndGetCardConfigs()
    {
        $cardConfig = new CardConfig();
        $cardConfig->setBrand('visa');
        $cardConfig->setEnabled(true);
        
        $this->configuration->addCardConfig($cardConfig);
        
        $configs = $this->configuration->getCardConfigs();
        $this->assertIsArray($configs);
        $this->assertNotEmpty($configs);
        $this->assertContainsOnlyInstancesOf(CardConfig::class, $configs);
    }

    public function testAddCardConfig()
    {
        $cardConfig1 = new CardConfig();
        $cardConfig1->setBrand('visa');
        
        $cardConfig2 = new CardConfig();
        $cardConfig2->setBrand('mastercard');
        
        $this->configuration->addCardConfig($cardConfig1);
        $this->configuration->addCardConfig($cardConfig2);
        
        $configs = $this->configuration->getCardConfigs();
        $this->assertCount(2, $configs);
    }

    // Marketplace

    public function testSetAndGetMarketplaceConfig()
    {
        $marketplaceConfig = new MarketplaceConfig();
        $marketplaceConfig->setEnabled(true);
        
        $this->configuration->setMarketplaceConfig($marketplaceConfig);
        
        $this->assertInstanceOf(MarketplaceConfig::class, $this->configuration->getMarketplaceConfig());
        $this->assertTrue($this->configuration->getMarketplaceConfig()->isEnabled());
    }

    // Outras Configurações

    public function testSetAndGetSaveCards()
    {
        $this->configuration->setSaveCards(true);
        $this->assertTrue($this->configuration->isSaveCards());
    }

    public function testSetAndGetMultiBuyer()
    {
        $this->configuration->setMultiBuyer(true);
        $this->assertTrue($this->configuration->isMultiBuyer());
    }

    public function testSetAndGetInstallmentsEnabled()
    {
        $this->configuration->setInstallmentsEnabled(true);
        $this->assertTrue($this->configuration->isInstallmentsEnabled());
    }

    public function testSetAndGetCardStatementDescriptor()
    {
        $descriptor = 'MY STORE NAME';
        $this->configuration->setCardStatementDescriptor($descriptor);
        
        $this->assertEquals($descriptor, $this->configuration->getCardStatementDescriptor());
        $this->assertIsString($this->configuration->getCardStatementDescriptor());
    }

    public function testSetAndGetBoletoDueDays()
    {
        $this->configuration->setBoletoDueDays(5);
        $this->assertEquals(5, $this->configuration->getBoletoDueDays());
    }

    public function testSetAndGetAddressAttributes()
    {
        $addressAttributes = new AddressAttributes();
        $addressAttributes->setStreet('street');
        
        $this->configuration->setAddressAttributes($addressAttributes);
        
        $this->assertInstanceOf(AddressAttributes::class, $this->configuration->getAddressAttributes());
    }

    // Serialização

    public function testJsonSerialize()
    {
        $this->configuration->setEnabled(true);
        $this->configuration->setTestMode(true);
        $this->configuration->setCreditCardEnabled(true);
        $this->configuration->setBoletoEnabled(true);
        
        $json = json_encode($this->configuration);
        $this->assertJson($json);
        
        $data = json_decode($json, true);
        $this->assertIsArray($data);
    }

    public function testConfigurationDefaultValues()
    {
        $config = new Configuration();
        
        // Test default values
        $this->assertTrue($config->isTestMode(), 'Test mode should be true by default');
        $this->assertFalse($config->isSaveCards(), 'Save cards should be false by default');
        $this->assertFalse($config->isMultiBuyer(), 'Multi buyer should be false by default');
        $this->assertEmpty($config->getCardConfigs(), 'Card configs should be empty by default');
    }
}
