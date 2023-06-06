<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Controller\Checkout;

use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Card
 * @package Woocommerce\Pagarme\Controller\Checkout
 */
class Card
{
    /** @var Gateway */
    private $gateway;

    /** @var Json */
    private $json;

    /**
     * @param Gateway|null $gateway
     * @param Json|null $json
     */
    public function __construct(
        Gateway $gateway = null,
        Json $json = null
    ) {
        $this->gateway = $gateway;
        if (!$this->gateway) {
            $this->gateway = new Gateway;
        }
        $this->json = $json;
        if (!$this->json) {
            $this->json = new Json;
        }
        $this->_init();
    }

    /**
     * @return void
     */
    private function _init()
    {
        add_action('wp_ajax_pagarme_checkout_card_config_provider', [$this, 'getJsConfigDataProvider']);
        add_action('wp_ajax_nopriv_pagarme_checkout_card_config_provider', [$this, 'getJsConfigDataProvider']);
    }

    /**
     * @return string
     */
    public function getJsConfigDataProvider()
    {
        $config = $this->gateway->getConfigDataProvider();
        return $this->json->serialize($config);
    }
}
