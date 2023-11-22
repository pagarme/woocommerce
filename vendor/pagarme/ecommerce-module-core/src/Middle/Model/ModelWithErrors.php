<?php

namespace Pagarme\Core\Middle\Model;

abstract class ModelWithErrors
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return void
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @param string|null $error
     * @return void
     */
    public function addError($error = null)
    {
        if (empty($error)) {
            return;
        }
        $this->errors[] = $error;
    }
}
