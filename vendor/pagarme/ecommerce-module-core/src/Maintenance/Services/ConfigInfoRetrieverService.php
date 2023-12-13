<?php

namespace Pagarme\Core\Maintenance\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class ConfigInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $obfuscated = json_decode(
            json_encode(
                AbstractModuleCoreSetup::getModuleConfiguration()
            )
        );

        $obfuscated->hubInstallId = $this->obfuscate($obfuscated->hubInstallId);

        $skAttr = Configuration::KEY_SECRET;
        $obfuscated->keys->$skAttr = $this->obfuscate($obfuscated->keys->$skAttr);

        if ($obfuscated->parent !== null) {
            $obfuscated->parent->hubInstallId = $this->obfuscate($obfuscated->parent->hubInstallId);
            $obfuscated->parent->keys->$skAttr = $this->obfuscate($obfuscated->parent->keys->$skAttr);
        }

        return $obfuscated;
    }

    //@todo This method could be useful in other places, so move it to another class.
    private function obfuscate($value, $replacement = '*', $start = 2, $end = 2)
    {
        if ($value === null) {
            return null;
        }
        $limit = -1;
        $strlen = strlen($value);

        $init = '';
        for($i = 0; $i < $start; $i++) {
            $init .= $replacement;
        }

        if ($strlen > $start + $end) {
            $limit = $strlen - $end;
            $init = substr($value, 0, $start);
        }
        
        $obfuscated = preg_replace('/./', $replacement, $value ?? '', $limit);

        return $init . substr($obfuscated, $start);
    }
}