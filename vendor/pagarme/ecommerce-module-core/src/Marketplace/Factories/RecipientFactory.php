<?php

namespace Pagarme\Core\Marketplace\Factories;

use PagarmeCoreApiLib\Controllers\BaseController;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
use Pagarme\Core\Marketplace\Aggregates\Recipient;

class RecipientFactory implements FactoryInterface
{
    /** @var bool */
    const TYPE_BY_DOCUMENT = true;

    /**
     * @var Recipient
     */
    protected $recipient;

    public function __construct()
    {
        $this->recipient = new Recipient();
    }

    public function createFromPostData($postData)
    {
        if (!is_array($postData)) {
            return;
        }

        $this->setId($postData);
        $this->setRecipientId($postData);
        $this->setExternalId($postData);
        $this->setName($postData);
        $this->setEmail($postData);
        $this->setDocumentType($postData);
        $this->setDocument($postData);
        $this->setType($postData, self::TYPE_BY_DOCUMENT);
        $this->setHolderName($postData);
        $this->setHolderDocument($postData);
        $this->setHolderType($postData);
        $this->setBank($postData);
        $this->setBranchNumber($postData);
        $this->setBranchCheckDigit($postData);
        $this->setAccountNumber($postData);
        $this->setAccountCheckDigit($postData);
        $this->setAccountType($postData);
        $this->setTransferEnabled($postData);
        $this->setTransferInterval($postData);
        $this->setTransferDay($postData);

        return $this->recipient;
    }

    public function createFromDbData($dbData)
    {
        if (!is_array($dbData)) {
            return;
        }

        $this->recipient->setId($dbData['id'])
            ->setExternalId($dbData['external_id'])
            ->setName($dbData['name'])
            ->setEmail($dbData['email'])
            ->setDocumentType($dbData['document_type'])
            ->setDocument($dbData['document'])
            ->setType($dbData['document_type'] == 'cpf' ? 'individual' : 'company')
            ->setPagarmeId(new RecipientId($dbData['pagarme_id']));

        if (self::TYPE_BY_DOCUMENT) {
            $this->recipient->setType($this->getTypeByDocument($this->recipient->getDocument()));
        }

        $this->setCreatedAt($dbData);
        $this->setUpdatedAt($dbData);


        return $this->recipient;
    }

    private function getTypeByDocument($document)
    {
        if ($document) {
            $document = preg_replace("/[^0-9]/", "", $document ?? '');
            return strlen($document) > 11 ? 'company' : 'individual';
        }
    }

    private function setId($postData)
    {
        if (array_key_exists('id', $postData)) {
            $this->recipient->setId($postData['id']);
            return;
        }
    }

    private function setExternalId($postData)
    {
        if (array_key_exists('external_id', $postData)) {
            $this->recipient->setExternalId($postData['external_id']);
            return;
        }
    }

    private function setName($postData)
    {
        if (array_key_exists('name', $postData)) {
            $this->recipient->setName($postData['name']);
            return;
        }
    }

    private function setEmail($postData)
    {
        if (array_key_exists('email', $postData)) {
            $this->recipient->setEmail($postData['email']);
            return;
        }
    }

    private function setDocumentType($postData)
    {
        if (array_key_exists('document_type', $postData)) {
            $this->recipient->setDocumentType($postData['document_type']);
        }
        return;
    }

    private function setDocument($postData)
    {
        if (array_key_exists('document', $postData)) {
            $this->recipient->setDocument($postData['document']);
            return;
        }
    }

    private function setType($postData , $byDocument = false)
    {
        if (array_key_exists('type', $postData)) {
            $this->recipient->setType($postData['type']);
        }
        if ($byDocument && array_key_exists('document', $postData)) {
            $this->recipient->setType($this->getTypeByDocument($postData['document']));
        }
        return;
    }

    private function setHolderName($postData)
    {
        if (array_key_exists('holder_name', $postData)) {
            $this->recipient->setHolderName($postData['holder_name']);
            return;
        }
    }

    private function setHolderDocument($postData)
    {
        if (array_key_exists('holder_document', $postData)) {
            $this->recipient->setHolderDocument($postData['holder_document']);
            return;
        }
    }

    private function setHolderType($postData)
    {
        if (array_key_exists('holder_type', $postData)) {
            $this->recipient->setHolderType($postData['holder_type']);
            return;
        }
    }

    private function setBank($postData)
    {
        if (array_key_exists('bank', $postData)) {
            $this->recipient->setBank($postData['bank']);
            return;
        }
    }

    private function setBranchNumber($postData)
    {
        if (array_key_exists('branch_number', $postData)) {
            $this->recipient->setBranchNumber($postData['branch_number']);
            return;
        }
    }

    private function setBranchCheckDigit($postData)
    {
        if (array_key_exists('branch_check_digit', $postData)) {
            $this->recipient->setBranchCheckDigit($postData['branch_check_digit']);
            return;
        }
    }

    private function setAccountNumber($postData)
    {
        if (array_key_exists('account_number', $postData)) {
            $this->recipient->setAccountNumber($postData['account_number']);
            return;
        }
    }

    private function setAccountCheckDigit($postData)
    {
        if (array_key_exists('account_check_digit', $postData)) {
            $this->recipient->setAccountCheckDigit($postData['account_check_digit']);
            return;
        }
    }

    private function setAccountType($postData)
    {
        if (array_key_exists('account_type', $postData)) {
            $this->recipient->setAccountType($postData['account_type']);
            return;
        }
    }

    private function setTransferEnabled($postData)
    {
        if (array_key_exists('transfer_enabled', $postData)) {
            $this->recipient->setTransferEnabled($postData['transfer_enabled']);
            return;
        }
    }

    private function setTransferInterval($postData)
    {
        if (array_key_exists('transfer_interval', $postData)) {
            $this->recipient->setTransferInterval($postData['transfer_interval']);
            return;
        }
    }

    private function setTransferDay($postData)
    {
        if (array_key_exists('transfer_day', $postData)) {
            $this->recipient->setTransferDay($postData['transfer_day']);
            return;
        }
    }

    private function setRecipientId($postData)
    {
        if (array_key_exists('recipient_id', $postData)) {
            $this->recipient->setPagarmeId(new RecipientId($postData['recipient_id']));
            return;
        }
    }

    private function setUpdatedAt($postData)
    {
        if (isset($postData['updated_at'])) {
            $this->recipient->setUpdatedAt(
                new \Datetime($postData['updated_at'])
            );
            return;
        }
    }

    private function setCreatedAt($postData)
    {
        if (isset($postData['created_at'])) {
            $this->recipient->setCreatedAt(
                new \Datetime($postData['created_at'])
            );
            return;
        }
    }
}
