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
     * Retrieves the TDS token for the 3DS-NX (AuthSwitch) flow.
     *
     * Returns null when the account is not enabled for NX, so the controller can
     * answer flow_preference 'legacy'. API failures are thrown and handled by the
     * controller, which logs them and also falls back to the legacy flow.
     *
     * @param string $accountId
     * @return string|null
     * @throws \Exception
     */
    public function getTdsTokenNx($accountId)
    {
        try {
            $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
            $environment = $this->config->getIsSandboxMode() ? 'test' : 'live';

            $response = $tdsTokenProxy->getTdsTokenNx($environment, $accountId);

            if (!$response || empty($response->tdsToken)) {
                return null;
            }

            return $response->tdsToken;
        } catch (\Throwable $e) {
            throw new \Exception(
                'Failed to retrieve the 3DS-NX token: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
