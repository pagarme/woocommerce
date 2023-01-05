<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

use Pagarme\Core\Payment\Aggregates\SavedCard;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Card
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Card extends AbstractPayment
{
    /**
     * @return SavedCard[]|null
     */
    public function getCards()
    {
        return $this->getCustomer()->get_cards($this->code);
    }

    /**
     * @return bool
     */
    public function getIsEnableWallet()
    {
        return (bool) $this->getConfig()->{'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->code)))  . 'Wallet'}();
    }
}
