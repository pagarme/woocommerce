<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use PHPUnit\Framework\TestCase;

class SubscriptionIdTest extends TestCase
{
    const VALID1 = 'sub_xxxxxxxxxxxxxxxx';

    public function testSubscriptionId()
    {
        $validStringObject = new SubscriptionId(self::VALID1);

        $this->assertEquals('sub_xxxxxxxxxxxxxxxx', $validStringObject->getValue());

        $this->expectException(InvalidParamException::class);
        $validStringObject = new SubscriptionId(self::VALID1."inva");
    }
}
