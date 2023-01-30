<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Data;

use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class ShippingMethod
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class ShippingMethod extends DataObject
{
    /** @var string */
    const SHIPPING_METHOD = 'shipping_method';

    /** @var string */
    const METHODS = 'methods';

    private $methods;

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        Json $jsonSerialize = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->init();
    }

    /**
     * @return $this
     */
    public function get()
    {
        return $this;
    }

    /**
     * @return void
     */
    private function init()
    {
        if (isset($_POST[self::SHIPPING_METHOD])) {
            if (is_array($_POST[self::SHIPPING_METHOD])) {
                foreach ($_POST[self::SHIPPING_METHOD] as $shipping) {
                    $this->methods[] = $shipping;
                }
            } else {
                $this->methods = $_POST[self::SHIPPING_METHOD];
            }
        }
        $this->setData(self::METHODS,  $this->methods);
    }
}
