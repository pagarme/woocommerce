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
 * Class Cards
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class Cards extends DataObject
{
    /** @var string */
    const CARDS = 'cards';

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        Json  $jsonSerialize = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->init();
    }

    /**
     * @return Cards
     */
    private function init()
    {
        $cards = [];
        for ($count = 0; $count < $this->getCountTokens(); $count++) {
            $card = new Card($count);
            $cards[] = $card;
        }
        return $this->setData(self::CARDS, $cards);
    }

    /**
     * @return int
     */
    private function getCountTokens()
    {
        $count = 0;
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'pagarmetoken') !== false) {
                $count++;
            }
        }
        return $count;
    }
}
