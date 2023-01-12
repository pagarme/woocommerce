<?php

namespace Pagarme\Core\Kernel\Services;

use Exception;
use PagarmeCoreApiLib\Models\CreateOrderRequest;
use PagarmeCoreApiLib\PagarmeCoreApiClient;

class OrderCreationService
{
    /**
     * @var PagarmeCoreApiClient
     */
    private $pagarmeCoreAPIClient;

    /**
     * @var OrderLogService
     */
    private $logService;

    /**
     * @var int
     */
    private $generalAttempt = 1;

    public function __construct(PagarmeCoreApiClient $pagarmeCoreAPIClient)
    {
        $this->pagarmeCoreAPIClient = $pagarmeCoreAPIClient;
        $this->logService = new OrderLogService(2);
    }

    /**
     * @param CreateOrderRequest $orderRequest
     * @param string $idempotencyKey
     * @param int $attempt
     * @return string|bool - json string
     * @throws Exception
     */
    public function createOrder(
        CreateOrderRequest $orderRequest,
                           $idempotencyKey,
                           $attempt = 1
    ) {
        $shouldRetry = false;
        $response = null;
        $messageLog = "";

        $orderController = $this->pagarmeCoreAPIClient->getOrders();

        try {
            $response = $orderController->createOrder($orderRequest, $idempotencyKey);
        } catch (Exception $exception) {
            $messageLog = $exception->getMessage();
            $shouldRetry = $this->shouldRetry($exception);
        }

        if ($shouldRetry && $attempt > 1) {
            sleep(3);

            $currentAttempt = ($attempt - 1);
            $this->generalAttempt++;

            $this->logService->orderInfo(
                $orderRequest->code,
                "Try create order Request attempts: {$this->generalAttempt}",
                [$messageLog]
            );

            return $this->createOrder(
                $orderRequest,
                $idempotencyKey,
                $currentAttempt
            );
        }

        if ($response == null) {
            throw $exception;
        }

        $this->logService->orderInfo(
            $orderRequest->code,
            "Create order Response",
            $response
        );

        return json_decode(json_encode($response), true);
    }

    /**
     * @param Exception $exception
     * @return bool
     */
    private function shouldRetry(Exception $exception)
    {
        $shouldRetry = false;

        if (($exception->getCode() < 200) || ($exception->getCode() > 208)) {
            $shouldRetry = true;
        }

        if ($exception->getCode() == 422 || $exception->getCode() == 401) {
            $shouldRetry = false;
        }

        return $shouldRetry;
    }
}
