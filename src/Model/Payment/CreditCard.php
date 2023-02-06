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

use Woocommerce\Pagarme\Model\Payment\CreditCard\Brands;
use Woocommerce\Pagarme\Model\Payment\CreditCard\BrandsInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class CreditCard
 * @package Woocommerce\Pagarme\Model\Payment
 */
class CreditCard extends Card implements PaymentInterface
{
    /** @var int */
    protected $suffix = 1;

    /** @var string */
    protected $name = 'Credit Card';

    /** @var string */
    protected $code = 'credit-card';

    /** @var string[] */
    protected $requirementsData = [
        'brand1',
        'pagarmetoken1',
        'installments_card',
        'multicustomer_card',
        'payment_method',
        'enable_multicustomers_card',
        'save_credit_card1',
        'card_id'
    ];

    /** @var array */
    protected $dictionary = [
        'installments_card' => 'installments',
        'brand1' => 'brand',
        'save_credit_card1' => 'save_credit_card'
    ];

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        $jsConfigProvider = parent::getConfigDataProvider();
        $brands = new Brands;
        foreach ($brands->getBrands() as $class) {
            /** @var BrandsInterface $bank */
            $brand = new $class;
            $jsConfigProvider['brands'][$brand->getBrandCode()] = $brand->getConfigDataProvider();
        }
        return $jsConfigProvider;
    }
}
