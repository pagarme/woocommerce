<?php

namespace Pagarme\Core\Middle;

use PagarmeCoreApiLib\PagarmeCoreApiClient;
use PagarmeCoreApiLib\Configuration;
use Pagarme\Core\Kernel\Services\ApiUrlService;

/**
 * This class is responsible for authentication.
 */
abstract class Client
{
    const DEFAULT_RESOURCE = 'core/v1';

    public $client;

    abstract public function getHubToken();
    public function __construct($resource = self::DEFAULT_RESOURCE)
    {
        Configuration::$basicAuthPassword = '';
        Configuration::$BASEURI = ApiUrlService::getHubApiUrl() . '/' . $resource;
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
