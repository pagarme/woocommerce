<?php

namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Webhook\Exceptions\WebhookAlreadyHandledException;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Webhook\Factories\WebhookFactory;
use Pagarme\Core\Webhook\Repositories\WebhookRepository;
use Pagarme\Core\Webhook\ValueObjects\WebhookId;

class WebhookReceiverService
{
    /**
     *
     * @param  $postData
     * @return mixed
     * @throws NotFoundException
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidClassException
     */
    public function handle($postData)
    {
        $logService = new LogService(
            'Webhook',
            true
        );
        try {
            $logService->info("Received", $postData);

            $repository = new WebhookRepository();
            $webhook = $repository->findByPagarmeId(new WebhookId($postData->id));
            if ($webhook !== null) {
                throw new WebhookAlreadyHandledException($webhook);
            }

            $factory = new WebhookFactory();
            $webhook = $factory->createFromPostData($postData);

            $handlerService = $this->getHandlerServiceFor($webhook);

            $return = $handlerService->handle($webhook);
            $repository->save($webhook);
            $logService->info(
                "Webhook handled successfuly",
                (object)[
                    'id' => $webhook->getId(),
                    'pagarmeId' => $webhook->getPagarmeId(),
                    'result' => $return
                ]
            );

            return $return;
        } catch(AbstractPagarmeCoreException $e) {
            $logService->exception($e);
            throw $e;
        }
    }

    private function getHandlerServiceFor(Webhook $webhook)
    {
        $handlerServiceClass =
            'Pagarme\\Core\\Webhook\\Services\\' .
            ucfirst($webhook->getType()->getEntityType()).
            'HandlerService';

        if (!class_exists($handlerServiceClass)) {
            throw new WebhookHandlerNotFoundException($webhook);
        }

        /**
         *
         * @var AbstractHandlerService $handlerService
         */
        return new $handlerServiceClass();
    }
}
