<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Pagarme\Core\Recurrence\Interfaces\RepetitionInterface;

class ProductSubscription extends AbstractEntity implements ProductSubscriptionInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const RECURRENCE_TYPE = "subscription";

    /** @var int */
    protected $id = null;
    /** @var int */
    private $productId;
    /** @var boolean */
    private $creditCard = false;
    /** @var boolean */
    private $boleto = false;
    /** @var boolean */
    private $allowInstallments = false;
    /** @var Repetition[] */
    private $repetitions = [];
    /** @var boolean */
    private $sellAsNormalProduct = false;
    /** @var string */
    private $billingType = 'PREPAID';
    /** @var string */
    private $createdAt;
    /** @var string */
    private $updatedAt;
    /** @var bool */
    private $applyDiscountInAllProductCycles;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ProductSubscription
     */
    public function setId($id)
    {
        $this->id = intval($id);
        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     * @return ProductSubscription
     * @throws InvalidParamException
     */
    public function setProductId($productId)
    {
        if (empty($productId)) {
            throw new InvalidParamException(
                "Product id should not be empty!",
                $productId
            );
        }
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * @param bool $creditCard
     * @return ProductSubscription
     */
    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }

    /**
     * @return bool
     */
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * @param bool $boleto
     * @return ProductSubscription
     */
    public function setBoleto($boleto)
    {
        $this->boleto = $boleto;
        return $this;
    }

    /**
     * @return string
     */
    public function getBillingType()
    {
        return $this->billingType;
    }

    /**
     * @param string $billingType
     * @return ProductSubscription
     * @throws InvalidParamException
     */
    public function setBillingType($billingType)
    {
        if (empty($billingType)) {
            throw new InvalidParamException(
                "Billing type should not be empty!",
                $billingType
            );
        }
        $this->billingType = $billingType;
        return $this;
    }

    /**
     * @return int
     */
    public function getAllowInstallments()
    {
        return $this->allowInstallments;
    }

    /**
     * @param bool $allowInstallments
     * @return ProductSubscription
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = $allowInstallments;
        return $this;
    }

    /**
     * @return \Pagarme\Core\Recurrence\Interfaces\RepetitionInterface[]|null
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * @param \Pagarme\Core\Recurrence\Interfaces\RepetitionInterface[] $repetitions
     * @return ProductSubscriptionInterface
     */
    public function setRepetitions(array $repetitions)
    {
        $this->repetitions = $repetitions;
        return $this;
    }

    /**
     * @param RepetitionInterface $repetition
     * @return ProductSubscription
     */
    public function addRepetition(RepetitionInterface $repetition)
    {
        $this->repetitions[] = $repetition;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return ProductSubscription
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt->format(self::DATE_FORMAT);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return ProductSubscription
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
        return $this;
    }

    /**
     * @return bool
     */
    public function getApplyDiscountInAllProductCycles()
    {
        return boolval($this->applyDiscountInAllProductCycles);
    }

    /**
     * @param bool $applyDiscountInAllProductCycles
     */
    public function setApplyDiscountInAllProductCycles($applyDiscountInAllProductCycles)
    {
        $this->applyDiscountInAllProductCycles = $applyDiscountInAllProductCycles;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->productId = $this->getProductId();
        $obj->creditCard = $this->getCreditCard();
        $obj->boleto = $this->getBoleto();
        $obj->sellAsNormalProduct = $this->getSellAsNormalProduct();
        $obj->billintType = $this->getBillingType();
        $obj->allowInstallments = $this->getAllowInstallments();
        $obj->repetitions = $this->getRepetitions();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();
        $obj->applyDiscountInAllProductCycles = $this->getApplyDiscountInAllProductCycles();

        return $obj;
    }

    public function getRecurrenceType()
    {
        return self::RECURRENCE_TYPE;
    }

    /**
     * @return int
     */
    public function getSellAsNormalProduct()
    {
        return $this->sellAsNormalProduct;
    }

    /**
     * @param bool $sellAsNormalProduct
     * @return ProductSubscription
     */
    public function setSellAsNormalProduct($sellAsNormalProduct)
    {
        $this->sellAsNormalProduct = $sellAsNormalProduct;
        return $this;
    }

    public function checkProductHasSamePaymentMethod(ProductSubscription $productSubscription)
    {
        $paymentMethodAccept = [];
        if ($this->getBoleto()) {
            $paymentMethodAccept[] = 'boleto';
        }

        if ($this->getCreditCard()) {
            $paymentMethodAccept[] = 'creditCard';
        }

        $paymentMethodAcceptReceive = [];
        if ($productSubscription->getBoleto()) {
            $paymentMethodAcceptReceive[] = 'boleto';
        }

        if ($productSubscription->getCreditCard()) {
            $paymentMethodAcceptReceive[] = 'creditCard';
        }

        $compatible = array_intersect($paymentMethodAccept, $paymentMethodAcceptReceive);
        return !empty($compatible);
    }
}
