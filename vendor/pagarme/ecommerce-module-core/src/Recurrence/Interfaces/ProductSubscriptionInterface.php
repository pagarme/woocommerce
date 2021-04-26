<?php

namespace Pagarme\Core\Recurrence\Interfaces;

interface ProductSubscriptionInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return ProductSubscriptionInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return ProductSubscriptionInterface
     */
    public function setProductId($productId);

    /**
     * @return bool
     */
    public function getCreditCard();

    /**
     * @param bool $creditCard
     * @return ProductSubscriptionInterface
     */
    public function setCreditCard($creditCard);

    /**
     * @return bool
     */
    public function getBoleto();

    /**
     * @param bool $boleto
     * @return ProductSubscriptionInterface
     */
    public function setBoleto($boleto);

    /**
     * @return bool
     */
    public function getAllowInstallments();

    /**
     * @param bool $installments
     * @return ProductSubscriptionInterface
     */
    public function setAllowInstallments($installments);

    /**
     * @return \Pagarme\Core\Recurrence\Interfaces\RepetitionInterface[]|null
     */
    public function getRepetitions();

    /**
     * @param \Pagarme\Core\Recurrence\Interfaces\RepetitionInterface[] $repetitions
     * @return ProductSubscriptionInterface
     */
    public function setRepetitions(array $repetitions);

    /**
     * @return bool
     */
    public function getSellAsNormalProduct();

    /**
     * @param bool $sellAsNormalProduct
     * @return ProductSubscriptionInterface
     */
    public function setSellAsNormalProduct($sellAsNormalProduct);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param mixed $createdAt
     * @return ProductSubscriptionInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * @param mixed $updatedAt
     * @return ProductSubscriptionInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt);
}