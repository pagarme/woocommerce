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
class Cards extends AbstractPayment
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
    protected function init()
    {
        $cards = [];
        for ($count = 1; $count <= $this->getCountCards(); $count++) {
            $card = new Card($count);
            $cards[] = $card;
        }
        return $this->setData(self::CARDS, $cards);
    }

    /**
     * @return int
     */
    private function getCountCards()
    {
        $count = 0;
        if ($this->getPostPaymentContent() && isset($this->getPostPaymentContent()['cards'])) {
            foreach ($this->getPostPaymentContent()['cards'] as $card) {
                foreach ($card as $key => $value) {
                    if (strpos($key, 'token') !== false || $key === 'wallet-id') {
                        if ($value) {
                            $count++;
                            break;
                        }
                    }
                }
            }
        }
        return $count;
    }
}
