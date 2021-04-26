<?php

namespace Pagarme\Core\Test\Payment\Aggregates;

use Pagarme\Core\Payment\Aggregates\Address;
use PHPUnit\Framework\TestCase;

class AddressTests extends TestCase
{
    /**
     * @var Address
     */
    private $andress;

    public function setUp()
    {
        $this->andress = new Address();
    }

    public function testAddressNumberRemoveComma()
    {
        $this->andress->setNumber('12,3,4,5,6');
        $this->assertEquals('123456', $this->andress->getNumber());
    }
}
