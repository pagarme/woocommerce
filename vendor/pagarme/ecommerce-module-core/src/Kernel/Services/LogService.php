<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Core\Kernel\Services;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Factories\LogObjectFactory;
use Pagarme\Core\Kernel\Log\BlurData;
use Pagarme\Core\Kernel\Log\JsonPrettyFormatter;

/**
 * Class LogService
 */
class LogService
{
    /** @var mixed|null */
    protected $path;

    /** @var bool */
    protected $addHost;

    /** @var string */
    protected $channelName;

    /** @var Logger */
    protected $monolog;

    /** @var string */
    protected $fileName;

    /** @var int */
    protected $stackTraceDepth;

    /** @var BlurData */
    protected $blurData;

    /**
     * @param $channelName
     * @param bool $addHost
     * @throws \Exception
     */
    public function __construct(
        $channelName,
        $addHost = false
    ) {
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
        $this->blurData = new BlurData();
    }

    /**
     * @param $message
     * @param $sourceObject
     * @return void
     */
    public function info($message, $sourceObject = null)
    {
        try {

            $logObject = $this->prepareObject($sourceObject);
            $logObject = $this->blurSensitiveData($logObject);
            $this->monolog->info($message, $logObject);

        } catch (\Throwable $th) {
            //throw $th;
        }

    }

    /**
     * @param \Exception $exception
     * @return void
     */
    public function exception(\Exception $exception)
    {
        try {

            $logObject = $this->prepareObject($exception);
            $code = ' | Exception code: ' . $exception->getCode();
            $this->monolog->error($exception->getMessage() . $code, $logObject);

        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * @param $sourceObject
     * @return mixed
     */
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
        if (!$baseObject) {
            $baseObject = [];
        }
        $baseObject = json_encode($baseObject);
        return json_decode($baseObject, true);
    }

    /**
     * @return void
     */
    protected function setFileName()
    {
        $base = 'Pagarme_PaymentModule_' . date('Y-m-d');
        $fileName = $this->path . DIRECTORY_SEPARATOR . $base;
        $fileName .= '.log';
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return mixed|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $logObject
     * @return mixed
     */
    private function blurSensitiveData($logObject)
    {
        try {
            if (is_object($logObject)) {
                if ($data = $this->getData($logObject->getData(), 'data')) {
                    foreach ($data as $method => $value) {
                        $blurMethod = $this->blurData->getBlurMethod($method);
                        if (method_exists($this->blurData, $blurMethod)) {
                            $data[$method] = $this->blurData->{$blurMethod}($value);
                        }
                    }
                    $logObjectData = $logObject->getData();
                    $this->setData($logObjectData, $data, 'data');
                    $logObject->setData($logObjectData);
                }
            }
            if (is_array($logObject)) {
                if ($data = $this->getData($logObject, 'data')) {
                    foreach ($data as $method => $value) {
                        $blurMethod = $this->blurData->getBlurMethod($method);
                        if (method_exists($this->blurData, $blurMethod)) {
                            $data[$method] = $this->blurData->{$blurMethod}($value);
                        }
                    }
                    $this->setData($logObject, $data, 'data');
                }
            }
            return $logObject;
        } catch (\Exception $e) {
            $this->exception($e);
        }
    }

    /**
     * @param $haystack
     * @param $key
     * @return mixed|null
     */
    private function getData($haystack, $key)
    {
        if ($haystack instanceof \stdClass) {
            if (property_exists($haystack, $key)) {
                return $haystack->{$key};
            }
        }
        if (is_array($haystack) && isset($haystack[$key]) && $haystack[$key]) {
            return $haystack[$key];
        }
        return null;
    }

    /**
     * @param $haystack
     * @param $value
     * @param $key
     * @return void
     */
    private function setData(&$haystack, $value, $key = null)
    {
        if ($haystack instanceof \stdClass && $key) {
            $haystack->{$key} = $value;
            return;
        }
        if (is_array($haystack) && $key) {
            $haystack[$key] = $value;
            return;
        }
        $haystack = $value;
    }
}
