<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Aggregates\SavedCard;

final class WoocommerceSavedCardAdapter
{
    private $adaptee;
    private $customer;

    public function __construct(SavedCard $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    public function getBrand()
    {
        return $this->adaptee->getBrand()->getName();
    }

    public function getLastFourNumbers()
    {
        return $this->adaptee->getLastFourDigits()->getValue();
    }

    public function getCreatedAt()
    {
        $createdAt = $this->adaptee->getCreatedAt();
        if ($createdAt !== null) {
            return $createdAt->format(SavedCard::DATE_FORMAT);
        }

        return null;
    }

    public function getId()
    {
        return 'mp_core_' . $this->adaptee->getId();
    }

    public function getFirstSixDigits()
    {
        return $this->adaptee->getFirstSixDigits()->getValue();
    }

    public function getMaskedNumber()
    {
        $firstSix = $this->getFirstSixDigits();
        $lastFour = $this->getLastFourNumbers();

        $firstSix = number_format($firstSix / 100, 2, '.', '');

        return $firstSix . '**.****.' . $lastFour;
    }

    public function setCustomer(Customer $customerObject)
    {
        $this->customer = $customerObject;
    }

    public function getCardId()
    {
        return $this->adaptee->getPagarmeId()->getValue();
    }

    /**
     * int|null
     */
    public function getCustomerId()
    {
        if (is_null($this->customer)) {
            return null;
        }

        return $this->customer->getCode();
    }
}
