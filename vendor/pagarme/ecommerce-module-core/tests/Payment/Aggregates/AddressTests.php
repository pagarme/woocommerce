<?php

namespace Pagarme\Core\Test\Payment\Aggregates;

use Pagarme\Core\Payment\Aggregates\Address;
use PHPUnit\Framework\TestCase;

class AddressTests extends TestCase
{
    /**
     * @var Address
     */
    private $address;

    public function setUp(): void
    {
        $this->address = new Address();
    }

    public function testCityTrim()
    {
        $this->address->setCity(' Florianópolis ');
        $this->assertEquals('Florianópolis', $this->address->getCity());
    }

    public function testCityRemoveCharactersAfterMaxLength()
    {
        $cityMaxLength = 64;
        $addressCity = str_repeat('a', $cityMaxLength + 1);

        $this->address->setCity($addressCity);

        $this->assertEquals($cityMaxLength, strlen($this->address->getCity()));
    }

    public function testNumberRemoveComma()
    {
        $this->address->setNumber('12,3,4,5,6');
        $this->assertEquals('123456', $this->address->getNumber());
    }

    public function testZipCodeRemoveDash()
    {
        $this->address->setZipCode('12345-678');
        $this->assertEquals('12345678', $this->address->getZipCode());
    }

    public function testZipCodeTrim()
    {
        $this->address->setZipCode(' 12345678 ');
        $this->assertEquals('12345678', $this->address->getZipCode());
    }

    public function testZipCodeRemoveCharactersAfterGeneralMaxLength()
    {
        $zipCodeGeneralMaxLength = 16;
        $this->address->setCountry('US');
        $this->address->setZipCode(
            str_repeat('1', $zipCodeGeneralMaxLength + 1)
        );
        $this->assertEquals(
            $zipCodeGeneralMaxLength, strlen($this->address->getZipCode())
        );
    }

    public function testZipCodeRemoveCharactersAfterBrazilianMaxLength()
    {
        $zipCodeBrazilianMaxLength = 8;
        $this->address->setCountry('BR');
        $this->address->setZipCode(
            str_repeat('1', $zipCodeBrazilianMaxLength + 1)
        );
        $this->assertEquals(
            $zipCodeBrazilianMaxLength, strlen($this->address->getZipCode())
        );
    }

    public function testZipCodeAddCharactersForBrazilianMinLength()
    {
        $zipCodeBrazilianMinLength = 8;
        $this->address->setCountry('BR');
        $this->address->setZipCode('1');
        $this->assertEquals(
            $zipCodeBrazilianMinLength, strlen($this->address->getZipCode())
        );
    }
}
