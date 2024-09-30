<?php

namespace Pagarme\Core\Middle\Model;

use PagarmeCoreApiLib\Models\CreateRecipientRequest;

class Recipient
{

    const INDIVIDUAL = 'individual';
    const CORPORATION = 'corporation';

    private $bankAccount;
    private $transferSettings;
    private $automaticAnticipationSettings;
    private $registerInformation;
    private $code;

    public function setBankAccount($bankAccount): void
    {
        $this->bankAccount = $bankAccount;
    }


    public function setRegisterInformation($registerInformation): void
    {
        $this->registerInformation = $registerInformation;
    }

    public function setTransferSettings($transferSettings): void
    {
        $this->transferSettings = $transferSettings;
    }

    public function setAutomaticAnticipationSettings($automaticAnticipationSettings): void
    {
        $this->automaticAnticipationSettings = $automaticAnticipationSettings;
    }

    public function setCode($code): void
    {
        $this->code = $code;
    }

    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    public function getTransferSettings()
    {
        return $this->transferSettings;
    }

    public function getAutomaticAnticipationSettings()
    {
        return $this->automaticAnticipationSettings;
    }

    public function getRegisterInformation()
    {
        return $this->registerInformation;
    }

    public function getCode()
    {
        return $this->code;
    }
    
    public function convertToCreateRequest()
    {
        return new CreateRecipientRequest(
            $this->getBankAccount(),
            $this->getTransferSettings(),
            $this->getAutomaticAnticipationSettings(),
            $this->getRegisterInformation(),
            $this->getCode()
        );
    }
}
