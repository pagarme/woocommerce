<?php

namespace Pagarme\Core\Test\Hub\Factories;

use Pagarme\Core\Hub\Commands\AbstractCommand;
use Pagarme\Core\Hub\Commands\CommandType;
use Pagarme\Core\Hub\Factories\HubCommandFactory;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use PHPUnit\Framework\TestCase;

class HubCommandFactoryTest extends TestCase
{
    private $factory;
    private $payload;

    public function setUp(): void
    {
        $this->factory = new HubCommandFactory();

        $this->payload = json_decode('{
            "access_token": "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
            "account_id": "acc_XXXXXXXXXXXXXXXX",
            "account_public_key": "pk_test_XXXXXXXXXXXXXXXX",
            "install_id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
            "merchant_id": "merch_XXXXXXXXXXXXXXXX",
            "additional_data": {},
            "type": "Development",
            "actions": [],
            "events": [],
            "command": "Install"
        }');
    }

    public function testShouldCreateHubInstallCommand()
    {
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertInstanceOf(AbstractCommand::class, $hubInstall);
    }

    public function testHubDevelopmentShouldUsePKTest()
    {
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals(CommandType::Development(), $hubInstall->getType());
        $this->assertInstanceOf(TestPublicKey::class, $hubInstall->getAccountPublicKey());
    }

    public function testHubSandboxShouldUsePKTest()
    {
        $this->payload->type = "Sandbox";
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals(CommandType::Sandbox(), $hubInstall->getType());
        $this->assertInstanceOf(TestPublicKey::class, $hubInstall->getAccountPublicKey());
    }

    public function testHubProductionShouldUsePKLive()
    {
        $this->payload->account_public_key = "pk_XXXXXXXXXXXXXXXX";
        $this->payload->type = "Production";
        $hubInstall = $this->factory->createFromStdClass($this->payload);
        $this->assertEquals(CommandType::Production(), $hubInstall->getType());
        $this->assertInstanceOf(PublicKey::class, $hubInstall->getAccountPublicKey());
    }
}