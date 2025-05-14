<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use Pagarme\Core\Kernel\Services\InstallmentService;
use PagarmeCoreApiLib\Models\CreatePlanRequest;
use PagarmeCoreApiLib\Models\UpdatePlanRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Recurrence\Interfaces\RecurrenceEntityInterface;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Core\Recurrence\Interfaces\ProductPlanInterface;

final class Plan extends AbstractEntity implements RecurrenceEntityInterface, ProductPlanInterface
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const RECURRENCE_TYPE = "plan";

    protected $id = null;
    private $interval;
    private $intervalType;
    private $intervalCount;
    private $name;
    private $description;
    private $productId;
    private $creditCard;
    private $boleto;
    private $status;
    private $billingType;
    private $allowInstallments;
    private $createdAt;
    private $updatedAt;
    private $subProduct;
    private $items;
    private $trialPeriodDays;

    private $applyDiscountInAllProductCycles;

    /**
     * @return string
     */
    public function getRecurrenceType()
    {
        return self::RECURRENCE_TYPE;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Plan
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Plan
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Plan
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return IntervalValueObject
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param IntervalValueObject $interval
     * @return Plan
     */
    public function setInterval(IntervalValueObject $interval)
    {
        $this->interval = $interval;
        $this->intervalType = $interval->getIntervalType();
        $this->intervalCount = $interval->getIntervalCount();

        return $this;
    }

    /**
     * @param string $interval
     * @return $this
     * @throws \Exception
     */
    public function setIntervalType($intervalType)
    {
        $listIntervalAccept = [
            IntervalValueObject::INTERVAL_TYPE_WEEK,
            IntervalValueObject::INTERVAL_TYPE_DAY,
            IntervalValueObject::INTERVAL_TYPE_MONTH,
            IntervalValueObject::INTERVAL_TYPE_YEAR
        ];

        if (!in_array($intervalType, $listIntervalAccept)) {
            throw new \Exception('Interval not find');
        }

        $this->intervalType = $intervalType;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntervalType()
    {
        return $this->intervalType;
    }

    /**
     * @param int $intervalCount
     * @return $this
     * @throws \Exception
     */
    public function setIntervalCount($intervalCount)
    {
        if (!is_numeric($intervalCount)) {
            throw new \Exception('Interval count not compatible');
        }

        $this->intervalCount = $intervalCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalCount()
    {
        return $this->intervalCount;
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
     * @return Plan
     * @throws InvalidParamException
     */
    public function setProductId($productId)
    {
        if (!is_numeric($productId)) {
            throw new InvalidParamException(
                "Product id should be an integer!",
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
     * @return Plan
     */
    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;
        return $this;
    }

    /**
     * @return string true or false
     */
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * @param bool $boleto
     * @return Plan
     */
    public function setBoleto($boleto)
    {
        $this->boleto = $boleto;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Plan
     * @throws InvalidParamException
     */
    public function setStatus($status)
    {
        if (empty($status)) {
            throw new InvalidParamException(
                "Status should not be empty!",
                $status
            );
        }
        $this->status = $status;
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
     * @return Plan
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
     * @return Plan
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = $allowInstallments;
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
     * @return Plan
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
     * @return Plan
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt->format(self::DATE_FORMAT);
        return $this;
    }

    /**
     * @param array $items An array of Subproducts aggregate
     * @return Plan
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    public function addItem(SubProduct $item)
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * @return SubProduct
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getTrialPeriodDays()
    {
        return $this->trialPeriodDays;
    }

    /**
     * @param mixed $trialPeriodDays
     * @throws InvalidParamException
     */
    public function setTrialPeriodDays($trialPeriodDays)
    {
        if (!is_numeric($trialPeriodDays)) {
            throw new InvalidParamException(
                "Trial period days should be an integer!",
                $trialPeriodDays
            );
        }
        $this->trialPeriodDays = $trialPeriodDays;
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
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->pagarmeId = $this->getPagarmeId();
        $obj->intervalType = $this->getIntervalType();
        $obj->intervalCount = $this->getIntervalCount();
        $obj->productId = $this->getProductId();
        $obj->creditCard = $this->getCreditCard();
        $obj->boleto = $this->getBoleto();
        $obj->status = $this->getStatus();
        $obj->billingType = $this->getBillingType();
        $obj->allowInstallments = $this->getAllowInstallments();
        $obj->createdAt = $this->getCreatedAt();
        $obj->updatedAt = $this->getUpdatedAt();
        $obj->trialPeriodDays = $this->getTrialPeriodDays();
        $obj->items = $this->getItems();
        $obj->applyDiscountInAllProductCycles = $this->getApplyDiscountInAllProductCycles();

        return $obj;
    }

    public function convertToSdkRequest($update = false)
    {
        $planRequest = new CreatePlanRequest();
        if ($update) {
            $planRequest = new UpdatePlanRequest();
            $planRequest->status = $this->getStatus();
            $planRequest->currency = $this->getCurrency();
        }

        $planRequest->description = $this->getDescription();
        $planRequest->name = $this->getName();
        $planRequest->intervalCount = $this->getIntervalCount();
        $planRequest->interval = $this->getIntervalType();
        $planRequest->billingType = $this->getBillingType();

        if ($this->getCreditCard()) {
            $planRequest->paymentMethods[] = 'credit_card';
        }
        if ($this->getBoleto()) {
            $planRequest->paymentMethods[] = 'boleto';
        }

        $planRequest->installments = $this->getInstallmentsRequest();

        if (!empty($this->getTrialPeriodDays())) {
            $planRequest->trialPeriodDays = $this->getTrialPeriodDays();
        }

        $items = $this->getItems();
        if ($items !== null) {
            foreach ($items as $item) {
                $itemsSdk[] = $item->convertToSDKRequest();
            }
            $planRequest->items = $itemsSdk;
        }

        return $planRequest;
    }

    public function getInstallmentsRequest()
    {
        if ($this->getIntervalType() == IntervalValueObject::INTERVAL_TYPE_MONTH) {
            return range(1, $this->getIntervalCount());
        }
        return range(1, InstallmentService::MAX_PSP_INSTALLMENTS_NUMBER);
    }

    public function getCurrency()
    {
        return 'BRL';
    }
}
