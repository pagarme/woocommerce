<?php

namespace Pagarme\Core\Maintenance\Services\InstallDataSource;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Maintenance\Interfaces\ModuleInstallTypeInterface;

final class ModmanInstallDataSource
    extends AbstractInstallDataSource
    implements ModuleInstallTypeInterface
{
    private $integrityFilePath;
    private $modmanFilePath;

    public function __construct()
    {
        $concreteDir = AbstractModuleCoreSetup::getModuleConcreteDir();

        $this->integrityFilePath = $concreteDir . DIRECTORY_SEPARATOR . 'integrityData';
        $this->modmanFilePath = $concreteDir . DIRECTORY_SEPARATOR . 'modman';
    }

    public function getFiles()
    {
        $files =  $this->scanDirs(
            $this->getInstallDirs(),
            true
        );
        return array_filter(
            $files, function ($file) {
                return
                strpos($file, 'integrityData') === false
                ;
            }
        );
    }

    public function getIntegrityFilePath()
    {
        if (file_exists($this->integrityFilePath) 
            && file_exists($this->modmanFilePath)
        ) {
            return $this->integrityFilePath;
        }

        return null;
    }

    protected function getInstallDirs()
    {
        $rawData = file_get_contents($this->modmanFilePath);

        $lines = [];
        preg_match_all('/^(?!#).+/m', $rawData ?? '', $lines);
        $lines = array_pop($lines);
        array_walk(
            $lines, function (&$line) {
                $data = explode(' ', $line ?? '');
                $line = end($data);
            }
        );

        $platformRootDir = '';
        foreach ($lines as $line) {
            $platformRootDir = str_replace($line, '', $this->modmanFilePath ?? '');
            if (strlen($platformRootDir) >= strlen($this->modmanFilePath)) {
                $platformRootDir = '';
            }
        }

        array_walk(
            $lines, function (&$line) use ($platformRootDir) {
                $line = $platformRootDir . $line;
            }
        );

        $lines = array_filter(
            $lines, function ($line) {
                return
                strpos($line, 'modman') === false
                ;
            }
        );

        return $lines;
    }

    protected function getModuleRoot()
    {
        $rawData = file_get_contents($this->modmanFilePath);

        $lines = [];
        preg_match_all('/^(?!#).+/m', $rawData ?? '', $lines);
        $lines = array_pop($lines);
        array_walk(
            $lines, function (&$line) {
            $data = explode(' ', $line ?? '');
            $line = end($data);
        }
        );

        $platformRootDir = '';
        foreach ($lines as $line) {
            $platformRootDir = str_replace($line, '', $this->modmanFilePath ?? '');
            if (strlen($platformRootDir) >= strlen($this->modmanFilePath)) {
                $platformRootDir = '';
            }
        }

        return $platformRootDir;
    }
}