<?php


namespace Pagarme\Core\Payment\Services\ResponseHandlers;

use Pagarme\Core\Kernel\Services\OrderLogService;
use Pagarme\Core\Payment\Interfaces\ResponseHandlerInterface;

abstract class AbstractResponseHandler implements ResponseHandlerInterface
{
    protected $logService;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
}