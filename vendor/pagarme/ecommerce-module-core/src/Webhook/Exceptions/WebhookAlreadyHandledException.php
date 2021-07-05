<?php

namespace Pagarme\Core\Webhook\Exceptions;

use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Webhook\Aggregates\Webhook;

class WebhookAlreadyHandledException extends AbstractPagarmeCoreException
{
    /**
     * WebhookHandlerNotFound constructor.
     */
    public function __construct(Webhook $webhook)
    {
        $message = "Webhoook {$webhook->getPagarmeId()->getValue()} already handled!";
        parent::__construct($message, 200);
    }
}