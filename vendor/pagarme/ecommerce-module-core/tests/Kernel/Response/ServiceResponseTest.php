<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Responses\ServiceResponse;
use PHPUnit\Framework\TestCase;

class ServiceResponseTest extends TestCase
{
    public function testServiceResponseObject()
    {
        $object = new ServiceResponse(true, 'Foi um sucesso', (object)['status' => 200, 'message' => 'ok']);
        $this->assertEquals('Foi um sucesso', $object->getMessage());
        $this->assertIsObject($object->getObject());
        $this->assertInstanceOf(\stdClass::class, $object->getObject());
        $this->assertEquals(true, $object->isSuccess());
    }
}
