<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

use InvalidArgumentException;
use Pagarme\Core\Middle\Model\Recipient;

class BaseRegisterInformation
{
    private $type;
    private $documentNumber;
    private $email;
    private $siteUrl;
    private $phoneNumbers;
    private $address;

    public function setType($type)
    {
        if ($type !== Recipient::CORPORATION && $type !== Recipient::INDIVIDUAL) {
            throw new InvalidArgumentException("Type is not valid");
        }
        $this->type = $type;
    }

    public function setDocumentNumber($documentNumber)
    {
        $this->documentNumber = $documentNumber;
    }

    public function setEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid e-mail');
        }
        $this->email = $email;
    }

    public function setSiteUrl($siteUrl)
    {
        if (empty($siteUrl)) {
            return;
        }
        if (!filter_var($siteUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Site Url is not valid!");
        }
        $this->siteUrl = $siteUrl;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function addPhoneNumbers($phoneNumbers)
    {
        $this->phoneNumbers[] = $phoneNumbers;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getPhoneNumbers()
    {
        return $this->phoneNumbers;
    }

    public function getDocumentNumber()
    {
        return $this->documentNumber;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    public function getAddress()
    {
        return $this->address;
    }
}
