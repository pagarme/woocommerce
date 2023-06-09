<?php

namespace Pagarme\Core\Test\Recurrence;

use Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject;
use PHPUnit\Framework\TestCase;

class PricingSchemeValueObjectTest extends TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Call to undefined method Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject::time()
     */
    public function testShouldReturnAExceptionBecauseTheTypeNotExist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject::time()");
        PricingSchemeValueObject::time(2);
    }

    public function testShoudReturnAPricingSchemeWithTypeMonthAndCountTwo()
    {
        $price = PricingSchemeValueObject::unit(2000);
        $this->assertEquals(2000, $price->getPrice());
        $this->assertEquals("unit", $price->getSchemeType());
    }

    public function testShoudReturnTrueBecauseTheObjectsAreEquals()
    {
        $price = PricingSchemeValueObject::unit(2000);
        $price2 = PricingSchemeValueObject::unit(2000);
        $this->assertTrue($price->equals($price2));
    }

    public function testShoudReturnAnJsonWhenCallJsonEncodeMethod()
    {
        $price = PricingSchemeValueObject::unit(200);
        $this->assertJson(json_encode($price));
    }
}