<?php

namespace Pagarme\Core\Middle;

use PagarmeCoreApiLib\PagarmeCoreApiClient;
use PagarmeCoreApiLib\Configuration;

/**
 * This class is responsible for authentication.
 */
abstract class Client
{
    const BASE_URI = 'https://hubapi.pagar.me/';
    const DEFAULT_RESOURCE = 'core/v1';

    public $client;

    abstract public function getHubToken();
    public function __construct($resource = self::DEFAULT_RESOURCE)
    {
        Configuration::$basicAuthPassword = '';
        Configuration::$BASEURI = self::BASE_URI . $resource;
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
