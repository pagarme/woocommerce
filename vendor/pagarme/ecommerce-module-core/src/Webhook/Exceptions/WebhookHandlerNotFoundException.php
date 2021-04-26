<?php

namespace Pagarme\Core\Webhook\Exceptions;

use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Webhook\Aggregates\Webhook;

class WebhookHandlerNotFoundException extends AbstractPagarmeCoreException
{
    /**
     * WebhookHandlerNotFound constructor.
     */
    public function __construct(Webhook $webhook)
    {
        $message =
            "Handler for {$webhook->getType()->getEntityType()}." .
            "{$webhook->getType()->getAction()} webhook not found!";
        parent::__construct($message, 200, null);
    }
}