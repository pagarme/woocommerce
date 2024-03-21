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

    public function getSdkUrl()
    {
        $url = 'https://auth-3ds.pagar.me/bundle.js';
        if ($this->getConfig()->getIsSandboxMode()) {
            $url = 'https://auth-3ds-sdx.pagar.me/bundle.js';
        }
        return $url;
    }

    public function canInitTds()
    {
        return $this->getConfig()->isTdsEnabled();
    }
}
