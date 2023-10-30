<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBox;

use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Helper\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class Modal
 * @package Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBox
 */
class Modal extends Template
{
    /** @var string */
    const MODAL_TYPE_CAPTURE = 'capture';

    /** @var string */
    const MODAL_TYPE_CANCEL = 'cancel';

    /**
     * @var string
     */
    protected $_template = 'templates/adminhtml/sales/order/meta-box/modal';

    /**
     * @param $charge
     * @return false|mixed
     */
    public function getTransaction($charge)
    {
        return current($charge->getTransactions());
    }

    /**
     * @return string|void
     */
    public function getPartiallyType()
    {
        if ($this->getModalType() === self::MODAL_TYPE_CAPTURE) {
            return 'partially captured';
        }
        if ($this->getModalType() === self::MODAL_TYPE_CANCEL) {
            return 'partially canceled';
        }
        return '';
    }

    public function getLabelByType()
    {
        if ($this->getModalType() === self::MODAL_TYPE_CAPTURE) {
            return 'Value to be captured: ';
        }
        if ($this->getModalType() === self::MODAL_TYPE_CANCEL) {
            return 'Value to be canceled: ';
        }
        return '';
    }

    /**
     * @return int|String|void
     */
    public function getActionAmount()
    {
        if ($this->getModalType() === self::MODAL_TYPE_CANCEL) {
            $canceled = $this->getCharge()->getCanceledAmount() ? $this->getCharge()->getCanceledAmount() : 0;
            $refunded = $this->getCharge()->getRefundedAmount() ? $this->getCharge()->getRefundedAmount() : 0;
            $paid = $this->getCharge()->getPaidAmount() ? $this->getCharge()->getPaidAmount() : 0;
            $toCancel = $this->getCharge()->getAmount();
            if ($paid) {
                $toCancel = $paid;
            }
            if ($paid && $canceled) {
                $toCancel = $paid - $canceled;
            }
            if ($paid && $refunded) {
                $toCancel = max(0, $paid - $refunded);
            }
            return $toCancel;
        }
        return 0;
    }

    /**
     * @return String
     */
    public function getAmount()
    {
        return Utils::format_order_price_to_view($this->getCharge()->getAmount());
    }

    public function getPartiallyAmount()
    {
        if ($this->getModalType() === self::MODAL_TYPE_CAPTURE) {
            return $this->getCharge()->getPaidAmount() ? Utils::format_order_price_to_view($this->getCharge()->getPaidAmount()) : ' - ';
        }
        if ($this->getModalType() === self::MODAL_TYPE_CANCEL) {
            return $this->getCharge()->getCanceledAmount() ? Utils::format_order_price_to_view($this->getCharge()->getCanceledAmount()) : ' - ';
        }
    }
}
