<?php

namespace Woocommerce\Pagarme\Model;

use Pagarme\Core\Mark1\Mark1Client;
use Woocommerce\Pagarme\Model\Config;
class CoreAuth extends Mark1Client
{
    public function getHubToken()
    {
        $config = new Config;
        return $config->getSecretKey();
    }
    
}