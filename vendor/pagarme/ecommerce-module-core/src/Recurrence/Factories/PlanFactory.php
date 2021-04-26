<?php

namespace Pagarme\Core\Recurrence\Factories;

use Magento\Catalog\Block\Product\Price;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Aggregates\SubProduct;
use Pagarme\Core\Recurrence\ValueObjects\DueValueObject;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Core\Recurrence\ValueObjects\PlanId;
use Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject as PricingScheme;

class PlanFactory implements FactoryInterface
{
    private $plan;
    private $intervalType;
    private $intervalCount;

    public function __construct()
    {
        $this->plan  = new Plan();
    }

    private function setPagarmeId($postData)
    {
        if (!empty($postData['plan_id'])) {
            $this->plan->setPagarmeId(new PlanId($postData['plan_id']));
            return;
        }
    }

    private function setIntervalType($postData)
    {
        if (isset($postData['interval_type'])) {
            $this->intervalType = $postData['interval_type'];
            return;
        }
    }

    private function setIntervalCount($postData)
    {
        if (isset($postData['interval_count'])) {
            $this->intervalCount = $postData['interval_count'];
            return;
        }
    }

    private function setId($postData)
    {
        if (!empty($postData['id'])) {
            $this->plan->setId($postData['id']);
            return;
        }

        $this->plan->setId(null);
    }

    private function setName($postData)
    {
        if (isset($postData['name'])) {
            $this->plan->setName($postData['name']);
            return;
        }
    }

    private function setDescription($postData)
    {
        if (isset($postData['description'])) {
            $this->plan->setDescription($postData['description']);
            return;
        }
    }

    private function setBillingType($postData)
    {
        $this->plan->setBillingType('PREPAID');
    }

    private function setCreditCard($postData)
    {
        if (isset($postData['credit_card']) && is_bool($postData['credit_card'])) {
            $this->plan->setCreditCard($postData['credit_card']);
            return;
        }
    }

    private function setBoleto($postData)
    {
        if (isset($postData['boleto']) && is_bool($postData['boleto'])) {
            $this->plan->setBoleto($postData['boleto']);
            return;
        }
    }

    private function setAllowInstallments($postData)
    {
        if (isset($postData['installments']) && is_bool($postData['installments'])) {
            $this->plan->setAllowInstallments($postData['installments']);
            return;
        }
    }

    private function setProductId($postData)
    {
        if (isset($postData['product_id'])) {
            $this->plan->setProductId($postData['product_id']);
            return;
        }
    }

    private function setUpdatedAt($postData)
    {
        if (isset($postData['updated_at'])) {
            $this->plan->setUpdatedAt(new \Datetime($postData['updated_at']));
            return;
        }
    }

    private function setCreatedAt($postData)
    {
        if (isset($postData['created_at'])) {
            $this->plan->setCreatedAt(new \Datetime($postData['created_at']));
            return;
        }
    }

    private function setStatus($postData)
    {
        if (isset($postData['status'])) {
            $this->plan->setStatus($postData['status']);
            return;
        }
    }

    private function setInterval()
    {
        $intervalCount = $this->intervalCount;
        $intervalType = $this->intervalType;

        if (isset($intervalType) && isset($intervalCount)) {
            $this->plan->setIntervalType($intervalType);
            $this->plan->setIntervalCount($intervalCount);
            return;
        }
    }

    private function setItems($postData)
    {
        if (!empty($postData['items'])) {
            foreach ($postData['items'] as $item) {
                $subProductFactory = new SubProductFactory();
                $subProduct = $subProductFactory->createFromPostData($item);
                $subProduct->setRecurrenceType($this->plan->getRecurrenceType());
                $items[] = $subProduct;
            }

            $this->plan->setItems($items);
            return;
        }
    }

    private function setTrialDays($postData)
    {
        if (isset($postData['trial_period_days'])) {
            $this->plan->setTrialPeriodDays((int) $postData['trial_period_days']);
        }
        return;
    }

    /**
     *
     * @param  array $postData
     * @return Plan
     */
    public function createFromPostData($postData)
    {
        if (!is_array($postData)) {
            return;
        }

        $this->setPagarmeId($postData);
        $this->setIntervalType($postData);
        $this->setIntervalCount($postData);
        $this->setId($postData);
        $this->setName($postData);
        $this->setDescription($postData);
        $this->setBillingType($postData);
        $this->setCreditCard($postData);
        $this->setBoleto($postData);
        $this->setAllowInstallments($postData);
        $this->setProductId($postData);
        $this->setUpdatedAt($postData);
        $this->setCreatedAt($postData);
        $this->setStatus($postData);
        $this->setInterval();
        $this->setItems($postData);
        $this->setTrialDays($postData);

        return $this->plan;
    }

    public function createFromDbData($dbData)
    {
        if (!is_array($dbData)) {
            return;
        }

        $this->setPagarmeId($dbData);
        $this->setIntervalType($dbData);
        $this->setIntervalCount($dbData);
        $this->setId($dbData);
        $this->setName($dbData);
        $this->setDescription($dbData);
        $this->setBillingType($dbData);
        $this->setProductId($dbData);
        $this->setUpdatedAt($dbData);
        $this->setCreatedAt($dbData);
        $this->setStatus($dbData);
        $this->setInterval();
        $this->setItems($dbData);
        $this->setTrialDays($dbData);

        $this->plan->setCreditCard(boolval($dbData['credit_card']));
        $this->plan->setAllowInstallments(boolval($dbData['installments']));
        $this->plan->setBoleto(boolval($dbData['boleto']));

        return $this->plan;
    }
}
