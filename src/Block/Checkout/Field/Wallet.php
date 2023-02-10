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

    /** @var int  */
    protected int $sequence = 1;

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
        $id = '[wallet][' . $this->getSequence() . '][' . $id . ']';
        return parent::getElementId($id);
    }
}
