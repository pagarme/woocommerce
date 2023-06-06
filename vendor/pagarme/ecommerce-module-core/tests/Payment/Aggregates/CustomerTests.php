<?php


namespace Pagarme\Core\Test\Payment;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
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

    public function setUp(): void
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

    public function testEmailTrim()
    {
        $this->customer->setCode(3);
        $this->customer->setEmail(' teste@teste.com ');
        $this->assertEquals('teste@teste.com', $this->customer->getEmail());
    }

    public function testEmailRemoveCharactersAfterMaxLength()
    {
        $emailMaxLength = 64;
        $newEmailLength = $emailMaxLength + 1;
        $customerEmail = "teste@gmail.com";
        $customerEmail = sprintf("%'a${newEmailLength}s", $customerEmail);

        $this->customer->setCode(4);
        $this->customer->setEmail($customerEmail);

        $this->assertEquals(
            $emailMaxLength, strlen($this->customer->getEmail())
        );
    }

    public function testDocumentSanitize()
    {
        $expectedDocument = "12345678910";
        $customerDocument = "123.456.789-10";

        $this->customer->setCode(5);
        $this->customer->setDocument($customerDocument);

        $this->assertEquals(
            $expectedDocument, $this->customer->getDocument()
        );
    }
}
