<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

class BasePersonInformation extends BaseRegisterInformation
{
    private $name;
    private $motherName;
    private $birthdate;
    private $monthlyIncome;
    private $professionalOccupation;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setMotherName($motherName)
    {
        if(empty($motherName)) {
            return;
        }
        $this->motherName = $motherName;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function setMonthlyIncome($monthlyIncome)
    {
        if($monthlyIncome < 0) {
            throw new \InvalidArgumentException("Monthly income cannot be negative");
        }
        $this->monthlyIncome = $monthlyIncome;
    }

    public function setProfessionalOccupation($professionalOccupation)
    {
        $this->professionalOccupation = $professionalOccupation;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getMotherName()
    {
        return $this->motherName;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function getMonthlyIncome()
    {
        return $this->monthlyIncome;
    }

    public function getProfessionalOccupation()
    {
        return $this->professionalOccupation;
    }
}
