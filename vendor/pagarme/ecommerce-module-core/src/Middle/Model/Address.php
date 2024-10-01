<?php

namespace Pagarme\Core\Middle\Model;

use PagarmeCoreApiLib\Models\CreateRegisterInformationAddressRequest;

class Address
{
    private $zipCode;
    private $street;
    private $street_number;
    private $complementary;
    private $referencePoint;
    private $neighborhood;
    private $state;
    private $city;

    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;
    }

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function setStreetNumber($street_number)
    {
        $this->street_number = $street_number;
    }

    public function setComplementary($complementary)
    {
        $this->complementary = empty($complementary) ? "Nenhum" : $complementary;
    }

    public function setReferencePoint($referencePoint)
    {
        $this->referencePoint = empty($referencePoint) ? "Nenhum" : $referencePoint;
    }

    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function getStreetNumber()
    {
        return $this->street_number;
    }

    public function getComplementary()
    {
        return $this->complementary;
    }

    public function getReferencePoint()
    {
        return $this->referencePoint;
    }

    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function convertToArray()
    {
        return array(
            'zip_code' => $this->getZipCode(),
            'street' => $this->getStreet(),
            'street_number' => $this->getStreetNumber(),
            'complementary' => $this->getComplementary(),
            'reference_point' => $this->getReferencePoint(),
            'neighbordhood' => $this->getNeighborhood(),
            'state' => $this->getState(),
            'city' => $this->getCity()
        );
    }

    public function convertToCreateRegisterInformationAddressRequest()
    {
        return new CreateRegisterInformationAddressRequest(
            $this->getStreet(),
            $this->getComplementary(),
            $this->getStreetNumber(),
            $this->getNeighborhood(),
            $this->getCity(),
            $this->getState(),
            $this->getZipCode(),
            $this->getReferencePoint()
        );
    }
}
