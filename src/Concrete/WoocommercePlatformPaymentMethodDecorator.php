<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Interfaces\PlatformPaymentMethodInterface;

class WoocommercePlatformPaymentMethodDecorator implements PlatformPaymentMethodInterface
{
    const CREDIT_CARD = 'creditCard';
    const BOLETO = 'boleto';
    const BOLETO_CREDIT_CARD = 'billetAndCard';
    const TWO_CREDIT_CARDS = '2Cards';
    const VOUCHER = 'voucher';
    const DEBIT = "debit";

    /** @var string */
    const PIX = "pix";

    private $paymentMethod;

    public function setPaymentMethod($platformOrder)
    {
        $paymentMethod = $platformOrder->getPaymentMethodPlatform();
        if (in_array($paymentMethod, [self::BOLETO_CREDIT_CARD, self::TWO_CREDIT_CARDS])) {
            $paymentMethod = self::CREDIT_CARD;
        }
        $method = 'get' . str_replace(' ', '', ucwords(str_replace('-', ' ', $paymentMethod)));
        $this->paymentMethod = $this->{$method}();
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    private function getCreditCard()
    {
        return self::CREDIT_CARD;
    }

    /**
     * @return string
     * @uses WoocommercePlatformPaymentMethodDecorator::setPaymentMethod()
     */
    private function getBillet()
    {
        return self::BOLETO;
    }

    private function getVoucher()
    {
        return self::VOUCHER;
    }

    private function getDebit()
    {
        return self::DEBIT;
    }

    private function getPix()
    {
        return self::PIX;
    }
}
