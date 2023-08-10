<?php

namespace Pagarme\Core\Middle\Interfaces;

/**
 * This interface ensures that the implementation works in the old way too.
 */
interface ConvertToLegacyInterface
{
    public function convertToLegacy();
}
