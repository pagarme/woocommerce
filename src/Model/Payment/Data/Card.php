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
class Card extends DataObject
{
    /** @var string */
    const BRAND = 'brand';

    /** @var string */
    const TOKEN = 'token';

    /** @var string */
    const INSTALLMENTS = 'installments';

    private $fields = [
        'brand',
        'installments_card',
        'pagarmetoken'
    ];

    /** @var int */
    private int $num;

    /**
     * @param int $num
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        int $num = 0,
        Json  $jsonSerialize = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->num = $num;
        $this->init();
    }

    /**
     * @return void
     */
    private function init()
    {
        foreach ($this->fields as $field) {
            if (!$this->num) {
                if (isset($_POST[$field])) {
                    $this->{$this->getMethod($field)}($_POST[$field]);
                }
            } else {
                if (isset($_POST[$field . '_' . $this->num])) {
                    $this->{$this->getMethod($field)}($_POST[$field . '_' . $this->num]);
                }
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
     * @param string $value
     * @return Card
     */
    protected function setPagarmetoken(string $value)
    {
        return $this->setData(self::TOKEN, $value);
    }

    /**
     * @param string $value
     * @return Card
     */
    protected function setInstallmentsCard(string $value)
    {
        return $this->setData(self::INSTALLMENTS, $value);
    }
}
