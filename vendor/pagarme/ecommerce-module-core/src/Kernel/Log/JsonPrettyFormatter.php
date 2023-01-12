<?php

namespace Pagarme\Core\Kernel\Log;

use Monolog\Formatter\JsonFormatter;
use Pagarme\Core\Kernel\Factories\LogObjectFactory;

class JsonPrettyFormatter extends JsonFormatter
{
    public function format(array $record): string
    {
        $logObjectFactory = new LogObjectFactory();
        $logObject = $logObjectFactory->createFromArray($record['context']);

        $msg =
            "[{$record['datetime']->format('Y-m-d h:i:s')}] " .
            "{$record['channel']}.{$record['level_name']}: " .
            "{$record['message']}" . PHP_EOL;
        $msg .= "Version: " .
            "m: {$logObject->getVersions()->getModuleVersion()} " .
            "c: {$logObject->getVersions()->getCoreVersion()} " .
            "p: {$logObject->getVersions()->getPlatformVersion()} " . PHP_EOL;
        $msg .= 'From: ' . $logObject->getMethod() . PHP_EOL;
        $msg .= json_encode($logObject->getData(), JSON_PRETTY_PRINT) . PHP_EOL;

        return $msg;
    }
}