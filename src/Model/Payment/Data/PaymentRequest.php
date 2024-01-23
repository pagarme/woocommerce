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

    /** @var Billet */
    private $billet;

    /** @var Pix */
    private $pix;

    /**
     * @param ShippingAddress|null $shippingAddress
     * @param BillingAddress|null $billingAddress
     * @param Cards|null $cards
     * @param ShippingMethod|null $shippingMethod
     * @param Json|null $jsonSerialize
     * @param Billet|null $billet
     * @param Pix|null $pix
     * @param array $data
     */
    public function __construct(
        ShippingAddress $shippingAddress = null,
        BillingAddress $billingAddress = null,
        Cards $cards = null,
        ShippingMethod $shippingMethod = null,
        Json $jsonSerialize = null,
        Billet $billet = null,
        Pix $pix = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->cards = $cards ?? new Cards;
        $this->shippingMethod = $shippingMethod ?? new ShippingMethod;
        $this->shippingAddress = $shippingAddress ?? new ShippingAddress;
        $this->billingAddress = $billingAddress ?? new BillingAddress;
        $this->billet = $billet ?? new Billet;
        $this->pix = $pix ?? new Pix;
        $this->init();
    }

    /**
     * @return void
     */
    protected function init()
    {
        foreach ($this->getConstants() as $const) {
            if (isset($_POST[$const]) || isset($this->getPostPaymentContent()[$const])) {
                $value = $_POST[$const] ?? $this->getPostPaymentContent()[$const];
                $this->{$this->getMethod($const)}($value);
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
        $paymentMethod = str_replace('woo-pagarme-payments-', '', $value);
        $paymentMethod = str_replace('-new-payment-method', '', $paymentMethod);
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
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
        if ($this->havePaymentForm(self::CARDS, false)) {
            return $this->setData(self::CARDS, $this->cards->getCards());
        }
        return $this;
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

    /**
     * @return PaymentRequest
     */
    public function setBillet()
    {
        if ($this->havePaymentForm(self::BILLET, false)) {
            return $this->setData(self::BILLET, $this->billet);
        }
        return $this;
    }

    /**
     * @return PaymentRequest
     */
    public function setPix()
    {
        if ($this->havePaymentForm(self::PIX, false)) {
            return $this->setData(self::PIX, $this->pix);
        }
        return $this;
    }
}
