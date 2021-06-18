<?php

namespace Pagarme\Core\Webhook\Exceptions;

use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Webhook\Aggregates\Webhook;

class UnprocessableWebhookException extends AbstractPagarmeCoreException
{
    /**
     * UnprocessableWebhookException constructor.
     */
    public function __construct($message, $code = 422)
    {
        parent::__construct($message, $code);
    }
}