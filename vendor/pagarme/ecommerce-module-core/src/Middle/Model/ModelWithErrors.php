<?php

namespace Pagarme\Core\Middle\Model;

abstract class ModelWithErrors
{
    /**
     * @var array
     */
    private $errors = [];

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function setError(string $error = null)
    {
        if (empty($error)) {
            return;
        }
        $this->errors[] = $error;
    }
}
