<?php


namespace Pagarme\Core\Test\Payment;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Payment\ValueObjects\Phone;

class PhoneTests extends TestCase
{
    /**
     * @var Customer
     */
    private $phone;

    public function testBuildPhoneObject()
    {
        $this->phone = new Phone('11987654321');


        $this->assertEquals('55', $this->phone->getCountryCode());
        $this->assertEquals('11', $this->phone->getAreaCode());
        $this->assertEquals('987654321', $this->phone->getNumber());
        $this->assertEquals('5511987654321', $this->phone->getFullNumber());
    }

    public function testPhoneOnlyDigits()
    {
        $this->phone = new Phone(' Tel. 12 987654333 ');

        $this->assertEquals('5512987654333', $this->phone->getFullNumber());
    }

    public function testPhoneRemoveCharactersAfterMaxLength()
    {
        $phoneMaxLength = 15;
        $phone = str_repeat('1', $phoneMaxLength + 1);

        $this->phone = new Phone($phone);

        $this->assertEquals(
            $phoneMaxLength, strlen($this->phone->getFullNumber())
        );
    }

    public function testPhoneAddCharactersForMinLength()
    {
        $phoneMinLength = 7;

        $this->phone = new Phone('1');

        $this->assertEquals(
            $phoneMinLength, strlen($this->phone->getFullNumber())
        );
    }
}
