<?php

namespace Pagarme\Core\Middle\Service;

use Pagarme\Core\Middle\Client;
use PagarmeCoreApiLib\PagarmeCoreApiClient;
use Pagarme\Core\Middle\Model\Card;

/**
 * This class is responsible for communicating with PagarmeCoreApi
 */
class CardService
{
    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
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


    public function getCard($customer, Card $card)
    {
        $response = $this->client->getCustomers()->getCard(
            $customer->getPagarmeCustomerId(),
            $card->getCardId()
        );
        return $response;
    }
}
