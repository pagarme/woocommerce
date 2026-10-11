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
    const TDS_NX_URL_SANDBOX = 'https://3ds-nx-js.stone.com.br/test/v2/3ds2.min.js';
    const TDS_NX_URL_PRODUCTION = 'https://3ds-nx-js.stone.com.br/live/v2/3ds2.min.js';

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
     * Lib 3DS NX, retrocompatível com tokens do 3DS Handler.
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
