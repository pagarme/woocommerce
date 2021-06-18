<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Responses\ServiceResponse;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\Id\TransactionId;
use PHPUnit\Framework\TestCase;
use Mockery;
use Carbon\Carbon;

class ServiceResponseTest extends TestCase
{
    public function testServiceResponseObject()
    {
        $object = new ServiceResponse(true, 'Foi um sucesso', (object)['status' => 200, 'message' => 'ok']);
        $this->assertEquals('Foi um sucesso', $object->getMessage());
        $this->assertInternalType('object', $object->getObject());
        $this->assertInstanceOf(\stdClass::class, $object->getObject());
        $this->assertEquals(true, $object->isSuccess());
    }
}
