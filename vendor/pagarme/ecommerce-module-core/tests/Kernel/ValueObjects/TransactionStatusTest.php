<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\TransactionStatus;
use PHPUnit\Framework\TestCase;

class TransactionStatusTest extends TestCase
{
    protected $validStatuses = [
        'CAPTURED' => [
            'method' => 'captured',
            'value' => "captured"
        ],
        'PARTIAL_CAPTURE' => [
            'method' => 'partialCapture',
            'value' => "partial_capture"
        ],
        'AUTHORIZED_PENDING_CAPTURE' => [
            'method' => 'authorizedPendingCapture',
            'value' => 'authorized_pending_capture'
        ],
        'VOIDED' => [
            'method' => 'voided',
            'value' => 'voided'
        ],
        'REFUNDED' => [
            'method' => 'refunded',
            'value' => 'refunded'
        ],
        'PARTIAL_VOID' => [
            'method' => 'partialVoid',
            'value' => 'partial_void'
        ],
        'WITH_ERROR' => [
            'method' => 'withError',
            'value' => 'withError'
        ],
        'NOT_AUTHORIZED' => [
            'method' => 'notAuthorized',
            'value' => 'notAuthorized'
        ],
        'FAILED' => [
            'method' => 'failed',
            'value' => 'failed'
        ],
        'CHARGEDBACK' => [
            'method' => 'chargedback',
            'value' => 'chargedback'
        ],
        'GENERATED' => [
            'method' => 'generated',
            'value' => 'generated'
        ],
        'UNDERPAID' => [
            'method' => 'underpaid',
            'value' => 'underpaid'
        ],
        'PAID' => [
            'method' => 'paid',
            'value' => 'paid'
        ],
        'OVERPAID' => [
            'method' => 'overpaid',
            'value' => 'overpaid'
        ],
        'PARTIAL_REFUNDED' => [
            'method' => 'partialRefunded',
            'value' => 'partial_refunded'
        ],
        'WAITING_PAYMENT' => [
            'method' => 'waitingPayment',
            'value' => 'waiting_payment'
        ],
        'PENDING_REFUND' => [
            'method' => 'pendingRefund',
            'value' => 'pending_refund'
        ],
        'EXPIRED' => [
            'method' => 'expired',
            'value' => 'expired'
        ],
        'PENDING_REVIEW' => [
            'method' => 'pendingReview',
            'value' => 'pending_review'
        ],
        'ANALYZING' => [
            'method' => 'analyzing',
            'value' => 'analyzing'
        ],
        'WAITING_CAPTURE' => [
            'method' => 'waitingCapture',
            'value' => 'waiting_capture'
        ]
    ];

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionStatus
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aTransactionStatusShouldBeComparable()
    {
        $TransactionStatusPaid1 = TransactionStatus::paid();
        $TransactionStatusPaid2 = TransactionStatus::paid();

        $TransactionStatusOverpaid = TransactionStatus::overpaid();

        $this->assertTrue($TransactionStatusPaid1->equals($TransactionStatusPaid2));
        $this->assertFalse($TransactionStatusPaid1->equals($TransactionStatusOverpaid));
        $this->assertFalse($TransactionStatusPaid2->equals($TransactionStatusOverpaid));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionStatus
     */
    public function aTransactionStatusShouldBeJsonSerializable()
    {
        $TransactionStatusPaid1 = TransactionStatus::paid();

        $json = json_encode($TransactionStatusPaid1);
        $expected = json_encode(TransactionStatus::PAID);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionStatus
     */
    public function allTransactionStatusConstantsDefinedInTheClassShouldBeInstantiable()
    {
        $TransactionStatusPaid = TransactionStatus::paid();

        $reflectionClass = new \ReflectionClass($TransactionStatusPaid);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $const => $stateData) {
            $method = $this->validStatuses[$const]['method'];
            $expectedValue = $this->validStatuses[$const]['value'];

            $TransactionStatus = TransactionStatus::$method();
            $this->assertEquals($expectedValue, $TransactionStatus->getStatus());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionStatus
     */
    public function aInvalidTransactionStatusShouldNotBeInstantiable()
    {
        $TransactionStatusClass = TransactionStatus::class;
        $invalidTransactionStatus = TransactionStatus::PAID . TransactionStatus::PAID;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method {$TransactionStatusClass}::{$invalidTransactionStatus}()");

        $TransactionStatusPaid = TransactionStatus::$invalidTransactionStatus();
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\TransactionStatus
     */
    public function aTransactionStatusShouldAcceptAllPossibleTransactionStatuses()
    {
        foreach ($this->validStatuses as $statusData) {
            $method = $statusData['method'];
            $expectedValue = $statusData['value'];

            $TransactionStatus = TransactionStatus::$method();
            $this->assertEquals($expectedValue, $TransactionStatus->getStatus());
        }
    }
}
