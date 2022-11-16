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

defined( 'ABSPATH' ) || exit;

/**
 *  Class TwoCards
 * @package Woocommerce\Pagarme\Model\Payment
 */
class TwoCards extends AbstractPayment implements PaymentInterface
{
    /** @var int */
    protected $suffix = 2;

    /** @var string */
    protected $name = '2 Cards';

    /** @var string */
    protected $code = '2_cards';

    /** @var string[] */
    protected $requirementsData = [
        'card_order_value',
        'brand2',
        'pagarmetoken2',
        'installments',
        'multicustomer_card1',
        'card_order_value2',
        'brand3',
        'pagarmetoken3',
        'installments2',
        'multicustomer_card2',
        'payment_method',
        'enable_multicustomers_card1',
        'enable_multicustomers_card2',
        'save_credit_card2',
        'save_credit_card3',
        'card_id2',
        'card_id3'
    ];

    /** @var array */
    protected $dictionary = [
        'brand2' => 'brand',
        'brand3' => 'brand2',
        'pagarmetoken2' => 'pagarmetoken1',
        'pagarmetoken3' => 'pagarmetoken2',
        'card_id2' => 'card_id',
        'card_id3' => 'card_id2',
        'save_credit_card2' => 'save_credit_card',
        'save_credit_card3' => 'save_credit_card2'
    ];
}
