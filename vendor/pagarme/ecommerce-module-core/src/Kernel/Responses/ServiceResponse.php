<?php

namespace Pagarme\Core\Kernel\Responses;

final class ServiceResponse
{

    /** @var bool */
    private $success;
    /** @var string */
    private $message;
    /** @var object */
    private $object;

    public function __construct($success, $message, $object = null)
    {
        $this->message  = $message;
        $this->success = $success;
        $this->object = $object;
    }

    public function isSuccess()
    {
        return $this->success;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getObject()
    {
        return $this->object;
    }
}