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
use Woocommerce\Pagarme\Model\CardInstallments;
use Woocommerce\Pagarme\Model\Subscription;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Gateway as GatewayModel;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined('ABSPATH') || exit;

/**
 * Class Installments
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Installments extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/card/installments';

    /** @var int  */
    protected $sequence = 1;

    /** @var CardInstallments */
    protected $cardInstallments;

    /** @var Subscription  */
    protected $subscription;

    public function __construct(
        Json         $jsonSerialize = null,
        array        $data = [],
        GatewayModel $gateway = null,
        Config       $config = null
    )
    {
        parent::__construct($jsonSerialize, $data, $gateway, $config);
        $this->cardInstallments = new CardInstallments();
        $this->subscription = new Subscription();
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
     * @param string $id
     * @return string
     */
    public function getElementId(string $id)
    {
        $id = '[cards][' . $this->getSequence() . '][' . $id . ']';
        return parent::getElementId($id);
    }

    /**
     * @return int
     */
    public function getInstallmentsType()
    {
        return intval($this->getConfig()->getCcInstallmentType());
    }

    /**
     * @return boolean
     */
    public function isInterestForAllFlags()
    {
        return ($this->getInstallmentsType() == CardInstallments::INSTALLMENTS_FOR_ALL_FLAGS);
    }
    /**
     * @return String
     */
    public function getInstallmentsComponent()
    {
        return Utils::get_component('installments');
    }

    public function render_installments($total)
    {
        return $this->cardInstallments->getInstallmentsByType($total);
    }

    /**
     * @return array
     */
    public function render()
    {
        return $this->render_installments($this->getCartTotals());
    }

    /**
     * @return bool
     */
    public function isCcInstallmentTypeByFlag()
    {
        $type = intval($this->cardInstallments->config->getCcInstallmentType()) ?? 1;
        return $type === CardInstallments::INSTALLMENTS_BY_FLAG;
    }

    /**
     * @return int
     */
    public function getConfiguredMaxCcInstallments()
    {
        if ($this->isCcInstallmentTypeByFlag()) {
            $flag = Utils::get('flag', false, 'esc_html');
            $configByFlags = $this->cardInstallments->config->getCcInstallmentsByFlag();
            return intval($configByFlags['max_installment'][$flag]);
        }
        return intval($this->cardInstallments->config->getCcInstallmentsMaximum());
    }

    /**
     * @return bool
     */
    public function showOneInstallmentInfo()
    {
        if (!Subscription::hasSubscriptionProductInCart()) {
            return false;
        }
        if (
            $this->subscription->allowInstallments()
            && $this->subscription->hasOneInstallmentPeriodInCart()
            && ($this->getConfiguredMaxCcInstallments() > 1 || $this->isCcInstallmentTypeByFlag())
        ) {
            return true;
        }
        return false;
    }
}
