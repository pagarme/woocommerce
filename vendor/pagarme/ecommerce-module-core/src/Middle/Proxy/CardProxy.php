<?php

namespace Pagarme\Core\Middle\Proxy;

use Pagarme\Core\Middle\Client;
use Pagarme\Core\Middle\Model\Card;

/**
 * This class is responsible for communicating with PagarmeCoreApi
 */
class CardProxy
{
    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
    {
        $this->client = $auth->services();
    }

    public function createCard(Card $card, $customerId)
    {
        $response = $this->client->getCustomers()->createCard(
            $customerId,
            $card->convertToSdk()
        );
        return $response;
    }

    public function getCards($customerId)
    {
        $response = $this->client->getCustomers()->getCards(
            $customerId
        );
        return $response;
    }

    public function getCard($customerId, Card $card)
    {
        $response = $this->client->getCustomers()->getCard(
            $customerId,
            $card->getCardId()
        );
        return $response;
    }
}
