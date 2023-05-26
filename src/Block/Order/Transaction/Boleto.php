<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Order\Transaction;

defined( 'ABSPATH' ) || exit;

/**
 * Class Boleto
 * @package Woocommerce\Pagarme\Block\Order
 */
class Boleto extends AbstractTransaction
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/transaction/billet';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/payment/billet'];

    /**
     * @return string|null
     */
    public function getBilletUrl()
    {
        try {
            return $this->getTransaction()->getBoletoUrl();
        } catch (\Exception $e) {}
        return null;
    }
}
