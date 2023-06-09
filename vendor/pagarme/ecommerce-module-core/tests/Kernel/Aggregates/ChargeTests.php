<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\Id\TransactionId;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;

class ChargeTests extends TestCase
{
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
     * @throws InvalidParamException
     * @expectedExceptionMessage Amount should be greater or equal to 0! Passed value: -10
     * @expectedExceptionCode 400
     */
    public function test_should_throw_an_exception_if_amount_is_invalid()
    {
        $this->expectException(InvalidParamException::class);

        $charge = new Charge();
        $charge->setAmount(-10);
    }

    public function test_should_return_paid_amount()
    {
        $charge = new Charge();
        $charge->setPaidAmount(10);
        $this->assertEquals(10, $charge->getPaidAmount());
    }

    public function test_should_return_zero_if_paid_amount_is_null()
    {
        $charge = new Charge();
        $this->assertEquals(0, $charge->getPaidAmount());
    }

    public function test_should_return_zero_if_try_set_paid_amount_with_number_less_than_zero()
    {
        $charge = new Charge();
        $charge->setPaidAmount(-10);
        $this->assertEquals(0, $charge->getPaidAmount());
    }

    public function test_should_return_canceled_amount()
    {
        $charge = new Charge();
        $charge->setAmount(20);
        $charge->setCanceledAmount(10);
        $this->assertEquals(10, $charge->getCanceledAmount());
    }

    public function test_should_return_zero_if_canceled_amount_is_null()
    {
        $charge = new Charge();
        $this->assertEquals(0, $charge->getCanceledAmount());
    }

    public function test_should_return_zero_if_try_set_canceled_amount_with_number_less_than_zero()
    {
        $charge = new Charge();
        $charge->setCanceledAmount(-10);
        $this->assertEquals(0, $charge->getCanceledAmount());
    }

    public function test_should_return_amount_value_if_try_set_canceled_amount_with_number_greater_than_amount()
    {
        $charge = new Charge();
        $charge->setAmount(5);
        $charge->setCanceledAmount(10);
        $this->assertEquals(5, $charge->getCanceledAmount());
    }


    public function test_should_return_refunded_amount()
    {
        $charge = new Charge();
        $charge->setPaidAmount(20);
        $charge->setRefundedAmount(10);
        $this->assertEquals(10, $charge->getRefundedAmount());
    }

    public function test_should_return_zero_if_refunded_amount_is_null()
    {
        $charge = new Charge();
        $this->assertEquals(0, $charge->getRefundedAmount());
    }

    public function test_should_return_zero_if_try_set_refunded_amount_with_number_less_than_zero()
    {
        $charge = new Charge();
        $charge->setRefundedAmount(-10);
        $this->assertEquals(0, $charge->getRefundedAmount());
    }

    public function test_should_return_paid_amount_value_if_try_set_refunded_amount_with_number_greater_than_paid_amount()
    {
        $charge = new Charge();
        $charge->setPaidAmount(5);
        $charge->setRefundedAmount(10);
        $this->assertEquals(5, $charge->getRefundedAmount());
    }

    public function test_should_return_code()
    {
        $charge = new Charge();
        $code = "code_1234";
        $charge->setCode($code);

        $this->assertEquals($code, $charge->getCode());
    }

    public function test_expected_an_object_chargestatus_to_set_status()
    {
        $charge = new Charge();
        $chargeStatus = ChargeStatus::paid();
        $charge->setStatus($chargeStatus);

        $this->assertEquals($chargeStatus, $charge->getStatus());
        $this->assertInstanceOf(ChargeStatus::class, $charge->getStatus());
    }

    public function test_should_return_metadata()
    {
        $charge = new Charge();
        $metadata = ["metadata"];
        $charge->setMetadata($metadata);

        $this->assertEquals($metadata, $charge->getMetadata());
    }

    public function test_should_json_serialize_correctly()
    {
        $charge = new Charge();
        $charge->setCode("code");

        $chargeArray = json_decode(json_encode($charge), true);
        $error = json_last_error();

        $this->assertArrayHasKey("code", $chargeArray);
        $this->assertEquals(0, $error);
    }

    public function test_should_return_empty_array_if_doesnt_have_transactions()
    {
        $charge = new Charge();
        $this->assertEmpty($charge->getTransactions());
    }


    public function test_should_return_empty_array_if_doesnt_have_last_transaction()
    {
        $charge = new Charge();
        $this->assertEmpty($charge->getLastTransaction());
    }

    public function test_should_return_the_last_transaction_that_was_added()
    {
        $transaction1 = $this->getTransaction("_____1");
        $transaction1->setCreatedAt(Carbon::now()->subMinutes(2));

        $transaction2 = $this->getTransaction("_____2");

        $charge = new Charge();
        $charge->addTransaction($transaction1);
        $charge->addTransaction($transaction2);

        $this->assertEquals($transaction2, $charge->getLastTransaction());
    }

    public function test_should_return_last_transaction()
    {
        $transaction1 = $this->getTransaction("_____1");
        $transaction1->setCreatedAt(Carbon::now()->addMinutes(2));

        $transaction2 = $this->getTransaction("_____2");

        $charge = new Charge();
        $charge->addTransaction($transaction1);
        $charge->addTransaction($transaction2);

        $this->assertEquals($transaction1, $charge->getLastTransaction());
    }

    public function test_should_not_add_transaction_one_more_time()
    {
        $transaction1 = $this->getTransaction("_____1");

        $charge = new Charge();
        $charge->addTransaction($transaction1);
        $charge->addTransaction($transaction1);

        $this->assertEquals($transaction1, $charge->getLastTransaction());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function test_should_update_a_transaction()
    {
        $transaction = $this->getTransaction("_____1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction->setAmount(3);
        $charge->updateTransaction($transaction);

        $this->assertEquals(3, $charge->getLastTransaction()->getAmount());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function test_should_not_overwrite_a_transaction_if_not_set_to_ovewrite()
    {
        $this->markTestSkipped();

        $transaction = $this->getTransaction("_____1");
        $transaction->setId("ID_1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction->setId("ID_2");

        $ovewrite = false;
        $charge->updateTransaction($transaction, $ovewrite);

        $this->assertEquals("ID_1", $charge->getLastTransaction()->getId());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function test_should_add_a_transaction_on_update_method_if_transaction_was_not_added()
    {
        $transaction = $this->getTransaction("_____1");
        $transaction->setCreatedAt(Carbon::now()->subMinutes(3));
        $transaction->setId("ID_1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction2 = $this->getTransaction("_____2");
        $transaction2->setId("ID_2");

        $ovewrite = false;
        $charge->updateTransaction($transaction2, $ovewrite);

        $this->assertEquals("ID_2", $charge->getLastTransaction()->getId());
        $this->assertCount(2, $charge->getTransactions());
    }

    public function test_should_overwrite_a_transaction_if_set_to_ovewrite()
    {
        $transaction = $this->getTransaction("_____1");
        $transaction->setId("ID_1");

        $charge = new Charge();
        $charge->addTransaction($transaction);

        $transaction->setId("ID_2");
        $ovewrite = true;
        $charge->updateTransaction($transaction, $ovewrite);

        $this->assertEquals("ID_2", $charge->getLastTransaction()->getId());
        $this->assertCount(1, $charge->getTransactions());
    }

    public function test_should_pay_partial_a_charge_and_set_status_how_underpaid()
    {
        $this->markTestSkipped();
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $charge->addTransaction($transaction);

        $charge->pay(50);

        $this->assertEquals(50, $charge->getPaidAmount());
        $this->assertEquals(ChargeStatus::underpaid(), $charge->getStatus());
    }


    public function test_should_pay_a_charge_and_set_status_how_paid()
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


    public function test_should_pay_a_charge_with_value_greater_then_amount_and_set_status_how_overpaid()
    {
        $charge = new Charge();
        $charge->setAmount(100);
        $charge->setStatus(ChargeStatus::pending());

        $transaction = $this->getTransaction("_____1");
        $transaction->setPaidAmount(200);
        $charge->addTransaction($transaction);

        // verificar se o valor d transação é que cota para setar uma charge para overpaid
        $charge->pay(200);

        $this->assertEquals(200, $charge->getPaidAmount());
        $this->assertEquals(ChargeStatus::overpaid(), $charge->getStatus());
    }

    public function test_if_not_was_passed_a_value_to_cancel_should_cancel_the_paid_value()
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

    public function test_should_cancel_partial_and_set_status_how_canceled()
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

    public function test_should_refound_partial_value_paid_and_continue_with_status_how_paid()
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