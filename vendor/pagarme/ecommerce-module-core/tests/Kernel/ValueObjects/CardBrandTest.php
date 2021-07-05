<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use PHPUnit\Framework\TestCase;

class CardBrandTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\CardBrand
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aCardBrandShouldBeComparable()
    {
        $cardBrandVisa1 = CardBrand::visa();
        $cardBrandVisa2 = CardBrand::visa();

        $cardBrandMastercard2 = CardBrand::mastercard();

        $this->assertTrue($cardBrandVisa1->equals($cardBrandVisa2));
        $this->assertFalse($cardBrandVisa1->equals($cardBrandMastercard2));
        $this->assertFalse($cardBrandVisa2->equals($cardBrandMastercard2));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\CardBrand
     */
    public function aCardBrandShouldBeJsonSerializable()
    {
        $cardBrandVisa1 = CardBrand::visa();

        $json = json_encode($cardBrandVisa1);
        $expected = json_encode(CardBrand::VISA);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\CardBrand
     */
    public function allCardBrandConstantsDefinedInTheClassShouldBeInstantiable()
    {
        $cardBrandVisa = CardBrand::visa();

        $reflectionClass = new \ReflectionClass($cardBrandVisa);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $brand) {
            $cardBrand = CardBrand::$brand();
            $this->assertEquals($brand, $cardBrand->getName());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\CardBrand
     */
    public function aInvalidCardBrandShouldNotBeInstantiable()
    {
        $cardBrandClass = CardBrand::class;
        $invalidCardBrand = CardBrand::NO_BRAND . CardBrand::NO_BRAND;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method {$cardBrandClass}::{$invalidCardBrand}()");

        $cardBrandVisa = CardBrand::$invalidCardBrand();
    }
}
