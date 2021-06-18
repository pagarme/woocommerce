<?php

namespace Pagarme\Core\Recurrence\Interfaces;

use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;

interface ProductPlanInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return ProductPlanInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return ProductPlanInterface
     */
    public function setProductId($productId);

    /**
     * @return string
     */
    public function getBoleto();

    /**
     * @param string $boleto
     * @return ProductPlanInterface
     */
    public function setBoleto($boleto);

    /**
     * @param string $creditCard
     * @return ProductPlanInterface
     */
    public function setCreditCard($creditCard);

    /**
     * @return string
     */
    public function getCreditCard();

    /**
     * @param string $allowInstallments
     * @return ProductPlanInterface
     */
    public function setAllowInstallments($allowInstallments);

    /**
     * @return string
     */
    public function getAllowInstallments();


    /**
     * @param int $intervalCount
     * @return ProductPlanInterface
     */
    public function setIntervalCount($intervalCount);

    /**
     * @return int
     */
    public function getIntervalCount();

    /**
     * @param int $intervalType
     * @return ProductPlanInterface
     */
    public function setIntervalType($intervalType);

    /**
     * @return string
     */
    public function getIntervalType();

    /**
     * @return int
     */
    public function getTrialPeriodDays();

    /**
     * @param int $trialPeriodDays
     * @return mixed
     */
    public function setTrialPeriodDays($trialPeriodDays);

    /**
     * @param \Pagarme\Core\Recurrence\Aggregates\SubProduct[] $items
     * @return \Pagarme\Core\Recurrence\Aggregates\Plan
     */
    public function setItems(array $items);

    /**
     * @return \Pagarme\Core\Recurrence\Aggregates\SubProduct[]
     */
    public function getItems();
}
