<?php

namespace Pagarme\Core\Test\Marketplace\Aggregates;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;

class RecipientTest extends TestCase
{
    /**
     * @dataProvider statusDataProvider
     */
    public function testParseStatus($status, $kycStatus, $expectedStatus)
    {
        $result = Recipient::parseStatus($status, $kycStatus);
        $this->assertEquals($expectedStatus, $result);
    }

    public function statusDataProvider()
    {
        return [
            "Registered status" => ["registration", "pending", RecipientInterface::REGISTERED],
            "Validation Request status" => ["affiliation", "partially_denied", RecipientInterface::VALIDATION_REQUESTED],
            "Waiting for analysis status" => ["affiliation", "pending", RecipientInterface::WAITING_FOR_ANALYSIS],
            "Active status" => ["active", "approved", RecipientInterface::ACTIVE],
            "Disapproved status" => ["registration", "denied", RecipientInterface::DISAPPROVED],
            "Suspended status" => ["suspended", "", RecipientInterface::SUSPENDED],
            "Blocked status" => ["blocked", "", RecipientInterface::BLOCKED],
            "Inactive status" => ["inactive", "", RecipientInterface::INACTIVE],
        ];
    }
}
