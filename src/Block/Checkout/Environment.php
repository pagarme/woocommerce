<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout;

use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Environment
 * @package Woocommerce\Pagarme\Block\Checkout
 */
class Environment extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/environment';

    /**
     * @param Config|null $config
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Config $config = null
    ) {
        parent::__construct($jsonSerialize, $data);
        if (!$this->getData('config')) {
            $this->setData('config', $config ?? new Config);
        }
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->getData('config');
    }
}
