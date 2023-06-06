<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\Field;

use Woocommerce\Pagarme\Block\Checkout\Gateway;
use Woocommerce\Pagarme\Model\Payment\BilletCard;
use Woocommerce\Pagarme\Model\Payment\CreditCard;
use Woocommerce\Pagarme\Model\Payment\TwoCards;
use Woocommerce\Pagarme\Model\Payment\Voucher;

defined( 'ABSPATH' ) || exit;

/**
 * Class Wallet
 * @package Woocommerce\Pagarme\Block\Checkout\Field
 */
class Wallet extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/field/card-save';

    protected $scripts = ['checkout/model/payment/card/wallet'];

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
     * @param string $id
     * @return string
     */
    public function getElementId(string $id)
    {
        if ($this->getParentElementId()) {
            return $this->getParentElementId() . '[' . $id . ']';
        }
        $id = '[wallet][' . $this->getSequence() . '][' . $id . ']';
        return parent::getElementId($id);
    }

    /**
     * @return bool
     */
    public function getIsEnableWallet()
    {
        $configField = [
            CreditCard::PAYMENT_CODE => 'cc-allow-save',
            TwoCards::PAYMENT_CODE => 'cc-allow-save',
            BilletCard::PAYMENT_CODE => 'cc-allow-save',
            Voucher::PAYMENT_CODE => 'voucher-card-wallet'
        ];
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('-', ' ' ,
            $configField[$this->getPaymentInstance()->getMethodCode()]
        )));
        return (bool)$this->getConfig()->{$method}();
    }
}
