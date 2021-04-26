<?php

namespace Pagarme\Core\Hub\Commands;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as  MPSetup;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Factories\ConfigurationFactory;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;

class UninstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if (!$moduleConfig->isHubEnabled()) {
            throw new Exception("Hub is not installed!");
        }

        $hubKey = $moduleConfig->getSecretKey();
        if (!$hubKey->equals($this->getAccessToken())) {
            throw new Exception("Access Denied.");
        }

        $cleanConfig = json_decode(json_encode($moduleConfig));
        $cleanConfig->keys = [
            Configuration::KEY_SECRET => null,
            Configuration::KEY_PUBLIC => null,
        ];
        $cleanConfig->testMode = true;
        $cleanConfig->hubInstallId = null;

        $cleanConfig = json_encode($cleanConfig);
        $configFactory = new ConfigurationFactory();
        $cleanConfig = $configFactory->createFromJsonData($cleanConfig);

        $method = $cleanConfig->getMethodsInherited();

        $methodInherited = array_merge($method, ['getSecretKey', 'getPublicKey', 'isHubEnabled']);

        $cleanConfig->setMethodsInherited(array_unique($methodInherited));


        $cleanConfig->setId($moduleConfig->getId());
        MPSetup::setModuleConfiguration($cleanConfig);
        
        $configRepo = new ConfigurationRepository();

        $configRepo->save($cleanConfig);
    }
}