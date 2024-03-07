<?php

namespace Pagarme\Core\Payment\Aggregates;

use PagarmeCoreApiLib\Models\CreateShippingRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Pagarme\Core\Payment\Traits\WithAmountTrait;
use Pagarme\Core\Payment\ValueObjects\Phone;

final class Shipping extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;

    /** @var string */
    private $description;
    /** @var string */
    private $recipientName;
    /** @var Phone */
    private $recipientPhone;
    /** @var Address */
    private $address;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = substr($description, 0, 64);
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->recipientName;
    }

    /**
     * @param string $recipientName
     */
    public function setRecipientName($recipientName)
    {
        $this->recipientName = substr($recipientName, 0, 64);
    }

    /**
     * @return Phone
     */
    public function getRecipientPhone()
    {
        return $this->recipientPhone;
    }

    /**
     * @param Phone $recipientPhone
     */
    public function setRecipientPhone(Phone $recipientPhone)
    {
        $this->recipientPhone = $recipientPhone;
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
        $obj = new \stdClass();

        $obj->amount = $this->amount;
        $obj->description = $this->description;
        $obj->recipientName = $this->recipientName;
        $obj->recipientPhone = $this->recipientPhone;
        $obj->address = $this->address;

        return $obj;
    }

    /**
     * @return CreateShippingRequest
     */
    public function convertToSDKRequest()
    {
        $shippingRequest = new CreateShippingRequest();

        $shippingRequest->amount = $this->getAmount();
        $shippingRequest->description = $this->getDescription();
        $shippingRequest->recipientName = $this->getRecipientName();
        $shippingRequest->recipientPhone = $this->getRecipientPhone()
            ->getFullNumber();

        if ($this->getAddress() !== null) {
            $shippingRequest->address = $this->getAddress()->convertToSDKRequest();
        }

        return $shippingRequest;
    }
}
