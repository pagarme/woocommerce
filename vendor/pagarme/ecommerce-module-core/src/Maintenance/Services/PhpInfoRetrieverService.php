<?php

namespace Pagarme\Core\Maintenance\Services;

use Pagarme\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class PhpInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        ob_start();
        phpinfo();
        $phpinfoAsString = ob_get_contents();
        ob_get_clean();

        return $phpinfoAsString;
    }
}