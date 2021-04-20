<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Interfaces\PlatformPaymentMethodInterface;

class WoocommercePlatformPaymentMethodDecorator implements PlatformPaymentMethodInterface
{
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'boleto';
    const BOLETO_CREDIT_CARD = 'billet_and_card';
    const VOUCHER = 'voucher';
    const DEBIT = "debit";
    const PIX = "pix";

    private $paymentMethod;

    public function setPaymentMethod($platformOrder)
    {
        $paymentMethod = $platformOrder->getPaymentMethodPlatform();
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

    private function twocreditcards()
    {
        return self::CREDIT_CARD;
    }

    private function billetcreditcard()
    {
        return self::BOLETO_CREDIT_CARD;
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
