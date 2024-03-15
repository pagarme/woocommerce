<?php

namespace Pagarme\Core\Middle\Proxy;

use Pagarme\Core\Middle\Client;

class TdsTokenProxy
{

    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
    {
        $this->client = $auth->services();
    }

    public function getTdsToken($environment, $accountId)
    {
        return $this->client->getTdsToken()->getToken(
            $environment,
            $accountId
        );
    }

}
