<?php

namespace Pagarme\Core\Maintenance\Interfaces;

interface InstallDataSourceInterface
{
    
    public function getFiles();
    public function getIntegrityFilePath();
}