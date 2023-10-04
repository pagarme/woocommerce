<?php

namespace Pagarme\Core\Middle;

use PagarmeCoreApiLib\PagarmeCoreApiClient;
use PagarmeCoreApiLib\Configuration;

/**
 * This class is responsible for authentication.
 */
abstract class Client
{
    public $client;

    abstract public function getHubToken();
    public function __construct()
    {
        Configuration::$basicAuthPassword = '';
        Configuration::$BASEURI = 'https://hubapi.pagar.me/core/v1';
        $this->client = $this->services();
    }
    private function auth()
    {
        return new PagarmeCoreApiClient($this->getHubToken(), "");
    }

    public function services()
    {
        return $this->auth();
    }

}
