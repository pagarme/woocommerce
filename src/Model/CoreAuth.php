<?php

namespace Woocommerce\Pagarme\Model;

use Pagarme\Core\Middle\Client;
use Woocommerce\Pagarme\Model\Config;

class CoreAuth extends Client
{
    public function getHubToken()
    {
        $config = new Config;
        return $config->getSecretKey();
    }
    
}