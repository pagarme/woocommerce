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
    const PIX = "pix";

    private $paymentMethod;

    public function setPaymentMethod($platformOrder)
    {
        $paymentMethod = $platformOrder->getPaymentMethodPlatform();
        if (
            $paymentMethod === self::BOLETO_CREDIT_CARD ||
            $paymentMethod === self::TWO_CREDIT_CARDS
        ) {
            $paymentMethod = self::CREDIT_CARD;
        }
        $this->paymentMethod = $this->{$paymentMethod}();
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    private function creditcard()
    {
        return self::CREDIT_CARD;
    }

    /**
     * @return string
     * @uses WoocommercePlatformPaymentMethodDecorator::setPaymentMethod()
     */
    private function billet()
    {
        return self::BOLETO;
    }

    private function voucher()
    {
        return self::VOUCHER;
    }

    private function debit()
    {
        return self::DEBIT;
    }

    private function pix()
    {
        return self::PIX;
    }
}
