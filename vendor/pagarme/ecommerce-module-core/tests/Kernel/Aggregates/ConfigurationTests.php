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

    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    public function testIsEnabled()
    {
        $this->configuration->setEnabled(true);
        $this->assertInternalType('bool', $this->configuration->isEnabled());
        $this->assertEquals(true, $this->configuration->isEnabled());
    }

    public function testIsUnabled()
    {
        $this->configuration->setEnabled(false);
        $this->assertInternalType('bool', $this->configuration->isEnabled());
        $this->assertEquals(false, $this->configuration->isEnabled());
    }
}
