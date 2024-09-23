<?php

namespace Pagarme\Core\Middle\Model;
use PagarmeCoreApiLib\Models\CreateRegisterInformationPhoneRequest;

class Phones
{
    private $typePhone;
    private $phoneNumber;
    private $areaCode;

    public function __construct($typePhone, $phoneNumber, $areaCode = null)
    {
        $this->typePhone    = $typePhone;
        $this->phoneNumber  = $phoneNumber;
        $this->areaCode     = $areaCode;
        if($areaCode === null) {
            $this->populateAreaCodeAndPhoneByString($phoneNumber);
        }
    }

    public function populateAreaCodeAndPhoneByString($phoneNumber)
    {
        $phone = $this->cleanPhone($phoneNumber);
        $this->areaCode = substr($phone, 0, 2);
        $this->phoneNumber = substr($phone, 2, 11);
    }

    private function cleanPhone($phoneNumber)
    {
        return preg_replace('/\D/', '', $phoneNumber);;
    }


    public function getTypePhone()
    {
        return $this->typePhone;
    }
    public function getAreaCode()
    {
        return $this->areaCode;
    }
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function convertToArray()
    {
        return array(
            'type' => $this->getTypePhone(),
            'ddd' => $this->getAreaCode(),
            'number' => $this->getPhoneNumber()
        );
    }

    public function convertToRegisterInformationPhonesRequest()
    {
        return new CreateRegisterInformationPhoneRequest(
            $this->getAreaCode(),
            $this->getPhoneNumber(),
            $this->getTypePhone()
        );
    }
}
