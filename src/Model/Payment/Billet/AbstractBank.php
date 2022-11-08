<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Billet;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Bank
 * @package Woocommerce\Pagarme\Model\Payment\Billet
 */
abstract class AbstractBank
{
    /** @var int */
    protected $id = 0;

    /** @var string */
    protected $name = '';

    /** @var bool */
    protected $prefix = true;

    /** @var bool */
    protected $isSA = true;

    /**
     * @return int
     */
    public function getBankId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = '';
        if ($this->prefix) {
            $name .= __('Bank ', 'woo-pagarme-payments');
        }
        $name .= $this->name;
        if ($this->isSA) {
            $name .= __(' S.A.', 'woo-pagarme-payments');
        }
        return $name;
    }
}
