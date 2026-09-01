<?php

namespace WooCommerce\Pagarme\Controller;

use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Service\TdsTokenService;

class TdsToken
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = new Config;
        add_action('woocommerce_api_pagarme-tds-token', [$this, 'getTdsToken']);
    }

    public function getTdsToken()
    {
        $accountId = $this->config->getAccountId();
        $tdsTokenService = new TdsTokenService($this->config);
        $tdsToken = $tdsTokenService->getTdsToken($accountId, $this->getRequestedEngine());
        /**
         * O `engine` viaja junto com o token para que o checkout não precise
         * adivinhar qual SDK usar por feature detection: um token NX enviado ao
         * bundle legado é rejeitado com 401 no `pre-auth`.
         */
        wp_send_json_success([
            'token' => $tdsToken['token'],
            'engine' => $tdsToken['engine'],
        ]);
        wp_die();
    }

    /**
     * O checkout só pode pedir explicitamente o engine legado, usado como
     * fallback quando o NX não está disponível. Qualquer outro valor cai na
     * preferência padrão (NX), para que a query string não force o legado por
     * acidente nem sirva de vetor para escolher um engine arbitrário.
     *
     * @return string|null
     */
    private function getRequestedEngine()
    {
        if (empty($_GET['engine'])) {
            return null;
        }

        $engine = sanitize_text_field(wp_unslash($_GET['engine']));

        return $engine === TdsTokenService::ENGINE_LEGACY ? TdsTokenService::ENGINE_LEGACY : null;
    }
}
