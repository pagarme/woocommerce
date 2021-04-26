<?php

namespace Pagarme\Core\Test\Recurrence\Factories;

use Pagarme\Core\Kernel\ValueObjects\Id\InvoiceId;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\Factories\ChargeFactory;
use PHPUnit\Framework\TestCase;

class ChargeFactoryTest extends TestCase
{

    public function testShouldCreateAChargeFromPostData()
    {
        $factory = new ChargeFactory;

        $postData = [
            'id' => 'ch_bVrP01wUjDfy951k',
            'code' => '000000050-15',
            'amount' => 30000,
            'paid_amount' => 30000,
            'payment_method' => 'boleto',
            "status"=> "paid",
            'cycle_start' => '2020-01-03',
            'cycle_end' => '2020-01-03',
            'boleto_link' => "https://api.mundipagg.com/core/v1/transactions/tran_5Gr1OamfZIrOBnEA/pdf",
            "invoice"=> [
                "id"=> "in_4QagkrZnupHek82A",
                "code"=> "D4LQFE7IGZ",
                "url"=> "/invoices/in_4QagkrZnupHek82A",
                "amount"=> 29000,
                "status"=> "paid",
                "payment_method"=> "boleto",
                "installments"=> 3,
                "due_at"=> "2020-01-08T23:59:59",
                "created_at"=> "2020-01-03T03:34:11",
                "subscriptionId"=> "sub_02XDQ3ptYzcg1WBY"
            ],
            "customer"=> [
                "id"=> "cus_JOl7padumrtaVnGm",
                "name"=> "Tony Stark",
                "email"=> "tonystark@avengers.com",
                "delinquent"=> false,
                "created_at"=> "2019-12-20T20:03:23",
                "updated_at"=> "2019-12-20T20:03:23",
            ],
            "last_transaction" =>  [
                "credit_at"=> "2020-01-03T03:39:08",
                "id"=> "tran_1RMV9ZTYgUdmL0Ja",
                "transaction_type"=> "boleto",
                "gateway_id"=> "e2d193e8-3978-4c59-8817-741b351ec1c5",
                "amount"=> 29000,
                "status"=> "paid",
                "success"=> true,
                "paid_amount"=> 29000,
                "paid_at"=> "2020-01-03T03:39:08",
                "url"=> "https://sandbox.mundipaggone.com/Boleto/ViewBoleto.aspx?e2d193e8-3978-4c59-8817-741b351ec1c5",
                "pdf"=> "https://api.mundipagg.com/core/v1/transactions/tran_ZEy4redHDTlqkgQ0/pdf",
                "line"=> "34191.75462 27010.071234 41234.510000 1 81280000029000",
                "barcode"=> "https://api.mundipagg.com/core/v1/transactions/tran_ZEy4redHDTlqkgQ0/barcode",
                "qr_code"=> "https://api.mundipagg.com/core/v1/transactions/tran_ZEy4redHDTlqkgQ0/qrcode",
                "nosso_numero"=> "46270100",
                "bank"=> "001",
                "document_number"=> "033413026",
                "instructions"=> "Pagar até o vencimento",
                "created_at"=> "2020-01-03T03:39:17",
            ]
        ];

        $charge = $factory->createFromPostData($postData);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertCount(1, $charge->getTransactions());
    }

    public function testShouldCreateAChargeFromDbData()
    {
        $factory = new ChargeFactory;

        $dbData = [
            'id' => 'id',
            'pagarme_id' => 'ch_bVrP01wUjDfy951k',
            'code' => '000000050-15',
            'amount' => 30000,
            'paid_amount' => '30000',
            'canceled_amount' => '30000',
            'refunded_amount' => '30000',
            'payment_method' => 'boleto',
            "status"=> "paid",
            'boleto_link' => "https://api.mundipagg.com/core/v1/transactions/tran_5Gr1OamfZIrOBnEA/pdf",
            'cycle_start' => '2020-01-03',
            'cycle_end' => '2020-01-03',
            'metadata' => 'metadata',
            'subscription_id' => 'sub_1234567890123457',
            'invoice_id' => 'in_1234567890123457'
        ];

        $charge = $factory->createFromDbData($dbData);
        $this->assertInstanceOf(Charge::class, $charge);
    }
}