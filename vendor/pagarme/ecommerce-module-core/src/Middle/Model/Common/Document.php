<?php

namespace Pagarme\Core\Middle\Model\Common;

class Document
{
    private $documentNumber;

    public function __construct(
        $documentNumber
    ) {
        $this->setDocumentNumber($documentNumber);
    }

    private function setDocumentNumber($document)
    {
        $this->documentNumber = $document;
    }
    public function getDocumentWithoutMask()
    {
        return preg_replace("/\D/" , "",$this->documentNumber);
    }

}
