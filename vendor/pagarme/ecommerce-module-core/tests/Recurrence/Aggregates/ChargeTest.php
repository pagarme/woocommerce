<?php

namespace Pagarme\Core\Test\Recurrence\Aggregates;

use Carbon\Carbon;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Kernel\ValueObjects\Id\InvoiceId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Kernel\ValueObjects\Id\TransactionId;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\Aggregates\Invoice;
use PHPUnit\Framework\TestCase;

class ChargeTest extends TestCase
{

    /**
     * @var Charge
     */
    private $charge;

    protected function setUp(): void
    {
        $this->charge = new Charge();
    }

    public function testChargeObject()
    {
        $this->charge->setId(1);
        $this->charge->setPagarmeId($this->createMock(ChargeId::class));
        $this->charge->setOrderId($this->createMock(OrderId::class));
        $this->charge->setAmount(100);
        $this->charge->setPaidAmount(100);
        $this->charge->setCanceledAmount(0);
        $this->charge->setRefundedAmount(0);
        $this->charge->setCode('1234');
        $this->charge->setStatus(ChargeStatus::paid());
        $this->charge->setMetadata("metadata");
        $this->charge->setInvoiceId(new InvoiceId('in_1234567890123457'));
        $this->charge->setSubscriptionId(new SubscriptionId('sub_1234567890123457'));

        $customer = new Customer();
        $customer->setPagarmeId($this->createMock(CustomerId::class));

        $invoice = new Invoice();
        $invoice->setPagarmeId($this->createMock(InvoiceId::class));

        $this->charge->setCustomer($customer);
        $this->charge->setInvoice($invoice);

        $transaction = $this->getTransaction("_____1");

        $this->charge->addTransaction($transaction);
        $this->charge->setBoletoUrl("urlBoleto");
        $this->charge->setBoletoLink("urlBoleto");
        $this->charge->setPaymentMethod(PaymentMethod::boleto());
        $this->charge->setCycleEnd(new \DateTime());
        $this->charge->setCycleStart(new \DateTime());

        $this->assertEquals(1, $this->charge->getId());
        $this->assertInstanceOf(ChargeId::class, $this->charge->getPagarmeId());
        $this->assertInstanceOf(OrderId::class, $this->charge->getOrderId());
        $this->assertEquals(100, $this->charge->getAmount());
        $this->assertEquals(100, $this->charge->getPaidAmount());
        $this->assertEquals(0, $this->charge->getCanceledAmount());
        $this->assertEquals(0, $this->charge->getRefundedAmount());
        $this->assertEquals("1234", $this->charge->getCode());
        $this->assertEquals(ChargeStatus::paid(), $this->charge->getStatus());
        $this->assertEquals("metadata", $this->charge->getMetadata());
        $this->assertInstanceOf(Customer::class, $this->charge->getCustomer());
        $this->assertInstanceOf(CustomerId::class, $this->charge->getCustomerId());
        $this->assertInstanceOf(Invoice::class, $this->charge->getInvoice());
        $this->assertInstanceOf(InvoiceId::class, $this->charge->getInvoiceId());
        $this->assertCount(1, $this->charge->getTransactions());
        $this->assertEquals("urlBoleto", $this->charge->getBoletoLink());
        $this->assertEquals("urlBoleto", $this->charge->getBoletoUrl());
        $this->assertEquals(PaymentMethod::boleto(), $this->charge->getPaymentMethod());
        $this->assertInstanceOf(\DateTime::class, $this->charge->getCycleEnd());
        $this->assertInstanceOf(\DateTime::class, $this->charge->getCycleStart());
    }

    public function testChargeShouldBeCreated()
    {
        $charge = new Charge();
        $this->assertTrue($charge !== null);
    }

    public function testExpectedAnObjectOrderidToSetOrderId()
    {
        $charge = new Charge();
        $orderId = $this->createMock(OrderId::class);
        $charge->setOrderId($orderId);

        $this->assertEquals($orderId, $charge->getOrderId());
        $this->assertInstanceOf(OrderId::class, $charge->getOrderId());
    }

    public function testAmountShouldBeGreaterOrEqualToZero()
    {
        $charge = new Charge();
        $charge->setAmount(10);
        $this->assertEquals(10, $charge->getAmount());
    }

    /**
     * @expectedException Pagarme\Core\Kernel\Exceptions\InvalidParamException
     * @expectedExceptionMessage Amount should be greater or equal to 0! Passed value: -10
     */
    public function testShouldThrowAnExceptionIfAmountIsInvalid()
    {
        $this->expectException(\Pagarme\Core\Kernel\Exceptions\InvalidParamException::class);
        $charge = new Charge();
        $charge->setAmount(-10);
    }

    /**
     * @expectedException Pagarme\Core\Kernel\Exceptions\InvalidParamException
     * @expectedExceptionMessage  Amount should be an integer! Passed value: string
     */
    public function testShouldThrowAnExceptionIfAmountIsNotNumeric()
    {
        $this->expectException(\Pagarme\Core\Kernel\Exceptions\InvalidParamException::class);
        $charge = new Charge();
        $charge->setAmount("string");
    }

    public function testShouldReturnPaidAmount()
    {
        $charge = new Charge();
        $charge->setPaidAmount(10);
        $this->assertEquals(10, $charge->getPaidAmount());
    }

    public function testShouldReturnZeroIfPaidAmountIsNull()
    {
        $charge = new Charge();
        $this->assertEquals(0, $charge->getPaidAmount());
    }

    /**
     * @expectedException Pagarme\Core\Kernel\Exceptions\InvalidParamException
     * @expectedExceptionMessage  Amount should be an integer! Passed value: string
     */
    public function testShouldThrowAnExceptionIfPaidAmountIsNotNumeric()
    {
        $this->expectException(\Pagarme\Core\Kernel\Exceptions\InvalidParamException::class);
        $charge = new Charge();
        $charge->setPaidAmount("string");
    }

    public function testShouldReturnZeroIfTrySetPaidAmountWithNumberLessThanZero()
    {
        $charge = new Charge();
        $charge->setPaidAmount(-10);
        $this->assertEquals(0, $charge->getPaidAmount());
    }

    public function testShouldReturnCanceledAmount()
    {
        $charge = new Charge();
        $charge->setAmount(20);
        $charge->setCanceledAmount(10);
        $this->assertEquals(10, $charge->getCanceledAmount());
    }

    public function testShouldReturnZeroIfCanceledAmountIsNull()
    {
        $charge = new Charge();
        $this->assertEquals(0, $charge->getCanceledAmount());
    }

    /**
     * @expectedException Pagarme\Core\Kernel\Exceptions\InvalidParamException
     * @expectedExceptionMessage  Amount should be an integer! Passed value: string
     */
    public function testShouldThrowAnExceptionIfCanceledAmountIsNotNumeric()
    {
        $this->expectException(\Pagarme\Core\Kernel\Exceptions\InvalidParamException::class);
        $charge = new Charge();
        $charge->setCanceledAmount("string");
    }

    public function testShouldReturnZeroIfTrySetCanceledAmountWithNumberLessThanZero()
    {
        $charge = new Charge();
        $charge->setCanceledAmount(-10);
        $this->assertEquals(0, $charge->getCanceledAmount());
    }

    public function testShouldReturnAmountValueIfTrySetCanceledAmountWithNumberGreaterThanAmount()
    {
        $charge = new Charge();
        $charge->setAmount(5);
        $charge->setCanceledAmount(10);
        $this->assertEquals(5, $charge->getCanceledAmount());
    }


    public function testShouldReturnRefundedAmount()
    {
        $charge = new Charge();
        $charge->setPaidAmount(20);
        $charge->setRefundedAmount(10);
        $this->assertEquals(10, $charge->getRefundedAmount());
    }

    public function testShouldReturnZeroIfRefundedAmountIsNull()
    {
        $charge = new Charge();
        $this->assertEquals(0, $charge->getRefundedAmount());
    }

    /**
     * @expectedException Pagarme\Core\Kernel\Exceptions\InvalidParamException
     * @expectedExceptionMessage  Amount should be an integer! Passed value: string
     */
    public function testShouldThrowAnExceptionIfRefoundedAmountIsNotNumeric()
    {
        $this->expectException(\Pagarme\Core\Kernel\Exceptions\InvalidParamException::class);
        $charge = new Charge();
        $charge->setRefundedAmount("string");
    }

    public function testShouldReturnZeroIfTrySetRefundedAmountWithNumberLessThanZero()
    {
        $charge = new Charge();
        $charge->setRefundedAmount(-10);
        $this->assertEquals(0, $charge->getRefundedAmount());
    }

    public function testShouldReturnPaidAmountValueIfTrySetRefundedAmountWithNumberGreaterThanPaidAmount()
    {
        $charge = new Charge();
        $charge->setPaidAmount(5);
        $charge->setRefundedAmount(10);
        $this->assertEquals(5, $charge->getRefundedAmount());
    }

    public function testShouldJsonSerializeCorrectly()
    {
        $charge = new Charge();
        $charge->setCode("code");

        $chargeArray = json_decode(json_encode($charge), true);
        $error = json_last_error();

        $this->assertArrayHasKey("code", $chargeArray);
        $this->assertEquals(0, $error);
    }

    public function testShouldReturnEmptyArrayIfDoesntHaveTransactions()
    {
        $charge = new Charge();
        $this->assertEmpty($charge->getTransactions());
    }


    public function testShouldReturnEmptyArrayIfDoesntHaveLastTransaction()
    {
        $charge = new Charge();
        $this->assertEmpty($charge->getLastTransaction());
    }

    public function testShouldReturnTheLastTransactionThatWasAdded()
    {
        $transaction1 = $this->getTransaction("_____1");
        $transaction1->setCreatedAt(Carbon::now()->subMinutes(2));

        $transaction2 = $this->getTransaction("_____2");

        $charge = new Charge();
        $charge->addTransaction($transaction1);
        $charge->addTransaction($transaction2);

        $this->assertEquals($transaction2, $charge->getLastTransaction());
    }

    public function testShouldReturnLastTransaction()
    {
        $transaction1 = $this->getTransaction("_____1");
        $transaction1->setCreatedAt(Carbon::now()->addMinutes(2));

        $transaction2 = $this->getTransaction("_____2");

        $charge = new Charge();
        $charge->addTransaction($transaction1);
        $charge->addTransaction($transaction2);

        $this->assertEquals($transaction1, $charge->getLastTransaction());
    }

    public function testShouldNotAddTransactionOneMoreTime()
    {
        $transaction1 = $this->getTransaction("_____1");

        $charge = new Charge();
        $charge->addTransaction($transaction1);
        $charge->addTransaction($transaction1);

        $this->assertEquals($transaction1, $charge->getLastTransaction());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function testShouldUpdateATransaction()
    {
        $transaction = $this->getTransaction("_____1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction->setAmount(3);
        $charge->updateTransaction($transaction);

        $this->assertEquals(3, $charge->getLastTransaction()->getAmount());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function testShouldNotOverwriteATransactionIfNotSetToOvewrite()
    {
        $transaction = $this->getTransaction("_____1");
        $transaction->setId("ID_1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $updatedTransaction = $this->getTransaction("_____1");
        $updatedTransaction->setId("ID_2");

        $overwriteId = false;
        $charge->updateTransaction($updatedTransaction, $overwriteId);

        $this->assertEquals("ID_1", $charge->getLastTransaction()->getId());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function testShouldAddATransactionOnUpdateMethodIfTransactionWasNotAdded()
    {
        $transaction = $this->getTransaction("_____1");
        $transaction->setCreatedAt(Carbon::now()->subMinutes(3));
        $transaction->setId("ID_1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction2 = $this->getTransaction("_____2");
        $transaction2->setId("ID_2");

        $overwriteId = false;
        $charge->updateTransaction($transaction2, $overwriteId);

        $this->assertEquals("ID_2", $charge->getLastTransaction()->getId());
        $this->assertCount(2, $charge->getTransactions());
    }

    public function testShouldOverwriteATransactionIfSetToOvewrite()
    {
        $transaction = $this->getTransaction("_____1");
        $transaction->setId("ID_1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction->setId("ID_2");
        $overwriteId = true;
        $charge->updateTransaction($transaction, $overwriteId);

        $this->assertEquals("ID_2", $charge->getLastTransaction()->getId());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function testShouldPayPartialAChargeAndSetStatusHowUnderpaid()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $charge->addTransaction($transaction);

        $charge->pay(50);

        $this->assertEquals(50, $charge->getPaidAmount());
        $this->assertEquals(ChargeStatus::underpaid(), $charge->getStatus());
    }


    public function testShouldPayAChargeAndSetStatusHowPaid()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $charge->addTransaction($transaction);

        $charge->pay(100);

        $this->assertEquals(100, $charge->getPaidAmount());
        $this->assertEquals(ChargeStatus::paid(), $charge->getStatus());
    }


    public function testShouldPayAChargeWithValueGreaterThenAmountAndSetStatusHowOverpaid()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $transaction->setPaidAmount(200);
        $charge->addTransaction($transaction);

        // verificar se o valor da transação é que conta para setar uma charge para overpaid
        $charge->pay(200);

        $this->assertEquals(200, $charge->getPaidAmount());
        $this->assertEquals(ChargeStatus::overpaid(), $charge->getStatus());
    }

    public function testIfNotWasPassedAValueToCancelShouldCancelThePaidValue()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $charge->addTransaction($transaction);

        $charge->pay(100);

        $charge->cancel();

        $this->assertEquals(100, $charge->getRefundedAmount());
        $this->assertEquals(ChargeStatus::canceled(), $charge->getStatus());
    }

    public function testShouldCancelPartialAndSetStatusHowCanceled()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $charge->cancel(50);

        $this->assertEquals(
            100,
            $charge->getCanceledAmount(),
            "The canceled amount is the total charge value because the charge was not paid"
        );
        $this->assertEquals(ChargeStatus::canceled(), $charge->getStatus());
    }

    public function testShouldRefoundPartialValuePaidAndContinueWithStatusHowPaid()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $charge->addTransaction($transaction);

        $charge->pay(100);

        $charge->cancel(50);

        $this->assertEquals(
            50,
            $charge->getRefundedAmount()
        );
        $this->assertEquals(ChargeStatus::paid(), $charge->getStatus());
    }

    public function testShouldReturnInvoiceIdNullIfDoesntHaveInvoice()
    {
        $this->assertNull($this->charge->getInvoiceId());
    }

    public function getTransaction($endId)
    {
        $transactionId = "tran_1234567890" . $endId;

        $transaction = new Transaction();
        $transaction->setPagarmeId(
            new TransactionId($transactionId)
        );
        $transaction->setCreatedAt(Carbon::now());

        return $transaction;
    }

}