<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Interfaces\PlatformPaymentMethodInterface;

class WoocommercePlatformPaymentMethodDecorator implements PlatformPaymentMethodInterface
{
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'billet';
    const BOLETO_CREDIT_CARD = 'billet_and_card';
    const TWO_CARDS = '2_cards';
    const VOUCHER = 'voucher';
    const DEBIT = "debit";
    const PIX = "pix";

    private $paymentMethod;

    public function setPaymentMethod($platformOrder)
    {
        $paymentMethod = $platformOrder->get_payment_method();
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
        return self::TWO_CARDS;
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
