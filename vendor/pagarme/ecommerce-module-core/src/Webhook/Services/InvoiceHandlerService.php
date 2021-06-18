<?php

namespace Pagarme\Core\Webhook\Services;

use Exception;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Factories\ChargeFactory;
use Pagarme\Core\Kernel\Responses\ServiceResponse;
use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\ChargeService;
use Pagarme\Core\Webhook\Aggregates\Webhook;

class InvoiceHandlerService
{
    const COMPONENT_KERNEL = 'Kernel';
    const COMPONENT_RECURRENCE = 'Recurrence';

    /**
     * @param $component
     * @throws Exception
     */
    public function build($component)
    {
        $listInvoiceHandleService = [
            self::COMPONENT_RECURRENCE => new InvoiceRecurrenceService()
        ];

        if (empty($listInvoiceHandleService[$component])) {
            throw new Exception('Não foi encontrado o tipo de charge a ser carregado', 400);
        }

        return $listInvoiceHandleService[$component];
    }

    /**
     * @param Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws Exception
     */
    public function handle(Webhook $webhook)
    {
        $handler = $this->build($webhook->getComponent());
        return $handler->handle($webhook);
    }
}
