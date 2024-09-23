<?php

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

class CreateRegisterInformationBaseRequest implements JsonSerializable
{
    /**
     * Email
     * @required
     * @maps email
     * @var string $email public property
     */
    public $email;

    /**
     * Document
     * @required
     * @maps document
     * @var string $document public property
     */
    public $document;

    /**
     * Type
     * @required
     * @maps type
     * @var string $type public property
     */
    public $type;

    /**
     * Site Url
     * @required
     * @maps site_url
     * @var string|null $siteUrl public property
     */
    public $siteUrl;

    /**
     * Phone Numbers
     * @required
     * @maps phone_numbers
     * @var CreateRegisterInformationPhoneRequest $phoneNumbers public property
     */
    public $phoneNumbers;

    /**
     * @param string $email
     * @param string $document
     * @param string $type
     * @param string|null $siteUrl
     * @param CreateRegisterInformationPhoneRequest[] $phoneNumbers
     */
    public function __construct(
        $email,
        $document,
        $type,
        $siteUrl,
        $phoneNumbers
    ) {
        $this->email = $email;
        $this->document = $document;
        $this->type = $type;
        $this->siteUrl = $siteUrl;
        $this->phoneNumbers = $phoneNumbers;
    }


    /**
     * Encode this object to JSON
     * @return array
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize()
    {
        $json = [];
        $json['email']         = $this->email;
        $json['document']      = $this->document;
        $json['type']          = $this->type;
        $json['site_url']      = $this->siteUrl;
        $json['phone_numbers'] = $this->phoneNumbers;

        return $json;
    }
}
