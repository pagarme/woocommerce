<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

use Woocommerce\Pagarme\Core;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Pix
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Pix extends AbstractPayment implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'pix';

    /** @var int */
    protected $suffix = 7;

    /** @var string */
    protected $name = 'Pix';

    /** @var string */
    protected $code = self::PAYMENT_CODE;

    /** @var string[] */
    protected $requirementsData = [
        'multicustomer_pix',
        'payment_method',
        'enable_multicustomers_pix'
    ];

    /** @var array */
    protected $dictionary = [];

    /**
     * @return string
     */
    public function getImage()
    {
        return esc_url(Core::plugins_url('assets/images/pix.svg'));
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return __('O QR Code para seu pagamento através de PIX será gerado após a confirmação da compra. Aponte seu celular para a tela para capturar o código ou copie e cole o código em seu aplicativo de pagamentos.', 'woo-pagarme-payments');
    }
}
