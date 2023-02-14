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
class Card extends AbstractPayment
{
    /** @var int */
    private int $num;

    /**
     * @param int $num
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        int $num = 1,
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
        foreach ($this->getPostPaymentContent()['cards'][$this->num] as $field => $value) {
            $this->{$this->getMethod($field)}($value);
        }
    }


}
