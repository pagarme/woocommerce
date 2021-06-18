<?php

namespace Pagarme\Core\Webhook\Factories;

use Pagarme\Core\Kernel\Exceptions\InvalidClassException;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\Services\FactoryService;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Webhook\ValueObjects\WebhookId;
use Pagarme\Core\Webhook\ValueObjects\WebhookType;

class WebhookFactory implements FactoryInterface
{
    /**
     *
     * @param  $postData
     * @return Webhook
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidClassException
     */
    public function createFromPostData($postData)
    {
        $webhook = new Webhook();

        $webhook->setPagarmeId(new WebhookId($postData->id));
        $webhook->setType(WebhookType::fromPostType($postData->type));
        $webhook->setComponent($postData->data);

        $factoryService = new FactoryService;

        try {
            $entityFactory =
                $factoryService->getFactoryFor(
                    $webhook->getComponent(),
                    $webhook->getType()->getEntityType()
                );
        }catch(InvalidClassException $e) {
            throw new WebhookHandlerNotFoundException($webhook);
        }

        $entity = $entityFactory->createFromPostData($postData->data);

        $webhook->setEntity($entity);

        return $webhook;
    }

    /**
     *
     * @param  $dbData
     * @return Webhook
     */
    public function createFromDbData($dbData)
    {
        $webhook = new Webhook();

        $webhook->setId($dbData['id']);
        $webhook->setPagarmeId(new WebhookId($dbData['pagarme_id']));

        return $webhook;
    }
}
