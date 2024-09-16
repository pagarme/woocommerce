<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

class ManagingPartner extends BasePersonInformation
{
    private $selfDeclaredLegalRepresentative;

    public function setSelfDeclaredLegalRepresentative($selfDeclaredLegalRepresentative): void
    {
        $this->selfDeclaredLegalRepresentative = $selfDeclaredLegalRepresentative;
    }

    public function getSelfDeclaredLegalRepresentative()
    {
        return $this->selfDeclaredLegalRepresentative;
    }

    public function convertToArray()
    {
        return array(
            'type' => $this->getType(),
            'document' => $this->getDocumentNumber(),
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'mother_name' => $this->getMotherName(),
            'phone_numbers' => $this->getPhoneNumbers(),
            'birthdate' => $this->getBirthdate(),
            'monthly_income' => $this->getMonthlyIncome(),
            'professional_occupation' => $this->getProfessionalOccupation(),
            'address' => $this->getAddress(),
            'self_declared_legal_representative' => $this->getSelfDeclaredLegalRepresentative()
        );
    }
}
