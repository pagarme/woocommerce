<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\Configuration;
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
}
