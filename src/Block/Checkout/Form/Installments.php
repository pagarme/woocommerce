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
use Woocommerce\Pagarme\View\Checkouts;

global $woocommerce;

defined( 'ABSPATH' ) || exit;

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
     * @return String
     */
    public function getInstallmentsComponent()
    {
        return Utils::get_component('installments');
    }

    /**
     * @return array
     */
    public function render()
    {
        return Checkouts::render_installments($this->getCartTotals());
    }
}
