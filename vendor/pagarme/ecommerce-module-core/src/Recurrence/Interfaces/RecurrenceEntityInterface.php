<?php

namespace Pagarme\Core\Recurrence\Interfaces;

interface RecurrenceEntityInterface
{
    public function getRecurrenceType();
    public function getId();
    public function convertToSdkRequest();
    public function getCreditCard();
    public function getBoleto();
}