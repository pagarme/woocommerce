<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use Pagarme\Core\Kernel\Services\LogService;
use PagarmeCoreApiLib\Models\GetTdsTokenResponse;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;

class TdsTokenService
{
    /** Engine do AuthSwitch / 3DS NX, que autentica via `window.tifa.init`. */
    const ENGINE_NX = 'nx';

    /** Engine do bundle 3DS legado, que autentica via `Script3ds.init3ds`. */
    const ENGINE_LEGACY = 'legacy';

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
     * O emissor do token define qual engine o frontend precisa usar: um token NX
     * não é aceito pela API do 3DS legado (e vice-versa), então as duas decisões
     * não podem ser tomadas de forma independente.
     *
     * @param string $accountId
     * @return array{token: string, engine: string}
     */
    public function getTdsToken($accountId)
    {
        $token = $this->getNxToken($accountId);
        if ($token) {
            return [
                'token' => $token,
                'engine' => self::ENGINE_NX,
            ];
        }

        return [
            'token' => $this->getLegacyToken($accountId),
            'engine' => self::ENGINE_LEGACY,
        ];
    }

    /**
     * @param string $accountId
     * @return string|null
    */
    private function getNxToken($accountId)
    {
        try {
            $url = 'https://hubapi.pagar.me/v2/management/tds-token';

            $response = wp_remote_post($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->config->getSecretKey() . ':'),
                    'Content-Type'  => 'application/json',
                ],
                /**
                 * O payload pede `merchant_id`, mas recebe o account id — é o que
                 * a conta de testes aceita hoje. Config expõe `getMerchantId()`
                 * separadamente; confirmar com o time do hubapi qual dos dois é o
                 * esperado antes de subir para produção.
                 */
                'body' => json_encode([
                    'merchant_id'  => $accountId
                ]),
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                return null;
            }

            $statusCode = wp_remote_retrieve_response_code($response);
            if ($statusCode !== 200) {
                return null;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            return $data['tds_token'] ?? null;
        } catch (\Throwable $e) {
            $log = new LogService('TdsTokenService', true);
            $log->error("Failed to fetch NX token: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * @param string $accountId
     * @return string
     */
    private function getLegacyToken($accountId)
    {
        $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
        $environment = $this->config->getIsSandboxMode() ? 'test' : 'live';
        return $tdsTokenProxy->getTdsToken($environment, $accountId)->tdsToken;
    }
}
