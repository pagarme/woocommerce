<?php

namespace Pagarme\Core\Test\Hub\Aggregates;

use Pagarme\Core\Hub\Aggregates\InstallToken;
use Pagarme\Core\Hub\ValueObjects\HubInstallToken;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use PHPUnit\Framework\TestCase;

class InstallTokenTests extends TestCase
{
    /**
     * @var InstallToken
     */
    public $installToken;
    /**
     * @var HubInstallToken
     */
    public $hubInstallToken;

    /**
     * @throws InvalidParamException
     */
    public function setUp(): void
    {
        $token = hash('sha512', '1' . '|' . microtime());
        $this->hubInstallToken = new HubInstallToken($token);

        $lifeSpam = InstallToken::LIFE_SPAN;
        $createdTime = time();
        $expireTime = $createdTime + $lifeSpam;

        $this->installToken = new InstallToken();
        $this->installToken->setToken($this->hubInstallToken);
        $this->installToken->setUsed(false);
        $this->installToken->setCreatedAtTimestamp($createdTime);
        $this->installToken->setExpireAtTimestamp($expireTime);
    }


    public function testInstallTokenBeCreated()
    {
        $this->assertInstanceOf(InstallToken::class, $this->installToken);
    }

    public function testInstallTokenMethodGetToken()
    {
        $this->assertInstanceOf(HubInstallToken::class, $this->installToken->getToken());
    }

    public function testInstallTokenMethodIsUsed()
    {
        $this->assertIsBool($this->installToken->isUsed());
    }

    public function testInstallTokenIsExpired()
    {
        $this->assertIsBool($this->installToken->isExpired());
    }

    public function testInstallTokenGetCreatedAtTimestamp()
    {
        $this->assertIsInt($this->installToken->getCreatedAtTimestamp());
    }

    public function testInstallTokenGetExpireAtTimestamp()
    {
        $this->assertIsInt($this->installToken->getExpireAtTimestamp());
    }

    public function testInstallTokenIsDisabled()
    {
        $this->assertIsBool($this->installToken->isDisabled());
    }

    public function testInstallTokenJsonSerialize()
    {
        $this->assertIsObject($this->installToken->jsonSerialize());
        $this->assertInstanceOf(\stdClass::class, $this->installToken->jsonSerialize());
    }
}
