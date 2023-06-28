<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order\Email;

defined('ABSPATH') || exit;

/**
 * Class Boleto
 * @package Woocommerce\Pagarme\Block\Order
 */
class Boleto extends AbstractEmail
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/email/billet';

    /**
     * @return string|null
     */
    public function getBilletUrl()
    {
        try {
            return $this->getTransaction()->getBoletoUrl();
        } catch (\Exception $e) {
            // @todo
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getBilletLine()
    {
        try {
            return json_decode($this->getTransaction()->getPostData()->tran_data)->line;
        } catch (\Exception $e) {
            // @todo
        }
        return null;
    }
}
