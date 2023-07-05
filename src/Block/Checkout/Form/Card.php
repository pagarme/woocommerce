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
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Payment\Voucher;
use Woocommerce\Pagarme\Model\Subscription;

defined( 'ABSPATH' ) || exit;

/**
 * Class Card
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Card extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/card';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/payment/card', 'checkout/model/payment/card/tokenize'];

    /**
     * @var string[]
     */
    protected $deps = ['pagarme-checkout-card'];

    /** @var int  */
    protected $sequence = 1;

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
     * @param int $qtyCards
     * @return $this
     */
    public function setQuantityCards(int $qtyCards = 1)
    {
        if ($qtyCards > 1) {
            $this->setShowOrderValue(true);
        }
        return $this->setData('qty_cards', $qtyCards);
    }

    /**
     * @return int
     */
    public function getQuantityCards()
    {
        if ($this->getQtyCards() > 1) {
            $this->setShowOrderValue(true);
        }
        return $this->getData('qty_cards');
    }

    /**
     * @return bool
     */
    public function showInstallments()
    {
        $hideInstallmentsInMethods = [
            Voucher::PAYMENT_CODE
        ];
        $isHideInstallments = in_array($this->getPaymentInstance()->getMethodCode(), $hideInstallmentsInMethods);

        if (Subscription::isChangePaymentSubscription() || $isHideInstallments) {
            return false;
        }
        return true;
    }

    public function showHolderName()
    {
        $methods = [
            Voucher::PAYMENT_CODE
        ];
        if (in_array($this->getPaymentInstance()->getMethodCode(), $methods)) {
            return true;
        }
        return false;
    }

    /**
     * @param string|null $id
     * @return string
     */
    public function getElementId(string $id = null)
    {
        $elementId = '[cards][' . $this->getSequence() . ']';
        if ($id) {
            $elementId .= '[' . $id . ']';
        }
        return parent::getElementId($elementId);
    }

    /**
     * @return String
     */
    public function getCompoenent()
    {
        return Utils::get_component('pagarme-checkout');
    }

}
