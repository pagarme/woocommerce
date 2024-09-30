<?php

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

class CreateRegisterInformationIndividualRequest extends CreateRegisterInformationBaseRequest implements JsonSerializable
{
    /**
     * Name
     * @required
     * @maps name
     * @var string $name public property
     */
    public $name;

    /**
     * Mother Name
     * @maps mother_name
     * @var string $motherName public property
     */
    public $motherName;

    /**
     * Birthdate
     * @required
     * @maps birthdate
     * @var string $birthdate public property
     */
    public $birthdate;

    /**
     * Monthly Income
     * @required
     * @maps monthly_income
     * @var int $monthlyIncome public property
     */
    public $monthlyIncome;

    /**
     * Professional Occupation
     * @required
     * @maps professional_occupation
     * @var string $professionalOccupation public property
     */
    public $professionalOccupation;

    /**
     * Address
     * @required
     * @maps address
     * @var CreateRegisterInformationAddressRequest $address public property
     */
    public $address;

    /**
     * @param string $email
     * @param string $document
     * @param string $type
     * @param string $siteUrl
     * @param CreateRegisterInformationPhoneRequest[] $phoneNumbers
     * @param string $name
     * @param string|null $motherName
     * @param string $birthdate
     * @param int $monthlyIncome
     * @param string $professionalOccupation
     * @param CreateRegisterInformationAddressRequest $address
     */
    public function __construct(
        $email,
        $document,
        $type,
        $siteUrl,
        $phoneNumbers,
        $name,
        $motherName,
        $birthdate,
        $monthlyIncome,
        $professionalOccupation,
        CreateRegisterInformationAddressRequest $address
    ) {
        parent::__construct($email, $document, $type, $siteUrl, $phoneNumbers);
        $this->name = $name;
        $this->motherName = $motherName;
        $this->birthdate = $birthdate;
        $this->monthlyIncome = $monthlyIncome;
        $this->professionalOccupation = $professionalOccupation;
        $this->address = $address;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize()
    {
        $json = [];
        $json['name']                    = $this->name;
        $json['mother_name']             = $this->motherName;
        $json['birthdate']               = $this->birthdate;
        $json['monthly_income']          = $this->monthlyIncome;
        $json['professional_occupation'] = $this->professionalOccupation;
        $json['address']                 = $this->address;
        $json = array_merge($json, parent::jsonSerialize());

        return $json;
    }
}
