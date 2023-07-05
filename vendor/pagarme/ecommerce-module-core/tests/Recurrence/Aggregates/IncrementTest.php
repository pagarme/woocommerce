<?php

namespace Pagarme\Core\Test\Recurrence\Aggregates;

use PagarmeCoreApiLib\Models\CreateIncrementRequest;
use Pagarme\Core\Recurrence\Aggregates\Increment;
use PHPUnit\Framework\TestCase;

class IncrementTest extends TestCase
{

    /**
     * @var Increment
     */
    private $increment;

    protected function setUp(): void
    {
        $this->increment = new Increment();
    }

    public function testShouldReturnAnIncrementObject()
    {
        $this->increment->setValue(300);
        $this->increment->setIncrementType('flat');
        $this->increment->setCycles(3);

        $this->assertEquals(300, $this->increment->getValue());
        $this->assertEquals('flat', $this->increment->getIncrementType());
        $this->assertEquals(3, $this->increment->getCycles());
    }

    public function testShouldReturnAnIncrementConvertedToSDKRequest()
    {
        $this->increment->setValue(300);
        $this->increment->setIncrementType('flat');
        $this->increment->setCycles(3);

        $incrementConverted = $this->increment->convertToSDKRequest();

        $this->assertInstanceOf(CreateIncrementRequest::class, $incrementConverted);
        $this->assertEquals(300, $incrementConverted->value);
        $this->assertEquals('flat', $incrementConverted->incrementType);
    }

    public function testReturnIncrementObjectSerialized()
    {
        $this->increment->setValue(300);
        $this->increment->setIncrementType('flat');
        $this->increment->setCycles(3);

        $this->assertJson(json_encode($this->increment));
    }
}