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
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Subscription;
use Woocommerce\Pagarme\Model\Payment\CreditCard\Brands;
use Woocommerce\Pagarme\Model\Payment\CreditCard\BrandsInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class CreditCard
 * @package Woocommerce\Pagarme\Model\Payment
 */
class CreditCard extends Card implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'credit_card';

    /** @var int */
    protected $suffix = 1;

    /** @var string */
    protected $name = 'Credit Card';

    /** @var string */
    protected $code = self::PAYMENT_CODE;

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
        global $wp;
        $jsConfigProvider = parent::getConfigDataProvider();
        $brands = new Brands;
        foreach ($brands->getBrands() as $class) {
            /** @var BrandsInterface $bank */
            $brand = new $class;
            $jsConfigProvider['brands'][$brand->getBrandCode()] = $brand->getConfigDataProvider();
        }
        $jsConfigProvider['tdsEnabled'] = Subscription::hasSubscriptionProductInCart()
            || Subscription::isChangePaymentSubscription()
            || isset($wp->query_vars['order-pay'])
            ? false
            : $this->getConfig()->isTdsEnabled();
        if ($jsConfigProvider['tdsEnabled']) {
            $jsConfigProvider['tdsMinAmount'] = $this->getConfig()->getTdsMinAmount();
        }
        return $jsConfigProvider;
    }

    /**
     * @param WC_Order $wc_order
     * @param array $form_fields
     * @param stdClass|null $customer
     * @return null[]|string[]
     * @throws \Exception
     */
    public function getPayRequest(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        $request = [];
        $content = current(parent::getPayRequest($wc_order, $form_fields, $customer));
        $amount = Utils::str_to_float($this->getAmount($wc_order, $form_fields));
        $content['amount'] = Utils::format_order_price(
            $this->getPriceWithInterest(
                $amount,
                Utils::get_value_by($form_fields, 'installments'),
                Utils::get_value_by($form_fields, 'brand')
            )
        );
        if (!isset($content['customer']) && isset($customer->email)) {
            $content['customer'] = $customer;
        }
        $request[] = $content;
        return $request;
    }
}
