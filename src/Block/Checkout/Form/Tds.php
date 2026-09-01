<?php

/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Checkout\Form;

use Woocommerce\Pagarme\Block\Checkout\Gateway;

defined('ABSPATH') || exit;

/**
 * Class Tds
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Tds extends Gateway
{
    /**
     * A doc do AuthSwitch não publica URL de sandbox para a lib — a tabela de
     * ambientes traz "-". Assumimos simetria com a lib de 3DS NX até o time da
     * Stone confirmar; é o único ponto a trocar quando a URL oficial sair.
     */
    const AUTH_SWITCH_URL_SANDBOX = 'https://tifa-app.stone.com.br/test/v1/tifa/tifa-app.min.js';
    const AUTH_SWITCH_URL_PRODUCTION = 'https://tifa-app.stone.com.br/live/v1/tifa/tifa-app.min.js';

    const TDS_NX_URL_SANDBOX = 'https://3ds-nx-js.stone.com.br/test/v2/3ds2.min.js';
    const TDS_NX_URL_PRODUCTION = 'https://3ds-nx-js.stone.com.br/live/v2/3ds2.min.js';

    const LEGACY_SDK_URL_SANDBOX = 'https://auth-3ds-sdx.pagar.me/bundle.js';
    const LEGACY_SDK_URL_PRODUCTION = 'https://auth-3ds.pagar.me/bundle.js';

    /** @var int  */
    protected $sequence = 1;

    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/card/tds';

    protected $scripts = [
        'checkout/model/payment/card/tds',
        'checkout/model/payment/card/tdsToken',
        'checkout/model/payment/card/initTds',
    ];

    /**
     * @var string[]
     */
    protected $deps = [WCMP_JS_HANDLER_BASE_NAME . 'card'];

    /**
     * Bundle do 3DS legado (engine `legacy`), mantido para contas ainda não
     * migradas para o NX.
     */
    public function getSdkUrl()
    {
        return $this->getConfig()->getIsSandboxMode()
            ? self::LEGACY_SDK_URL_SANDBOX
            : self::LEGACY_SDK_URL_PRODUCTION;
    }

    /**
     * Lib do AuthSwitch, que orquestra fingerprint e 3DS NX.
     */
    public function getAuthSwitchUrl()
    {
        return $this->getConfig()->getIsSandboxMode()
            ? self::AUTH_SWITCH_URL_SANDBOX
            : self::AUTH_SWITCH_URL_PRODUCTION;
    }

    /**
     * O AuthSwitch exige que a lib de 3DS NX seja carregada em conjunto com a
     * dele; sem os dois scripts `window.tifa.init` não executa a autenticação.
     */
    public function get3DsNxUrl()
    {
        return $this->getConfig()->getIsSandboxMode()
            ? self::TDS_NX_URL_SANDBOX
            : self::TDS_NX_URL_PRODUCTION;
    }

    public function canInitTds()
    {
        return $this->getConfig()->isTdsEnabled();
    }
}
