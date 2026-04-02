<?php

namespace Pagarme\Core\Hub\Commands;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;

class InstallCommand extends AbstractCommand
{
    public function execute()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        $moduleConfig->setAccountId($this->getAccountId());

        $moduleConfig->setMerchantId($this->getMerchantId());

        $moduleConfig->setPaymentProfileId($this->getPaymentProfileId());

        $moduleConfig->setPoiType($this->getPoiType());

        $moduleConfig->setHubInstallId($this->getInstallId());

        $moduleConfig->setHubEnvironment($this->getType());

        $moduleConfig->setPublicKey($this->getAccountPublicKey());

        $moduleConfig->setSecretKey($this->getAccessToken());

        $configRepo = new ConfigurationRepository();

        $configRepo->save($moduleConfig);
    }
}
