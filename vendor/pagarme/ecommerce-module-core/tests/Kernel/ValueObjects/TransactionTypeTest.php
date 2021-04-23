<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use PHPUnit\Framework\TestCase;

class TransactionTypeTest extends TestCase
{
    protected $validStatuses = [
        'CREDIT_CARD' => [
            'method' => 'creditCard',
            'value' => "credit_card"
        ],
        'BOLETO' => [
            'method' => 'boleto',
            'value' => "boleto"
        ],
        'VOUCHER' => [
            'method' => 'voucher',
            'value' => "voucher"
        ],
        'DEBIT_CARD' => [
            'method' => 'debitCard',
            'value' => "debit_card"
        ],
        'PIX' => [
            'method' => 'pix',
            'value' => "pix"
        ]
    ];

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionType
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aTransactionTypeShouldBeComparable()
    {
        $TransactionTypeCreditCard1 = TransactionType::creditCard();
        $TransactionTypeCreditCard2 = TransactionType::creditCard();

        $TransactionTypeBoleto = TransactionType::boleto();

        $this->assertTrue($TransactionTypeCreditCard1->equals($TransactionTypeCreditCard2));
        $this->assertFalse($TransactionTypeCreditCard1->equals($TransactionTypeBoleto));
        $this->assertFalse($TransactionTypeCreditCard2->equals($TransactionTypeBoleto));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionType
     */
    public function aTransactionTypeShouldBeJsonSerializable()
    {
        $TransactionTypeCreditCard1 = TransactionType::creditCard();

        $json = json_encode($TransactionTypeCreditCard1);
        $expected = json_encode(TransactionType::CREDIT_CARD);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionType
     */
    public function allTransactionTypeConstantsDefinedInTheClassShouldBeInstantiable()
    {
        $TransactionTypeCreditCard = TransactionType::creditCard();

        $reflectionClass = new \ReflectionClass($TransactionTypeCreditCard);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $const => $stateData) {
            $method = $this->validStatuses[$const]['method'];
            $expectedValue = $this->validStatuses[$const]['value'];

            $TransactionType = TransactionType::$method();
            $this->assertEquals($expectedValue, $TransactionType->getType());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionType
     */
    public function aInvalidTransactionTypeShouldNotBeInstantiable()
    {
        $TransactionTypeClass = TransactionType::class;
        $invalidTransactionType = TransactionType::CREDIT_CARD . TransactionType::CREDIT_CARD;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method {$TransactionTypeClass}::{$invalidTransactionType}()");

        $TransactionTypeCreditCard = TransactionType::$invalidTransactionType();
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionType
     */
    public function aTransactionTypeShouldAcceptAllPossibleTransactionTypees()
    {
        foreach ($this->validStatuses as $statusData) {
            $method = $statusData['method'];
            $expectedValue = $statusData['value'];

            $TransactionType = TransactionType::$method();
            $this->assertEquals($expectedValue, $TransactionType->getType());
        }
    }
}
