<?php

namespace Pagarme\Core\Maintenance\Services\InstallDataSource;

final class CoreInstallDataSource extends AbstractInstallDataSource
{
    private $integrityFilePath;

    public function __construct()
    {
        $dir = explode(DIRECTORY_SEPARATOR, __DIR__);
        array_pop($dir);
        array_pop($dir);
        $dir = implode(DIRECTORY_SEPARATOR, $dir);

        $this->integrityFilePath = $dir . DIRECTORY_SEPARATOR .
            'Assets' . DIRECTORY_SEPARATOR . 'integrityData';
    }

    public function getFiles()
    {
        return $this->scanDirs(
            $this->getInstallDirs()
        );
    }

    public function getIntegrityFilePath()
    {
        return $this->integrityFilePath;
    }

    protected function getInstallDirs()
    {
        $currentDir = __DIR__;

        do {
            $currentDir = explode(DIRECTORY_SEPARATOR, $currentDir);
            array_pop($currentDir);
            $currentDir = implode(DIRECTORY_SEPARATOR, $currentDir);

            if (strpos($currentDir, 'ecommerce-module-core') === false) {
                return null;
            }

            $composerJsonFilename =  $currentDir . DIRECTORY_SEPARATOR . 'composer.json';

        } while (!file_exists($composerJsonFilename));

        return [$currentDir . DIRECTORY_SEPARATOR . 'src'];
    }

    protected function getModuleRoot()
    {
        return $this->getInstallDirs()[0];
    }
}