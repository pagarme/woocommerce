<?php


namespace Pagarme\Core\Test\Payment;

use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use PHPUnit\Framework\TestCase;

class CustomerTests extends TestCase
{
    /**
     * @var Customer
     */
    private $customer;

    public function setUp()
    {
        $this->customer = new Customer();
    }

    public function testBuildCustomerObject()
    {
        $this->customer->setCode(2);
        $this->customer->setPagarmeId(new CustomerId('cus_K7dJ521DiETZnjM4'));
        $this->customer->setName("teste teste sobrenome");
        $this->customer->setEmail("teste@teste.com");
        $this->customer->setDocument("76852559017");
        $this->customer->setType(CustomerType::individual());


        $this->assertEquals(2, $this->customer->getCode());
    }
}
