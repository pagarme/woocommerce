<?php

namespace Pagarme\Core\Recurrence\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Aggregates\SubscriptionItem;
use Pagarme\Core\Recurrence\ValueObjects\SubscriptionItemId;

class SubscriptionItemFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscriptionItem = new SubscriptionItem();

        $subscriptionItem->setSubscriptionId(new SubscriptionId($postData['subscription_id']));
        $subscriptionItem->setPagarmeId(new SubscriptionItemId($postData['id']));
        $subscriptionItem->setCode($postData['code']);
        $subscriptionItem->setQuantity($postData['quantity']);

        return $subscriptionItem;
    }
    /**
     * @param array $dbData
     * @return AbstractEntity|Subscription
     * @throws InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        $subscriptionItem = new SubscriptionItem();

        $subscriptionItem->setId($dbData["id"]);
        $subscriptionItem->setSubscriptionId(new SubscriptionId($dbData['subscription_id']));
        $subscriptionItem->setPagarmeId(new SubscriptionItemId($dbData['pagarme_id']));
        $subscriptionItem->setCode($dbData['code']);
        $subscriptionItem->setQuantity($dbData['quantity']);

        return $subscriptionItem;
    }
}
