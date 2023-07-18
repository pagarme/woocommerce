<?php

namespace Pagarme\Core\Middle\Model;

use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Aggregates\Customer as CustomerLegacy;
use Pagarme\Core\Middle\Interfaces\ConvertToLegacyInterface;
use PagarmeCoreApiLib\Models\CreateCustomerRequest;

class Customer implements ConvertToLegacyInterface
{

    public const INDIVIDUAL = 'individual';
    public const COMPANY = 'company';
    private $code;
    private $pagarmeId;
    private $email;
    private $name;
    private $document;
    /**
     * @var Pagarme\Core\Middle\Model\Customer\Address
     */
    private $address;
    /**
     * @var Pagarme\Core\Middle\Model\Customer\Phones
     */
    private $phones;

    public function getType()
    {
        return $this->getCustomerTypeByDocument($this->getDocument());
    }

    public function getDocumentType()
    {
        if ($this->getCustomerTypeByDocument($this->getDocument()) === self::INDIVIDUAL) {
            return 'cpf';
        }
        return 'cnpj';
    }

    public function getCustomerTypeByDocument($document)
    {
        if(strlen($document) === 11) {
            return self::INDIVIDUAL;
        }
        return self::COMPANY;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getPagarmeId()
    {
        return $this->pagarmeId;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getAddress()
    {
        return $this->address;
    }
    
    public function getPhones()
    {
        return $this->phones;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function setPagarmeId($pagarmeId)
    {
        $this->pagarmeId = $pagarmeId;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDocument($document)
    {
        $this->document = preg_replace('/\D/', '', $document);
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function setPhones($phones)
    {
        $this->phones = $phones;
    }

    public function convertToLegacy()
    {
        $legacy = new CustomerLegacy();
        $legacy->setCode($this->getCode());
        $legacy->setPagarmeId(new CustomerId($this->getPagarmeId()));
        return $legacy;
    }

    public function convertToSdk()
    {
        $customer = new CreateCustomerRequest();
        $customer->name = $this->getName();
        $customer->email = $this->getEmail();
        $customer->document = $this->getDocument();
        $customer->type = $this->getType();
        $customer->address = $this->getAddress();
        $customer->phones = $this->getPhones();
        $customer->code = $this->getCode();
        $customer->documentType = $this->getDocumentType();
        return $customer;
    }
}