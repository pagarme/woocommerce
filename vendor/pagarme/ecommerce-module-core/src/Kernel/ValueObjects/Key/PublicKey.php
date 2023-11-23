<?php

namespace Pagarme\Core\Kernel\ValueObjects\Key;

use Pagarme\Core\Kernel\Interfaces\SensibleDataInterface;

final class PublicKey extends AbstractPublicKey implements SensibleDataInterface
{
    protected function validateValue($value)
    {
        return preg_match('/^pk_\w{16}$/', $value ?? '') === 1;
    }

    /**
     *
     * @param string
     * @return string
     */
    public function hideSensibleData($string)
    {
        // TODO: Implement hideSensibleData() method.
    }
}