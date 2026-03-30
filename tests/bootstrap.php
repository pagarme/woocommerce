<?php

define('ABSPATH', '/tmp/wordpress/');

function plugin_basename(string $file): string
{
    return basename(dirname($file)) . '/' . basename($file);
}

require_once dirname(__DIR__) . '/constants.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
