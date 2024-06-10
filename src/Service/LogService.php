<?php

namespace Woocommerce\Pagarme\Service;

use WC_Logger;
use Pagarme\Core\Kernel\Services\LogService as CoreLogService;

class LogService
{

    public const START_LOG_NAME = "Pagarme_PaymentModule";

    /**
     * @var CoreLogService
     */
    protected $coreLog;

    public function __construct($channelName) {
        // $this->logger = new WC_Logger(); //Log by platform
        $this->coreLog = new CoreLogService($channelName, true);
    }

    public function info($message, $sourceObject = null)
    {
        $this->coreLog->info($message, $sourceObject);
    }

    public function log($error)
    {
        $this->coreLog->info($error);
        // $this->logger->add(self::START_LOG_NAME, $error->getMessage()); //Log by platform
    }
}
