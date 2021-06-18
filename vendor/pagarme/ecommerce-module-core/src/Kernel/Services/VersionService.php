<?php

namespace Pagarme\Core\Kernel\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\ValueObjects\VersionInfo;

final class VersionService
{
    public function getCoreVersion()
    {
        $currentDir = __DIR__;

        do {
            $currentDir = explode(DIRECTORY_SEPARATOR, $currentDir);
            array_pop($currentDir);
            $currentDir = implode(DIRECTORY_SEPARATOR, $currentDir);

            if (strpos($currentDir, 'ecommerce-module-core') === false) {
                return 'x.x.x';
            }

            $composerJsonFilename =  $currentDir . DIRECTORY_SEPARATOR . 'composer.json';

        } while (!file_exists($composerJsonFilename));

        $composerData = json_decode(file_get_contents($composerJsonFilename));

        return $composerData->version;
    }

    public function getModuleVersion()
    {
        return AbstractModuleCoreSetup::getModuleVersion();
    }

    public function getPlatformVersion()
    {
        return AbstractModuleCoreSetup::getPlatformVersion();
    }

    public function getVersionInfo()
    {
        return new VersionInfo(
            $this->getModuleVersion(),
            $this->getCoreVersion(),
            $this->getPlatformVersion()
        );
    }
}