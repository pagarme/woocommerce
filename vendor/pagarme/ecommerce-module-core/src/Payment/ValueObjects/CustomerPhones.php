<?php

namespace Pagarme\Core\Payment\ValueObjects;

use PagarmeCoreApiLib\Models\CreatePhonesRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class CustomerPhones extends AbstractValueObject implements ConvertibleToSDKRequestsInterface
{
    /** @var Phone */
    private $home;
    /** @var Phone */
    private $mobile;

    private function __construct(Phone $home, Phone $mobile)
    {
        $this->setHome($home);
        $this->setMobile($mobile);
    }

    private function setHome(Phone $home)
    {
        $this->home = $home;
    }

    private function setMobile(Phone $mobile)
    {
        $this->mobile =  $mobile;
    }

    /**
     * @return Phone
     */
    public function getHome()
    {
        return $this->home;
    }

    /**
     * @return Phone
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    public static function create($phones)
    {
        $phoneArray = $phones;
        if (!is_array($phones)) {
            $phoneArray[0] =
            $phoneArray[1] =
                $phones;
        }

        return new self(
            array_pop($phoneArray),
            array_pop($phoneArray)
        );
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param CustomerPhones $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getHome()->equals($object->getHome()) &&
            $this->getMobile()->equals($object->getMobile())
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

        $obj->home = $this->getHome();
        $obj->mobile = $this->getMobile();

        return $obj;
    }

    /**
     * @return CreatePhonesRequest
     */
    public function convertToSDKRequest()
    {
        $phonesRequest = new CreatePhonesRequest();
        $phonesRequest->homePhone = $this->getHome()->convertToSDKRequest();
        $phonesRequest->mobilePhone = $this->getMobile()->convertToSDKRequest();

        return $phonesRequest;
    }
}
