<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

use PagarmeCoreApiLib\Models\CreateRegisterInformationCorporationRequest;

class CorporationRegisterInformation extends BaseRegisterInformation
{
    private $companyName;
    private $corporationType;
    private $tradingName;
    private $annualRevenue;
    private $managingPartners = [];
    private $foundingDate;

    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    public function setCorporationType($corporationType)
    {
        $this->corporationType = $corporationType;
    }


    public function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;
    }

    public function setAnnualRevenue($annualRevenue)
    {
        $this->annualRevenue = $annualRevenue;
    }

    public function setFoundingDate($foundingDate)
    {
        $this->foundingDate = $foundingDate;
    }

    public function addManagingPartners($managingPartners)
    {
        $this->managingPartners[] = $managingPartners;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function getCorporationType()
    {
        return $this->corporationType;
    }

    public function getTradingName()
    {
        return $this->tradingName;
    }

    public function getAnnualRevenue()
    {
        return $this->annualRevenue;
    }

    public function getFoundingDate()
    {
        return $this->foundingDate;
    }

    public function getManagingPartners()
    {
        return $this->managingPartners;
    }

    public function convertToSDKRequest()
    {
        return new CreateRegisterInformationCorporationRequest(
            $this->getEmail(),
            $this->getDocumentNumber(),
            $this->getType(),
            $this->getSiteUrl(),
            $this->getPhoneNumbers(),
            $this->getCompanyName(),
            $this->getCorporationType(),
            $this->getTradingName(),
            $this->getAnnualRevenue(),
            $this->getFoundingDate(),
            $this->getManagingPartners(),
            $this->getAddress()
        );
    }
}
