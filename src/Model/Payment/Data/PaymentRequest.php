<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Data;

use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentRequest
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class PaymentRequest extends DataObject implements PaymentRequestInterface
{
    /** @var ShippingMethod */
    private $shippingMethod;

    /** @var Cards */
    private $cards;

    /** @var ShippingAddress */
    private $shippingAddress;

    /** @var BillingAddress */
    private $billingAddress;

    /**
     * @param ShippingAddress|null $shippingAddress
     * @param BillingAddress|null $billingAddress
     * @param Cards|null $cards
     * @param ShippingMethod|null $shippingMethod
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        ShippingAddress $shippingAddress = null,
        BillingAddress $billingAddress = null,
        Cards $cards = null,
        ShippingMethod $shippingMethod = null,
        Json $jsonSerialize = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        if (!$shippingMethod) {
            $shippingMethod = new ShippingMethod;
        }
        if (!$cards) {
            $cards = new Cards;
        }
        if (!$shippingAddress) {
            $shippingAddress = new ShippingAddress;
        }
        if (!$billingAddress) {
            $billingAddress = new BillingAddress();
        }
        $this->cards = $cards;
        $this->shippingMethod = $shippingMethod;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return void
     */
    private function init()
    {
        foreach ($this->getConstants() as $const) {
            if (isset($_POST[$const])) {
                $this->{$this->getMethod($const)}($_POST[$const]);
            }
        }
        $this->setCards();
        $this->setShippingAddress();
        $this->setBillingAddress();
    }

    /**
     * @param string $value
     * @return PaymentRequest
     */
    protected function setPaymentMethod(string $value)
    {
        return $this->setData(self::PAYMENT_METHOD, str_replace('woo-pagarme-payments-', '', $value));
    }

    /**
     * @return PaymentRequest
     */
    protected function setShippingMethod()
    {
        return $this->setData(self::SHIPPING_METHOD, $this->shippingMethod->getMethods());
    }

    /**
     * @return PaymentRequest
     */
    protected function setCards()
    {
        return $this->setData(self::CARDS, $this->cards->getCards());
    }

    /**
     * @return PaymentRequest
     */
    protected function setShippingAddress()
    {
        return $this->setData(self::SHIPPING_ADDRESS, $this->shippingAddress);
    }

    /**
     * @return PaymentRequest
     */
    protected function setBillingAddress()
    {
        return $this->setData(self::BILLING_ADDRESS, $this->billingAddress);
    }

    /**
     * @param string $value
     * @param string $type
     * @return string
     */
    private function getMethod(string $value, string $type = 'set')
    {
        return $type . str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }
}
