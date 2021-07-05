<?php

namespace Pagarme\Core\Kernel\Interfaces;

interface PlatformCustomerInterface
{
    public function getCode();
    public function getPagarmeId();
    public function getName();
    public function getEmail();
    public function getDocument();
    public function getType();
    public function getAddress();
    public function getPhones();
}