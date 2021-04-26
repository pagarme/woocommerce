<?php

namespace Pagarme\Core\Maintenance\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Services\VersionService;
use Pagarme\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class VersionInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $versionService = new VersionService();

        $info = new \stdClass();

        $info->phpVersion = phpversion();
        $info->platformCoreConcreteClass = MPSetup::get(MPSetup::CONCRETE_MODULE_CORE_SETUP_CLASS);
        $info->moduleVersion = $versionService->getModuleVersion();
        $info->coreVersion = $versionService->getCoreVersion();
        $info->platformVersion = $versionService->getPlatformVersion();

        return $info;
    }
}