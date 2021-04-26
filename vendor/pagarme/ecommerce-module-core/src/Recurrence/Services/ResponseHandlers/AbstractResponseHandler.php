<?php


namespace Pagarme\Core\Recurrence\Services\ResponseHandlers;

use Pagarme\Core\Kernel\Services\OrderLogService;

abstract class AbstractResponseHandler
{
    protected $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
}