<?php

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Maintenance\Services\IntegrityInfoRetrieverService;

require_once '../../autoload.php';

$coreClass = isset($argv[1]) ? $argv[1] : null;

if (!class_exists($coreClass)) {
    die('Invalid concrete core class!');
}

$concretePlatformCoreSetupClass = $coreClass;

$moduleCoreSetupReflection = new ReflectionClass($concretePlatformCoreSetupClass);
$concreteCoreSetupFilename = $moduleCoreSetupReflection->getFileName();
$concreteDir = explode(DIRECTORY_SEPARATOR, $concreteCoreSetupFilename ?? '');
array_pop($concreteDir);
$concreteDir = implode(DIRECTORY_SEPARATOR, $concreteDir);

AbstractModuleCoreSetup::setModuleConcreteDir($concreteDir);

$integrityInfoService = new IntegrityInfoRetrieverService();
$integrityInfoService->generateCoreIntegrityFile();
$integrityInfoService->generateModuleIntegrityFile();