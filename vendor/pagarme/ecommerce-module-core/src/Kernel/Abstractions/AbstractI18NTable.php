<?php

namespace Pagarme\Core\Kernel\Abstractions;

use Pagarme\Core\Kernel\Interfaces\I18NTableInterface;

abstract class AbstractI18NTable implements I18NTableInterface
{
    /**
     *
     * @param  string $string
     * @return string
     */
    public function get($string)
    {
        $table = $this->getTable();

        $result = null;
        if (isset($table[$string])) {
            $result = $table[$string];
        }
        return $result;
    }

    abstract protected function getTable();
}