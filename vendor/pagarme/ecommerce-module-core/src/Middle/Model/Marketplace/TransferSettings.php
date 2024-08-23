<?php

namespace Pagarme\Core\Middle\Model\Marketplace;

use InvalidArgumentException;
use PagarmeCoreApiLib\Models\CreateTransferSettingsRequest;

class TransferSettings
{
    const TRANSFER_INTERVAL_DAILY = 'Daily';
    const TRANSFER_INTERVAL_WEEKLY = 'Weekly';
    const TRANSFER_INTERVAL_MONTHLY = 'Monthly';
    const VALID_TRANSFER_INTERVAL = [
        self::TRANSFER_INTERVAL_DAILY,
        self::TRANSFER_INTERVAL_WEEKLY, 
        self::TRANSFER_INTERVAL_MONTHLY
    ];
    private $transferEnabled;
    private $transferInterval;
    private $transferDay;

    public function __construct($transferEnabled, $transferInterval, $transferDay)
    {
        $this->setTransferEnabled($transferEnabled);
        $this->setTransferInterval($transferInterval);
        $this->setTransferDay($transferDay);
    }

    private function setTransferEnabled($transferEnabled): void
    {
        $this->transferEnabled = $transferEnabled;
    }

    private function setTransferInterval($transferInterval): void
    {
        if (!in_array($transferInterval, self::VALID_TRANSFER_INTERVAL)) {
            throw new InvalidArgumentException("Invalid argument to transferInterval");
        }
        $this->transferInterval = $transferInterval;
    }

    private function setTransferDay($transferDay): void
    {
        if ($this->getTransferInterval() === self::TRANSFER_INTERVAL_DAILY) {
            $this->transferDay = 0;
            return;
        }
        $this->validateTransferDay($transferDay);
        $this->transferDay = $transferDay;
    }

    private function validateTransferDay($transferDay)
    {
        if ($this->getTransferInterval() === self::TRANSFER_INTERVAL_WEEKLY && !$this->isValidForWeeklyInterval($transferDay)) {
            throw new InvalidArgumentException("Invalid Transfer Day to Weekly Transfer Interval!");
        }
        if ($this->getTransferInterval() === self::TRANSFER_INTERVAL_MONTHLY && !$this->isValidForMonthlyInterval($transferDay)) {
            throw new InvalidArgumentException("Invalid Transfer Day to Monthly Transfer Interval!");
        }
        return true;
    }

    private function isValidForWeeklyInterval($transferDay)
    {
        if ((int) $transferDay >= 1 && (int) $transferDay <= 5) {
            return true;
        }
        return false;
    }
    private function isValidForMonthlyInterval($transferDay)
    {
        if ($transferDay >= 1 && $transferDay <= 31) {
            return true;
        }
        return false;
    }

    public function getTransferEnabled()
    {
        return $this->transferEnabled;
    }

    public function getTransferInterval()
    {
        return $this->transferInterval;
    }

    public function getTransferDay()
    {
        return $this->transferDay;
    }


    public function convertToArray()
    {
        return array (
            'transfer_enabled' => $this->getTransferEnabled(),
            'transfer_interval' => $this->getTransferInterval(),
            'transfer_day' => $this->getTransferDay()
        );
    }

    public function convertToSdkRequest()
    {
        return new CreateTransferSettingsRequest(
            $this->getTransferEnabled(),
            $this->getTransferInterval(),
            $this->getTransferDay()
        );
    }
}
