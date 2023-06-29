<?php

namespace Pagarme\Core\Mark1\Service;

use Pagarme\Core\Mark1\Mark1Client;
use PagarmeCoreApiLib\PagarmeCoreApiClient;
use Pagarme\Core\Mark1\Model\Card;

/**
 * 
 */
class CardService
{
    private PagarmeCoreApiClient $client;

    /**
     * @param Mark1Client $auth
     */
    public function __construct(Mark1Client $auth)
    {
        $this->client = $auth->services();
    }


    public function createCard(Card $card, $customer)
    {
        $response = $this->client->getCustomers()->createCard(
            $customer->getPagarmeCustomerId(),
            $card->convertToSdk()
        );
        return $response;
    }

    public function getCards($customer)
    {
        $response = $this->client->getCustomers()->getCards(
            $customer->getPagarmeCustomerId
        );
        return $response;
    }


    public function getCard($customer, $card)
    {
        $response = $this->client->getCustomers()->getCard(
            $customer->getPagarmeCustomerId(),
            $card->getCardId()
        );
        return $response;
    }
}