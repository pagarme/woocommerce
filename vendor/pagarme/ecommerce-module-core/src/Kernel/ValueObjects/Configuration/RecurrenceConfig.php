<?php

namespace Pagarme\Core\Kernel\ValueObjects\Configuration;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class RecurrenceConfig extends AbstractValueObject
{
    /** @var bool */
    private $enabled;

    /** @var bool */
    private $showRecurrenceCurrencyWidget;

    /** @var bool */
    private $purchaseRecurrenceProductWithNormalProduct;

    /** @var string */
    private $conflictMessageRecurrenceProductWithNormalProduct;

    /** @var bool */
    private $purchaseRecurrenceProductWithRecurrenceProduct;

    /** @var string */
    private $conflictMessageRecurrenceProductWithRecurrenceProduct;

    /** @var bool */
    private $decreaseStock;

    /**
     * @return bool
     */
    public function isDecreaseStock()
    {
        return $this->decreaseStock;
    }

    /**
     * @param bool $decreaseStock
     * @return RecurrenceConfig
     */
    public function setDecreaseStock($decreaseStock)
    {
        $this->decreaseStock = $decreaseStock;
        return $this;
    }

    /**
     * @return bool
     */
    public function isShowRecurrenceCurrencyWidget()
    {
        return $this->showRecurrenceCurrencyWidget;
    }

    /**
     * @param bool $showRecurrenceCurrencyWidget
     * @return $this
     */
    public function setShowRecurrenceCurrencyWidget($showRecurrenceCurrencyWidget)
    {
        $this->showRecurrenceCurrencyWidget = $showRecurrenceCurrencyWidget;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return RecurrenceConfig
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPurchaseRecurrenceProductWithNormalProduct()
    {
        return $this->purchaseRecurrenceProductWithNormalProduct;
    }

    /**
     * @param bool $purchaseRecurrenceProductWithNormalProduct
     */
    public function setPurchaseRecurrenceProductWithNormalProduct(
        $purchaseRecurrenceProductWithNormalProduct
    ) {
        $this->purchaseRecurrenceProductWithNormalProduct =
            $purchaseRecurrenceProductWithNormalProduct;
    }

    /**
     * @return string
     */
    public function getConflictMessageRecurrenceProductWithNormalProduct()
    {
        return $this->conflictMessageRecurrenceProductWithNormalProduct;
    }

    /**
     * @param string $conflictMessageRecurrenceProductWithNormalProduct
     */
    public function setConflictMessageRecurrenceProductWithNormalProduct(
        $conflictMessageRecurrenceProductWithNormalProduct
    ) {
        $this->conflictMessageRecurrenceProductWithNormalProduct =
            $conflictMessageRecurrenceProductWithNormalProduct;
    }

    /**
     * @return bool
     */
    public function isPurchaseRecurrenceProductWithRecurrenceProduct()
    {
        return $this->purchaseRecurrenceProductWithRecurrenceProduct;
    }

    /**
     * @param bool $purchaseRecurrenceProductWithRecurrenceProduct
     */
    public function setPurchaseRecurrenceProductWithRecurrenceProduct(
        $purchaseRecurrenceProductWithRecurrenceProduct
    ) {
        $this->purchaseRecurrenceProductWithRecurrenceProduct =
            $purchaseRecurrenceProductWithRecurrenceProduct;
    }

    /**
     * @return string
     */
    public function getConflictMessageRecurrenceProductWithRecurrenceProduct()
    {
        return $this->conflictMessageRecurrenceProductWithRecurrenceProduct;
    }

    /**
     * @param string $conflictMessageRecurrenceProductWithRecurrenceProduct
     */
    public function setConflictMessageRecurrenceProductWithRecurrenceProduct(
        $conflictMessageRecurrenceProductWithRecurrenceProduct
    ) {
        $this->conflictMessageRecurrenceProductWithRecurrenceProduct =
            $conflictMessageRecurrenceProductWithRecurrenceProduct;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->enabled === $object->isEnabled() &&
            $this->showRecurrenceCurrencyWidget === $object->isShowRecurrenceCurrencyWidget() &&
            $this->purchaseRecurrenceProductWithNormalProduct === $object->isPurchaseRecurrenceProductWithNormalProduct() &&
            $this->conflictMessageRecurrenceProductWithNormalProduct === $object->getConflictMessageRecurrenceProductWithNormalProduct() &&
            $this->purchaseRecurrenceProductWithRecurrenceProduct === $object->isPurchaseRecurrenceProductWithRecurrenceProduct() &&
            $this->conflictMessageRecurrenceProductWithRecurrenceProduct === $object->getConflictMessageRecurrenceProductWithRecurrenceProduct();
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

        $obj->enabled = $this->isEnabled();

        $obj->showRecurrenceCurrencyWidget =
            $this->isShowRecurrenceCurrencyWidget();

        $obj->purchaseRecurrenceProductWithNormalProduct =
            $this->isPurchaseRecurrenceProductWithNormalProduct();

        $obj->conflictMessageRecurrenceProductWithNormalProduct =
            $this->getConflictMessageRecurrenceProductWithNormalProduct();

        $obj->purchaseRecurrenceProductWithRecurrenceProduct =
            $this->isPurchaseRecurrenceProductWithRecurrenceProduct();

        $obj->conflictMessageRecurrenceProductWithRecurrenceProduct =
            $this->getConflictMessageRecurrenceProductWithRecurrenceProduct();

        $obj->decreaseStock = $this->isDecreaseStock();

        return $obj;
    }
}
