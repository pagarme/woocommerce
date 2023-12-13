<?php

namespace Pagarme\Core\Payment\ValueObjects;


use PagarmeCoreApiLib\Models\CreatePhoneRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;
use Pagarme\Core\Kernel\ValueObjects\NumericString;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class Phone extends AbstractValueObject implements ConvertibleToSDKRequestsInterface
{
    /** @var NumericString */
    private $countryCode;
    /** @var NumericString */
    private $areaCode;
    /** @var NumericString */
    private $number;

    /**
     * Phone constructor.
     * @param string $phone
     */
    public function __construct($phone)
    {
        $phone = preg_replace('/(?!\d)./', '', $phone ?? '');
        $phone = sprintf("%05s", $phone);

        $this->countryCode = new NumericString(55);
        $this->areaCode = new NumericString(substr($phone, 0, 2));
        $this->number = new NumericString(substr($phone, 2, 11));
    }

    /**
     * @return NumericString
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return NumericString
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }

    /**
     * @return NumericString
     */
    public function getNumber()
    {
        return $this->number;
    }

    public function getFullNumber()
    {
        return
            $this->countryCode->getValue() .
            $this->areaCode->getValue() .
            $this->number->getValue();
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param Phone $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getCountryCode()->equals($object->getCountryCode()) &&
            $this->getAreaCode()->equals($object->getAreaCode()) &&
            $this->getNumber()->equals($object->getNumber())
        ;
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

        $obj->countryCode = $this->getCountryCode();
        $obj->areaCode = $this->getAreaCode();
        $obj->number = $this->getNumber();

        return $obj;
    }

    /**
     * @return CreatePhoneRequest
     */
    public function convertToSDKRequest()
    {
        return new CreatePhoneRequest(
            $this->getCountryCode()->getValue(),
            $this->getNumber()->getValue(),
            $this->getAreaCode()->getValue()
        );
    }
}
