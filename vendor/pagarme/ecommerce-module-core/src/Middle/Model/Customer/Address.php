<?php

namespace Pagarme\Core\Middle\Model\Customer;

use Pagarme\Core\Middle\Interfaces\ConvertToLegacyInterface;
use Pagarme\Core\Payment\Aggregates\Address as LegacyAddress;
use PagarmeCoreApiLib\Models\CreateAddressRequest;

class Address implements ConvertToLegacyInterface
{

    private const ADDRESS_SEPARATOR = ', ';
    private $country;
    private $state;
    private $city;
    private $neighborhood;
    private $zipCode;
    private $street;
    private $number;
    private $complement;

    public function setCountry($country)
    {
        $this->country = $country;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;
    }

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function setComplement($complement)
    {
        $this->complement = $complement;
    }

    public function getCountry()
    {
        return $this->country ?? "";
    }

    public function getState()
    {
        return $this->state ?? "";
    }

    public function getCity()
    {
        return $this->city ?? "";
    }

    public function getNeighborhood()
    {
        return $this->neighborhood ?? "";
    }

    public function getZipCode()
    {
        return $this->zipCode ?? "";
    }

    public function getStreet()
    {
        return $this->street ?? "";
    }

    public function getNumber()
    {
        return $this->number ?? "";
    }
    
    public function getComplement()
    {
        return $this->complement ?? "";
    }
    
    public function getLine1()
    {
        $address = [$this->getNumber(), $this->getStreet(), $this->getNeighborhood()];
        return implode(self::ADDRESS_SEPARATOR, $address);
    }

    public function getLine2()
    {
        return $this->getComplement() ?? "";
    }


    /**
     * When it is no longer needed (Magento and Woocommerce) remove implementation and interface.
     *
     * @return \Pagarme\Core\Payment\Aggregates\Address
     */
    public function convertToLegacy()
    {
        $legacy = new LegacyAddress();
        $legacy->setCountry($this->getCountry());
        $legacy->setState($this->getState());
        $legacy->setCity($this->getCity());
        $legacy->setNeighborhood($this->getNeighborhood());
        $legacy->setZipCode($this->getZipCode());
        $legacy->setStreet($this->getStreet());
        $legacy->setNumber($this->getNumber());
        $legacy->setComplement($this->getComplement());
        return $legacy;
    }

    public function convertToSdk()
    {
        $address = new CreateAddressRequest();
        $address->country = $this->getCountry();
        $address->state = $this->getState();
        $address->city = $this->getCity();
        $address->zipCode = $this->getZipCode();
        $address->line1 = $this->getLine1();
        $address->line2 = $this->getLine2();
        return $address;
    }
}
