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

use stdClass;
use WC_Order;
use Woocommerce\Pagarme\Model\Payment\Voucher\Brands;
use Woocommerce\Pagarme\Model\Payment\Voucher\BrandsInterface;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Voucher
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Voucher extends Card implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'voucher';

    /** @var int */
    protected $suffix = 6;

    /** @var string */
    protected $name = 'Voucher';

    /** @var string */
    protected $code = self::PAYMENT_CODE;

    /** @var string[] */
    protected $requirementsData = [
        'brand6',
        'payment_method',
        'pagarmetoken6',
        'save_credit_card6',
        'card_id6'
    ];

    /** @var array */
    protected $dictionary = [
        'card_id6' => 'card_id',
        'brand6' => 'brand',
        'save_credit_card6' => 'save_credit_card'
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

    /**
     * @param WC_Order $wc_order
     * @param array $form_fields
     * @param stdClass|null $customer
     * @return array|string[]
     * @throws \Exception
     */
    public function getPayRequestBase(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        $content = parent::getPayRequestBase($wc_order, $form_fields, $customer);
        $content['voucher'] = [
            'statement_descriptor' => $this->getConfig()->getVoucherSoftDescriptor(),
            'card' => [
                'billing_address' => $this->getBillingAddressFromCustomer($customer, $wc_order)
            ]
        ];
        return $this->handleCardType($form_fields, $content);
    }
}
