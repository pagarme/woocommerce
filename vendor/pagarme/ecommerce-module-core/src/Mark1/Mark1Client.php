<?php

namespace Pagarme\Core\Mark1;

use PagarmeCoreApiLib\PagarmeCoreApiClient;
use PagarmeCoreApiLib\Configuration;
abstract class Mark1Client 
{
    public PagarmeCoreApiClient $client;

    abstract public function getHubToken();
    public function __construct()
    {
        Configuration::$basicAuthPassword = '';
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
