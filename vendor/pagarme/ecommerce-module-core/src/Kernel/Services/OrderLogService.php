<?php

namespace Pagarme\Core\Kernel\Services;

final class OrderLogService extends LogService
{
    public function __construct($stackTraceDepth = 3, $addHost = true)
    {
        parent::__construct('Order', $addHost);
        $this->stackTraceDepth = $stackTraceDepth;
    }

    public function orderInfo($orderCode, $message, $sourceObject = null)
    {
        try {
            $orderMessage = "Order #$orderCode : $message";
            parent::info($orderMessage, $sourceObject);
        } catch (\Throwable $throwable) {
            $this->orderException($throwable, $orderCode);
        }
    }

    public function orderException($exception, $orderCode)
    {
        $exceptionMessage = $exception->getMessage();
        $exceptionMessage = "Order #$orderCode : $exceptionMessage";

        $reflection = new \ReflectionClass($exception);
        $property = $reflection->getProperty('message');
        $property->setAccessible(true);
        $property->setValue($exception, $exceptionMessage);
        $property->setAccessible(false);

        parent::exception($exception);
    }
}
