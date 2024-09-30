<?php

namespace Pagarme\Core\Middle\Proxy;

use Pagarme\Core\Middle\Client;
use Pagarme\Core\Middle\Model\Recipient;

class RecipientProxy
{

   
    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
    {
        $this->client = $auth->services();
    }

    public function create(Recipient $recipient)
    {
         /**
     * @var \PagarmeCoreApiLib\Controllers\RecipientsController 
     */
        $recipientRequest = $this->client->getRecipients()->createRecipient(
            $recipient->convertToCreateRequest()
        );
        return $recipientRequest;
    }

    public function getFromPagarme($recipientId)
    {
        $recipientRequest = $this->client->getRecipients()->getRecipient(
            $recipientId
        );
        return $recipientRequest;
    }

    public function createKycLink($recipientId)
    {
        $kycLinkResponse = $this->client->getRecipients()->createKycLink(
            $recipientId
        );
        return $kycLinkResponse;
    }
}
