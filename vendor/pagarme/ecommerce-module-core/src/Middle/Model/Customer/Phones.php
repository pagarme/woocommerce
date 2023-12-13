<?php

namespace Pagarme\Core\Middle\Model\Customer;

use Pagarme\Core\Payment\ValueObjects\Phone;
use PagarmeCoreApiLib\Models\CreatePhoneRequest;
use PagarmeCoreApiLib\Models\CreatePhonesRequest;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Middle\Interfaces\ConvertToLegacyInterface;

class Phones implements ConvertToLegacyInterface
{
    private $homePhone;
    private $mobilePhone;
    private $countryCode = 55;

    public function setHomePhone($homePhone)
    {
        $this->homePhone = $this->createPhoneRequest($homePhone);
    }

    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = $this->createPhoneRequest($mobilePhone);
    }

    public function getHomePhone()
    {
        return $this->homePhone;
    }

    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }
    
    public function convertToLegacy()
    {
        $home = new Phone($this->getHomePhone());
        $mobile = new Phone($this->getMobilePhone());
        $legacy = new CustomerPhones(
            $home,
            $mobile
        );
        return $legacy;
    }

    public function convertToSdk()
    {
        $phones = new CreatePhonesRequest();
        $phones->homePhone = $this->getHomePhone();
        $phones->mobilePhone = $this->getMobilePhone();
        return $phones;
    }

    private function cleanInput($value)
    {
        $value = preg_replace('/(?!\d)./', '', $value);
        return sprintf("%05s", $value);
    }
    
    private function createPhoneRequest($phone)
    {
        $phone = $this->cleanInput($phone);
        $areaCode = substr($phone, 0, 2);
        $number = substr($phone, 2, 11);
        return new CreatePhoneRequest($this->countryCode, $number, $areaCode);
    }
}