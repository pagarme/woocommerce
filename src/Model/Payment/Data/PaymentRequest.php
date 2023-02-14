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

use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentRequest
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class PaymentRequest extends AbstractPayment implements PaymentRequestInterface
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
            $billingAddress = new BillingAddress;
        }
        $this->cards = $cards;
        $this->shippingMethod = $shippingMethod;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->init();
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
     * @return array
     */
    private function getConstants()
    {
        $oClass = new \ReflectionClass($this);
        return $oClass->getConstants();
    }

    /**
     * @param string $value
     * @return PaymentRequest
     */
    public function setPaymentMethod(string $value)
    {
        return $this->setData(self::PAYMENT_METHOD, str_replace('woo-pagarme-payments-', '', $value));
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * @return PaymentRequest
     */
    public function setShippingMethod()
    {
        return $this->setData(self::SHIPPING_METHOD, $this->shippingMethod->getMethods());
    }

    /**
     * @return array|mixed|null
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * @return PaymentRequest
     */
    public function setCards()
    {
        return $this->setData(self::CARDS, $this->cards->getCards());
    }

    /**
     * @return PaymentRequest
     */
    public function setShippingAddress()
    {
        return $this->setData(self::SHIPPING_ADDRESS, $this->shippingAddress);
    }

    /**
     * @return PaymentRequest
     */
    public function setBillingAddress()
    {
        return $this->setData(self::BILLING_ADDRESS, $this->billingAddress);
    }
}
