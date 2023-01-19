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
 *  Class BilletCard
 * @package Woocommerce\Pagarme\Model\Payment
 */
class BilletCard extends AbstractPayment implements PaymentInterface
{
    /** @var int */
    protected $suffix = 4;

    /** @var string */
    protected $name = 'Billet and Card';

    /** @var string */
    protected $code = 'billet-and-card';

    /** @var string[] */
    protected $requirementsData = [
        'card_billet_order_value',
        'installments3',
        'multicustomer_card_billet',
        'billet_value',
        'brand4',
        'pagarmetoken4',
        'multicustomer_billet_card',
        'payment_method',
        'enable_multicustomers_billet',
        'enable_multicustomers_card',
        'save_credit_card4',
        'card_id4'
    ];

    /** @var array */
    protected $dictionary = [
        'card_billet_order_value' => 'card_order_value',
        'multicustomer_card_billet' => 'multicustomer_card',
        'multicustomer_billet_card' => 'multicustomer_billet',
        'brand4' => 'brand',
        'installments3' => 'installments',
        'pagarmetoken4' => 'pagarmetoken1',
        'card_id4' => 'card_id',
        'save_credit_card4' => 'save_credit_card'
    ];

    /**
     * @return array
     */
    public function renameFieldsPost(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {
        $formattedPost = parent::renameFieldsPost($field, $formattedPost, $arrayFieldKey);
        if (in_array('pagarme_payment_method', $field)) {
            $field['name'] = 'payment_method';
            $field['value'] = 'billet_and_card';
            $formattedPost['fields'][$arrayFieldKey] = $field;
        }
        return $formattedPost;
    }
}