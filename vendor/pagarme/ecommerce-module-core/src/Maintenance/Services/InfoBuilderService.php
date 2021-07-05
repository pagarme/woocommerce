<?php

namespace Pagarme\Core\Maintenance\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Abstractions\AbstractPlatformOrderDecorator;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class InfoBuilderService
{

    /**
     *
     * @param  array $query
     * @return string|array
     */
    public function buildInfoFromQueryArray(array $query)
    {
        $infos = [];
        if (!$this->isTokenValid($query)) {
            return [];
        }

        foreach ($query as $parameter => $value) {
            $infoRetriever = $this->getInfoRetrieverServiceFor($parameter);
            if ($infoRetriever === null) {
                continue;
            }

            $data = $infoRetriever->retrieveInfo($value);
            if (is_string($data)) {
                return $data;
            }
            $infos[$parameter] = $data;
        }
        return $infos;
    }

    /**
     *
     * @param  $parameter
     * @return null|InfoRetrieverServiceInterface
     */
    private function getInfoRetrieverServiceFor($parameter)
    {
        $infoRetrieverServiceClass =
            'Pagarme\\Core\\Maintenance\\Services\\' .
            ucfirst($parameter) .
            'InfoRetrieverService';

        if (!class_exists($infoRetrieverServiceClass)) {
            return null;
        }

        return new $infoRetrieverServiceClass();
    }


    private function isTokenValid($token)
    {
        if (is_array($token)) {
            if (!isset($token['token'])) {
                return false;
            }
            $token = $token['token'];
        }

        $passedKeyHash = base64_decode($token);

        $moduleConfig = AbstractModuleCoreSetup::getModuleConfiguration();
        $secretKey = $moduleConfig->getSecretKey();

        if ($secretKey === null) {
            return false;
        }

        $secretKeyHash = $this->generateKeyHash($secretKey->getValue());

        return $secretKeyHash === $passedKeyHash;
    }

    public function generateKeyHash($keyValue)
    {
        return hash('sha512', $keyValue);
    }
}