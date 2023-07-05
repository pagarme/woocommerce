<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\LogObject;
use PHPUnit\Framework\TestCase;

class LogObjectTests extends TestCase
{
    /**
     * @var LogObject
     */
    private $logObject;

    public function setUp(): void
    {
        $this->logObject = new LogObject();
    }

    public function testJsonSerialize()
    {
        $this->assertIsObject($this->logObject->jsonSerialize());
        $this->assertInstanceOf(\stdClass::class, $this->logObject->jsonSerialize());
    }
}
