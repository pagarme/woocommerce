<?php

namespace Pagarme\Core\Recurrence\Services;

use Pagarme\Core\Kernel\Interfaces\PlatformProductInterface;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\SubProduct;
use Pagarme\Core\Recurrence\Repositories\SubscriptionItemRepository;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Core\Recurrence\ValueObjects\SubscriptionItemId;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;

class RecurrenceService
{
    const MAX_INSTALLMENTS_NUMBER = 12;

    //@todo Change the function name because we've change the name of subscription product to recurrence product

    public function getRecurrenceProductByProductId($productId)
    {
        $productSubscription = $this->getProductSubscription($productId);
        if ($productSubscription !== null) {
            return $productSubscription;
        }

        $productPlan = $this->getProductPlan($productId);
        if ($productPlan !== null) {
            return $productPlan;
        }
        return null;
    }

    public function getMaxInstallmentByRecurrenceInterval(IntervalValueObject $interval)
    {
        if ($interval->getIntervalType() === IntervalValueObject::INTERVAL_TYPE_MONTH) {
            return $interval->getIntervalCount();
        }

        return self::MAX_INSTALLMENTS_NUMBER;
    }

    protected function getProductSubscription($productId)
    {
        $productSubscriptionService = new ProductSubscriptionService();
        return $productSubscriptionService->findByProductId($productId);
    }

    protected function getProductPlan($productId)
    {
        $productSubscriptionService = new PlanService();
        return $productSubscriptionService->findByProductId($productId);
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getSubProductByNameAndRecurrenceType($productName, $subscription)
    {
        $recurrenceType = $subscription->getRecurrenceType();

        if ($recurrenceType === Plan::RECURRENCE_TYPE) {
            $plan = (new PlanService)->findByPagarmeId(
                $subscription->getPlanId()
            );

            return $this->getProductByName($productName, $plan);
        }
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getSubscriptionItemByProductId($subscriptionItemId)
    {
        $subscriptionItemRepository = new SubscriptionItemRepository();
        return $subscriptionItemRepository->findByPagarmeId(
            new SubscriptionItemId($subscriptionItemId)
        );

    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getProductByName($productName, $recurrence)
    {
        foreach ($recurrence->getItems() as $item) {
            $product = $this->getProductDecorated($item->getProductId());
            $subProduct = new SubProduct();
            $subProduct->setName($product->getName());
            if ($productName == $subProduct->getName()) {
                return $item;
            }
            continue;
        }
    }

    /**
     * @todo Remove when be implemented code on mark1
     */
    public function getProductDecorated($id)
    {
        $productDecorator =
            AbstractModuleCoreSetup::get(
                AbstractModuleCoreSetup::CONCRETE_PRODUCT_DECORATOR_CLASS
            );

        /**
         * @var PlatformProductInterface $product
         */
        $product = new $productDecorator();
        $product->loadByEntityId($id);

        return $product;
    }

    public function getGreatestCyclesFromItems($items)
    {
        $cycles = 1;
        foreach ($items ?? [] as $item) {
            if ($item->getCycles() === null) {
                $cycles = $item->getCycles();
                break;
            }

            if ($cycles < $item->getCycles()) {
                $cycles = $item->getCycles();
            }
        }

        return $cycles;
    }
}
