<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use PagarmeCoreApiLib\Models\GetTdsTokenResponse;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;

class TdsTokenService
{
    /**
     * @var CoreAuth
     */
    private $coreAuth;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->coreAuth = new CoreAuth('');
        $this->config = $config;
    }

    /**
     * @param string $accountId
     * @return string
     */
    public function getTdsToken($accountId)
    {
        $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
        $environment = 'live';
        if ($this->config->getIsSandboxMode()) {
            $environment = 'test';
        }
        return $tdsTokenProxy->getTdsToken($environment, $accountId)->tdsToken;
    }
}
