<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\Form;

use Woocommerce\Pagarme\Block\Checkout\Gateway;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Payment\CreditCard;
use Woocommerce\Pagarme\Model\Payment\Voucher;

defined( 'ABSPATH' ) || exit;

/**
 * Class Wallet
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Wallet extends Gateway
{

    /** @var int  */
    protected $sequence = 1;

    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/card/wallet';

    /**
     * @var string[]
     */
    protected $deps = [ WCMP_JS_HANDLER_BASE_NAME . 'card'];

    /**
     * @param int $qtyCards
     * @return $this
     */
    public function setQuantityCards(int $qtyCards = 1)
    {
        return $this->setData('qty_cards', $qtyCards);
    }

    /**
     * @return int
     */
    public function getQuantityCards()
    {
        return $this->getData('qty_cards');
    }

    /**
     * @param int $sequence
     * @return $this
     */
    public function setSequence(int $sequence)
    {
        return $this->setData('sequence', $sequence);
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        if (!$this->getData('sequence')) {
            return $this->sequence;
        }
        return $this->getData('sequence');
    }

    /**
     * @return bool
     */
    public function isWalletEnable()
    {
        $result = false;
        switch ($this->getPaymentInstance()->getMethodCode()) {
            case Voucher::PAYMENT_CODE:
                $result = (bool) $this->getConfig()->getVoucherCardWallet();
                break;
            default:
                $result = (bool) $this->getConfig()->getCcAllowSave();
                break;
        }
        return $result;
    }

    /**
     * @param string $id
     * @return string
     */
    public function getElementId(string $id)
    {
        $id = '[cards][' . $this->getSequence() . '][' . $id . ']';
        return parent::getElementId($id);
    }
}
