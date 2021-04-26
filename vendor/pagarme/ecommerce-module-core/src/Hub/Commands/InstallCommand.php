<?php

namespace Pagarme\Core\Hub\Commands;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;

class InstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        if ($moduleConfig->isHubEnabled()) {
            throw new Exception("Hub already installed!");
        }

        $moduleConfig->setHubInstallId($this->getInstallId());

        $moduleConfig->setPublicKey(
            $this->getAccountPublicKey()
        );

        $moduleConfig->setSecretKey(
            $this->getAccessToken()
        );

        $configRepo = new ConfigurationRepository();

        $configRepo->save($moduleConfig);
    }
}