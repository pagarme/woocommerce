<?php

namespace Pagarme\Core\Payment\Aggregates;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use PagarmeCoreApiLib\Models\CreateCustomerRequest;
use stdClass;

final class Customer extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    /** @var null|string */
    private $code;
    /** @var string */
    private $name;
    /** @var string */
    private $email;
    /** @var CustomerPhones */
    private $phones;
    /** @var string */
    private $document;
    /** @var CustomerType */
    private $type;
    /** @var Address */
    private $address;

    /** @var LocalizationService */
    protected $i18n;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     */
    public function setCode($code)
    {
        $this->code = substr($code ?? "", 0, 52);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;

    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = substr($name ?? "", 0, 64);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Customer
     * @throws Exception
     */
    public function setEmail($email)
    {
        $email = trim($email);
        $email = substr($email ?? "", 0, 64);

        $this->validateEmail($email);
        $this->email = $email;

        return $this;
    }

    /**
     * @return CustomerPhones
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param CustomerPhones $phones
     */
    public function setPhones(CustomerPhones $phones)
    {
        $this->phones = $phones;
    }

    /**
     * @return string
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param string $document
     *
     * @return Customer
     * @throws Exception
     */
    public function setDocument($document)
    {
        $this->document = $this->formatDocument($document);

        if (empty($this->document) && empty($this->getPagarmeId())) {

            $inputName = $this->i18n->getDashboard('document');
            $message   = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new Exception($message, 400);
        }

        return $this;
    }

    /**
     * @return CustomerType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param CustomerType $type
     */
    public function setType(CustomerType $type)
    {
        $this->type = $type;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(?Address $address)
    {
        $this->address = $address;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new stdClass();

        $code = $this->code;
        if ($code !== null) {
            $obj->code = $code;
        }

        $obj->name      = $this->name;
        $obj->email     = $this->email;
        $obj->phones    = $this->phones;
        $obj->document  = $this->document;
        $obj->type      = $this->type;
        $obj->address   = $this->address;
        $obj->pagarmeId = $this->getPagarmeId();

        return $obj;
    }

    public function getTypeValue()
    {
        if ($this->getType() !== null) {
            return $this->getType()->getType();
        }

        return null;
    }

    public function getAddressToSDK()
    {
        if ($this->getAddress() !== null) {
            return $this->getAddress()->convertToSDKRequest();
        }

        return null;
    }

    public function getPhonesToSDK()
    {
        if ($this->getPhones() !== null) {
            return $this->getPhones()->convertToSDKRequest();
        }

        return null;
    }

    public function convertToSDKRequest()
    {
        $customerRequest = new CreateCustomerRequest();

        $customerRequest->code     = $this->getCode();
        $customerRequest->name     = $this->getName();
        $customerRequest->email    = $this->getEmail();
        $customerRequest->document = $this->getDocument();
        $customerRequest->type     = $this->getTypeValue();
        $customerRequest->address  = $this->getAddressToSDK();
        $customerRequest->phones   = $this->getPhonesToSDK();

        return $customerRequest;
    }

    private function validateEmail($email)
    {
        if (empty($email)) {
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                "email"
            );

            throw new Exception($message, 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = $this->i18n->getDashboard(
                "The %s is invalid!",
                "email"
            );

            throw new Exception($message, 400);
        }
    }

    private function formatDocument($document)
    {
        $document = preg_replace(
            '/[^0-9]/is', '',
            substr($document ?? "", 0, 16)
        );

        return $document;
    }
}
