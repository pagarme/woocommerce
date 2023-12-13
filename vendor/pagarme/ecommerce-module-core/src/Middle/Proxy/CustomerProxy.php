<?php

namespace Pagarme\Core\Middle\Proxy;

use Pagarme\Core\Middle\Client;

class CustomerProxy
{

    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
    {
        $this->client = $auth->services();
    }

    public function createCustomer($customer)
    {
        $response = $this->client->getCustomers()->createCustomer(
            $customer
        );
        return $response;
    }

}
