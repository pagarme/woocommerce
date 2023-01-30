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
 * Class AbstractAddress
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class AbstractAddress extends DataObject implements AddressInterface
{
    /** @var string */
    protected $type = '';

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
        foreach ($this->getConstants() as $const) {
            if (isset($_POST[$this->type . '_' . $const])) {
                $this->{$this->getMethod($const)}($_POST[$this->type . '_' . $const]);
            }
        }
    }

    /**
     * @param string $value
     * @param string $type
     * @return string
     */
    private function getMethod(string $value, string $type = 'set')
    {
        return $type . str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }

    /**
     * @return array
     */
    private function getConstants()
    {
        $oClass = new \ReflectionClass($this);
        return $oClass->getConstants();
    }
}
