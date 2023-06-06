<?php

namespace Woocommerce\Pagarme\Model;

if (!defined('ABSPATH')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;

class Account
{
    private $config;

    const WALLET_ENDPOINT = 'zff3yg2have4pcw';

    public function __construct()
    {
        $this->config = new Config();
    }
}
