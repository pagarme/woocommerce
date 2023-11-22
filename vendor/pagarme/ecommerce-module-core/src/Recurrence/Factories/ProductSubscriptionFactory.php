<?php

namespace Pagarme\Core\Recurrence\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;

class ProductSubscriptionFactory implements FactoryInterface
{
    /**
     * @var ProductSubscription
     */
    protected $productSubscription;

    public function __construct()
    {
        $this->productSubscription = new ProductSubscription();
    }
    /**
     *
     * @param array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData)
    {
        if (!is_array($postData)) {
            return;
        }

        $this->setId($postData);
        $this->setProductId($postData);
        $this->setCreditCard($postData);
        $this->setAllowInstallments($postData);
        $this->setBoleto($postData);
        $this->setSellAsNormalProduct($postData);
        $this->setBillingType($postData);
        $this->setRepetitions($postData);
        $this->setUpdatedAt($postData);
        $this->setCreatedAt($postData);
        $this->setApplyDiscountInAllProductCycles($postData);

        return $this->productSubscription;
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        if (!is_array($dbData)) {
            return;
        }

        $this->productSubscription->setId($dbData['id'])
            ->setProductId($dbData['product_id'])
            ->setCreditCard(boolval($dbData['credit_card']))
            ->setBoleto(boolval($dbData['boleto']))
            ->setAllowInstallments(boolval($dbData['allow_installments']))
            ->setSellAsNormalProduct(boolval($dbData['sell_as_normal_product']))
            ->setBillingType($dbData['billing_type'])
            ->setApplyDiscountInAllProductCycles($dbData['apply_discount_in_all_product_cycles'] ?? false);

        $this->setCreatedAt($dbData);
        $this->setUpdatedAt($dbData);

        return $this->productSubscription;
    }

    protected function setRepetitions($postData)
    {
        if (empty($postData['repetitions'])) {
            return;
        }

        foreach ($postData['repetitions'] as $repetition) {
            if (
                empty($repetition['interval_count']) ||
                empty($repetition['interval'])
            ) {
                continue;
            }

            $repetitionFactory = new RepetitionFactory();
            $repetitionEntity =
                $repetitionFactory->createFromPostData($repetition);

            $this->productSubscription->addRepetition($repetitionEntity);
        }
    }

    private function setId($postData)
    {
        if (!empty($postData['id'])) {
            $this->productSubscription->setId($postData['id']);
        }
    }

    private function setBillingType($postData)
    {
        $this->productSubscription->setBillingType('PREPAID');
    }

    private function setCreditCard($postData)
    {
        if (isset($postData['credit_card']) && is_bool($postData['credit_card'])) {
            $this->productSubscription->setCreditCard($postData['credit_card']);
        }
    }

    private function setBoleto($postData)
    {
        if (isset($postData['boleto']) && is_bool($postData['boleto'])) {
            $this->productSubscription->setBoleto($postData['boleto']);
        }
    }

    private function setSellAsNormalProduct($postData)
    {
        if (
            isset($postData['sell_as_normal_product']) &&
            is_bool($postData['sell_as_normal_product'])
        ) {
            $this->productSubscription->setSellAsNormalProduct(
                $postData['sell_as_normal_product']
            );
        }
    }

    private function setAllowInstallments($postData)
    {
        if (
            isset($postData['allow_installments']) &&
            is_bool($postData['allow_installments'])
        ) {
            $this->productSubscription->setAllowInstallments(
                $postData['allow_installments']
            );
        }
    }

    private function setProductId($postData)
    {
        if (isset($postData['product_id'])) {
            $this->productSubscription->setProductId($postData['product_id']);
        }
    }

    private function setUpdatedAt($postData)
    {
        if (isset($postData['updated_at'])) {
            $this->productSubscription->setUpdatedAt(
                new \Datetime($postData['updated_at'])
            );
        }
    }

    private function setCreatedAt($postData)
    {
        if (isset($postData['created_at'])) {
            $this->productSubscription->setCreatedAt(
                new \Datetime($postData['created_at'])
            );
        }
    }

    private function setApplyDiscountInAllProductCycles($postData)
    {
        if (
            isset($postData['apply_discount_in_all_product_cycles'])
            && is_bool($postData['apply_discount_in_all_product_cycles'])
        ) {
            $this->productSubscription->setApplyDiscountInAllProductCycles($postData['apply_discount_in_all_product_cycles']);
        }
    }
}
