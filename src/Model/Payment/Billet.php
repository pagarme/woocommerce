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

use ReflectionClass;
use Woocommerce\Pagarme\Core;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Billet
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Billet extends AbstractPayment implements PaymentInterface
{
    /** @var int */
    protected $suffix = 5;

    /** @var string */
    protected $name = 'Billet';

    /** @var string */
    protected $code = 'billet';

    /** @var string[] */
    protected $requirementsData = [
        'multicustomer_billet',
        'payment_method',
        'enable_multicustomers_billet'
    ];

    /** @var array */
    protected $dictionary = [];

    /**
     * @return string
     */
    public function getImage()
    {
        return esc_url(Core::plugins_url('assets/images/barcode.svg'));
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return __('O Boleto bancário será exibido após a confirmação da compra e poderá ser pago em qualquer agência bancária, pelo seu smartphone ou computador através de serviços digitais de bancos.', 'woo-pagarme-payments');
    }
}
