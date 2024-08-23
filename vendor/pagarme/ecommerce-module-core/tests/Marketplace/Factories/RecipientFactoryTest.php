<?php

namespace Pagarme\Core\Test\Marketplace\Factories;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;

class RecipientFactoryTest extends TestCase
{
    /**
     * @dataProvider webhookDataProvider
     */
    public function testCreateFromPostDataShouldCreateWithWebhookData($webhookData)
    {
        $pagarmeId = "rp_xxxxxxxxxxxxxxxx";
        $postData = [
            "id" => $pagarmeId,
            "name" => "Test recipient",
            "email" => "test@recipient.test",
            "document" => "11111111111",
            "description" => "Test description",
            "type" => "individual",
            "payment_mode" => "bank_transfer",
            "status" => "active",
            "kyc_details" =>
            [
                "status" => "approved"
            ],
        ];

        $recipientFactory = new RecipientFactory();

        $result = $recipientFactory->createFromPostData($webhookData);
        $this->assertSame($result->getStatus(), RecipientInterface::ACTIVE);
        $this->assertSame($result->getPagarmeId()->getValue(), $webhookData['id']);
    }

    public function testCreateFromDbDataShouldCreateWithStatus()
    {
        $dbData = [
            "id" => 1,
            "external_id" => 2,
            "name" => "Test recipient",
            "email" => "test@recipient.test",
            "document" => "11111111111",
            "type" => "cpf",
            "pagarme_id" => "rp_xxxxxxxxxxxxxxxx",
            "status" => RecipientInterface::ACTIVE,
        ];

        $recipientFactory = new RecipientFactory();

        $result = $recipientFactory->createFromDbData($dbData);
        $this->assertSame($result->getStatus(), RecipientInterface::ACTIVE);
    }

    public function webhookDataProvider()
    {
        return [
            "webhook with kyc_details" => [
                [
                    "id" => 'rp_xxxxxxxxxxxxxxxx',
                    "name" => "Test recipient",
                    "email" => "test@recipient.test",
                    "document" => "11111111111",
                    "description" => "Test description",
                    "type" => "individual",
                    "payment_mode" => "bank_transfer",
                    "status" => "active",
                    "kyc_details" =>
                    [
                        "status" => "approved"
                    ],
                ]
            ],
            "webhook without kyc_details" => [
                [
                    "id" => 'rp_xxxxxxxxxxxxxxxx',
                    "name" => "Test recipient",
                    "email" => "test@recipient.test",
                    "document" => "11111111111",
                    "description" => "Test description",
                    "type" => "individual",
                    "payment_mode" => "bank_transfer",
                    "status" => "active",
                ]
            ],
        ];
    }
}
