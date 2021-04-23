<?php

namespace Pagarme\Core\Kernel\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Exceptions\AbstractPagarmeCoreException;
use Pagarme\Core\Kernel\Factories\LogObjectFactory;
use Pagarme\Core\Kernel\Log\JsonPrettyFormatter;

class LogService
{
    protected $path;
    protected $addHost;
    protected $channelName;
    protected $monolog;
    protected $fileName;
    protected $stackTraceDepth;

    public function __construct($channelName, $addHost = false)
    {
        $this->stackTraceDepth = 2;
        $this->channelName = $channelName;
        $this->path = AbstractModuleCoreSetup::getLogPath();

        if (is_array($this->path)) {
            $this->path = array_shift($this->path);
        }

        $this->addHost = $addHost;

        $this->setFileName();

        $this->monolog = new Logger(
            $this->channelName
        );
        $handler = new StreamHandler($this->fileName, Logger::DEBUG);
        $handler->setFormatter(new JsonPrettyFormatter());
        $this->monolog->pushHandler($handler);
    }

    public function info($message, $sourceObject = null)
    {
        $logObject = $this->prepareObject($sourceObject);

        $this->monolog->info($message, $logObject);
    }

    public function exception(\Exception $exception)
    {
        $logObject = $this->prepareObject($exception);

        $code = ' | Exception code: ' . $exception->getCode();
        $this->monolog->error($exception->getMessage() . $code, $logObject);
    }

    protected function prepareObject($sourceObject)
    {
        $logObjectFactory = new LogObjectFactory;

        $versionService = new VersionService();
        $debugStep = $this->stackTraceDepth;

        $baseObject = $logObjectFactory->createFromLogger(
            debug_backtrace()[$debugStep],
            $sourceObject,
            $versionService->getVersionInfo()
        );
        $baseObject = json_encode($baseObject);
        return json_decode($baseObject, true);
    }

    protected function setFileName()
    {
        $base = 'Pagarme_PaymentModule_' . date('Y-m-d');
        $fileName = $this->path . DIRECTORY_SEPARATOR . $base;

        if ($this->addHost) {
            $fileName .= '_' . gethostname();
        }

        $fileName .= '.log';

        $this->fileName = $fileName;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getPath()
    {
        return $this->path;
    }
}