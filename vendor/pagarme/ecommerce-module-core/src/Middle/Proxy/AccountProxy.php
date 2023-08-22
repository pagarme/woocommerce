<?php

namespace Pagarme\Core\Middle\Proxy;

use Pagarme\Core\Middle\Client;

class AccountProxy
{

    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
    {
        $this->client = $auth->services();
    }

    public function getAccount($accountId)
    {
        $response = $this->client->getAccounts()->getAccountById(
            $accountId
        );
        return $response;
    }

}
