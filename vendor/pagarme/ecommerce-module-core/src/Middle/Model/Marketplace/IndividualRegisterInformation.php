<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

use PagarmeCoreApiLib\Models\CreateRegisterInformationIndividualRequest;

class IndividualRegisterInformation extends BasePersonInformation
{
    public function convertToSDKRequest()
    {
        return new CreateRegisterInformationIndividualRequest(
            $this->getEmail(),
            $this->getDocumentNumber(),
            $this->getType(),
            $this->getSiteUrl(),
            $this->getPhoneNumbers(),
            $this->getName(),
            $this->getMotherName(),
            $this->getBirthdate(),
            $this->getMonthlyIncome(),
            $this->getProfessionalOccupation(),
            $this->getAddress()
        );
    }
}
