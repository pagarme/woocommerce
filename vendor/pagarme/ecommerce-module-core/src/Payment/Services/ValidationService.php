<?php

namespace Pagarme\Core\Payment\Services;

use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;

class ValidationService
{
    /** @var array */
    protected $errors = [];

    public function validatePayment(PlatformOrderInterface $order)
    {
        try {
            $order->getCustomer();
        } catch (\Exception $exception) {
            $this->addError($exception->getMessage());
        } catch (\Throwable $throwable) {
            $this->addError($throwable->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }
}