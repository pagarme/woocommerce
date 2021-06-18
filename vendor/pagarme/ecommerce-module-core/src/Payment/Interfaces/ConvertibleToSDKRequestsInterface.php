<?php

namespace Pagarme\Core\Payment\Interfaces;

interface ConvertibleToSDKRequestsInterface
{
    public function convertToSDKRequest();
}