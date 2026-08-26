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

    /**
     * Retrieves TDS token for 3DS-NX (AuthSwitch) flow.
     *
     * Currently uses the legacy getTdsToken() method since the core lib hasn't been
     * updated with getTdsTokenNx() yet. The 3DS-NX flow determination happens at the
     * controller level based on account configuration.
     *
     * If the account is not enabled for NX or if the service fails,
     * returns null to trigger fallback to legacy 3DS Handler.
     *
     * @param string $accountId
     * @return string|null
     */
    public function getTdsTokenNx($accountId)
    {
        try {
            $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
            $environment = $this->config->getIsSandboxMode() ? 'test' : 'live';

            $response = $tdsTokenProxy->getTdsToken($environment, $accountId);

            if (!$response || empty($response->tdsToken)) {
                return null;
            }

            return $response->tdsToken;
        } catch (\Throwable $e) {
            error_log(sprintf(
                'TDS-NX Token retrieval failed for account %s: %s',
                $accountId,
                $e->getMessage()
            ));
            return null;
        }
    }
}
