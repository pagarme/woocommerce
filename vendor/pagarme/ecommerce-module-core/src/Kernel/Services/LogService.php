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
        $logObject = $this->blurSensitiveData($logObject);
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

    private function blurSensitiveData($data)
    {
        if (isset($data['data'])) {
            if (isset($data['data']['customer'])) {
                $data = $this->blurCustomer($data);
            }
            if (isset($data['data']['shipping'])) {
                $data = $this->blurShipping($data);
            }
            if (isset($data['data']['payments'][0]['customer'])) {
                $data = $this->blurPaymentCustomer($data);
            }
            if (isset($data['data']['charges'][0]['customer'])) {
                $data = $this->blurChargeCustomer($data);
            }
            if (isset($data['data']['charges'][0]['last_transaction']['card']['billing_address'])) {
                $data['data']['charges'][0]['last_transaction']['card']['billing_address'] = null;
            }
            if (isset($data['data']['payments'][0]['credit_card']['card']['billing_address'])) {
                $data['data']['payments'][0]['credit_card']['card']['billing_address'] = null;
            }
        }
        return $data;
    }

    private function blurStringSensitiveData($string, $delimiter){
        $displayed = substr($string, 0, $delimiter);
        $blur = str_repeat("*", strlen($string));
        $blur = substr($blur, $delimiter);
        $result = "$displayed $blur";
        return $result;
    }

    private function blurEmailSensitiveData($string)
    {
        $displayed = substr($string, 0, 3);
        $final = substr($string, strpos($string, "@"));
        $result = "$displayed***$final";
        return $result;
    }

    private function blurCustomer($data){
        $data['data']['customer']['name'] = $this->blurStringSensitiveData($data['data']['customer']['name'], 5);
        $data['data']['customer']['email'] = $this->blurEmailSensitiveData($data['data']['customer']['email']);
        $data['data']['customer']['document'] = preg_replace('/\B[^@.]/', '*', $data['data']['customer']['document']);
        $data['data']['customer']['phones'] = '***********';
        $data['data']['customer']['address']['street'] = $this->blurStringSensitiveData($data['data']['customer']['address']['street'], 8);
        $data['data']['customer']['address']['line_1'] = $this->blurStringSensitiveData($data['data']['customer']['address']['line_1'], 8);
        $data['data']['customer']['address']['line_2'] = '***********';
        $data['data']['customer']['address']['number'] = '***********';
        $data['data']['customer']['address']['complement'] = '***********';
        $data['data']['customer']['address']['zip_code'] = $this->blurStringSensitiveData($data['data']['customer']['address']['zip_code'], 5);
        $data['data']['customer']['address']['neighborhood'] = '***********';
        return $data;
    }

    private function blurShipping($data){
        $data['data']['shipping']['recipient_name'] = $this->blurStringSensitiveData($data['data']['shipping']['recipient_name'], 5);
        $data['data']['shipping']['recipient_phone'] = '***********';
        $data['data']['shipping']['address']['street'] = $this->blurStringSensitiveData($data['data']['shipping']['address']['street'], 8);
        $data['data']['shipping']['address']['line_1'] = $this->blurStringSensitiveData($data['data']['shipping']['address']['line_1'], 8);
        $data['data']['shipping']['address']['line_2'] = '***********';
        $data['data']['shipping']['address']['number'] = '***********';
        $data['data']['shipping']['address']['complement'] = '***********';
        $data['data']['shipping']['address']['zip_code'] = $this->blurStringSensitiveData($data['data']['shipping']['address']['zip_code'], 5);
        $data['data']['shipping']['address']['neighborhood'] = '***********';
        return $data;
    }

    private function blurPaymentCustomer($data){
        $data['data']['payments'][0]['customer']['name'] = $this->blurStringSensitiveData($data['data']['payments'][0]['customer']['name'], 5);
        $data['data']['payments'][0]['customer']['email'] = $this->blurEmailSensitiveData($data['data']['payments'][0]['customer']['email']);
        $data['data']['payments'][0]['customer']['document'] = preg_replace('/\B[^@.]/', '*', $data['data']['payments'][0]['customer']['document']);
        $data['data']['payments'][0]['customer']['phones'] = '***********';
        $data['data']['payments'][0]['customer']['address']['street'] = $this->blurStringSensitiveData($data['data']['payments'][0]['customer']['address']['street'], 8);
        $data['data']['payments'][0]['customer']['address']['line_1'] = $this->blurStringSensitiveData($data['data']['payments'][0]['customer']['address']['line_1'], 8);
        $data['data']['payments'][0]['customer']['address']['line_2'] = '***********';
        $data['data']['payments'][0]['customer']['address']['number'] = '***********';
        $data['data']['payments'][0]['customer']['address']['complement'] = '***********';
        $data['data']['payments'][0]['customer']['address']['zip_code'] = $this->blurStringSensitiveData($data['data']['payments'][0]['customer']['address']['zip_code'], 5);
        $data['data']['payments'][0]['customer']['address']['neighborhood'] = '***********';
        return $data;
    }

    private function blurChargeCustomer($data){
        $data['data']['charges'][0]['customer']['name'] = $this->blurStringSensitiveData($data['data']['charges'][0]['customer']['name'], 5);
        $data['data']['charges'][0]['customer']['email'] = $this->blurEmailSensitiveData($data['data']['charges'][0]['customer']['email']);
        $data['data']['charges'][0]['customer']['document'] = preg_replace('/\B[^@.]/', '*', $data['data']['charges'][0]['customer']['document']);
        $data['data']['charges'][0]['customer']['phones'] = '***********';
        $data['data']['charges'][0]['customer']['address']['street'] = $this->blurStringSensitiveData($data['data']['charges'][0]['customer']['address']['street'], 8);
        $data['data']['charges'][0]['customer']['address']['line_1'] = $this->blurStringSensitiveData($data['data']['charges'][0]['customer']['address']['line_1'], 8);
        $data['data']['charges'][0]['customer']['address']['line_2'] = '***********';
        $data['data']['charges'][0]['customer']['address']['number'] = '***********';
        $data['data']['charges'][0]['customer']['address']['complement'] = '***********';
        $data['data']['charges'][0]['customer']['address']['zip_code'] = $this->blurStringSensitiveData($data['data']['charges'][0]['customer']['address']['zip_code'], 5);
        $data['data']['charges'][0]['customer']['address']['neighborhood'] = '***********';
        return $data;
    }
}
