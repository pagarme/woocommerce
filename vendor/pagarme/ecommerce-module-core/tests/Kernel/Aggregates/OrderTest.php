<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\TransactionId;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use Pagarme\Core\Payment\Aggregates\Customer;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    private $order;

    protected function setUp(): void
    {
        $this->order = new Order();
        parent::setUp();
    }

    // ========== TESTES BÁSICOS ==========

    public function testOrderShouldBeCreated()
    {
        $order = new Order();
        $this->assertTrue($order !== null);
        $this->assertInstanceOf(Order::class, $order);
    }

    public function testSetAndGetPlatformOrder()
    {
        $platformOrder = $this->createMock(PlatformOrderInterface::class);
        $platformOrder->method('getCode')->willReturn('ORDER-123');
        
        $this->order->setPlatformOrder($platformOrder);
        
        $this->assertEquals($platformOrder, $this->order->getPlatformOrder());
        $this->assertInstanceOf(PlatformOrderInterface::class, $this->order->getPlatformOrder());
    }

    public function testGetCodeFromPlatformOrder()
    {
        $platformOrder = $this->createMock(PlatformOrderInterface::class);
        $platformOrder->method('getCode')->willReturn('ORDER-456');
        
        $this->order->setPlatformOrder($platformOrder);
        
        $this->assertEquals('ORDER-456', $this->order->getCode());
    }

    public function testSetAndGetStatus()
    {
        $status = OrderStatus::pending();
        $this->order->setStatus($status);
        
        $this->assertEquals($status, $this->order->getStatus());
        $this->assertInstanceOf(OrderStatus::class, $this->order->getStatus());
    }

    public function testGetAmountWithNoCharges()
    {
        $amount = $this->order->getAmount();
        $this->assertEquals(0, $amount);
    }

    public function testGetAmountWithMultipleCharges()
    {
        $charge1 = $this->createChargeWithAmount(1000, 'ch_1');
        $charge2 = $this->createChargeWithAmount(2000, 'ch_2');
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        
        $this->assertEquals(3000, $this->order->getAmount());
    }

    public function testGetChargesReturnsEmptyArrayWhenNull()
    {
        $charges = $this->order->getCharges();
        
        $this->assertIsArray($charges);
        $this->assertEmpty($charges);
    }

    public function testJsonSerialize()
    {
        $platformOrder = $this->createMock(PlatformOrderInterface::class);
        $platformOrder->method('getCode')->willReturn('ORDER-789');
        
        $this->order->setPlatformOrder($platformOrder);
        $this->order->setStatus(OrderStatus::pending());
        
        $json = json_encode($this->order);
        $this->assertJson($json);
        
        $data = json_decode($json, true);
        $this->assertArrayHasKey('code', $data);
        $this->assertEquals('ORDER-789', $data['code']);
    }

    // ========== TESTES DE CHARGES ==========

    public function testAddCharge()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_1');
        
        $this->order->addCharge($charge);
        
        $charges = $this->order->getCharges();
        $this->assertCount(1, $charges);
        $this->assertEquals($charge, $charges[0]);
    }

    public function testAddMultipleCharges()
    {
        $charge1 = $this->createChargeWithAmount(1000, 'ch_1');
        $charge2 = $this->createChargeWithAmount(2000, 'ch_2');
        $charge3 = $this->createChargeWithAmount(3000, 'ch_3');
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        $this->order->addCharge($charge3);
        
        $charges = $this->order->getCharges();
        $this->assertCount(3, $charges);
    }

    public function testAddChargeShouldNotAddDuplicate()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_duplicate');
        
        $this->order->addCharge($charge);
        $this->order->addCharge($charge);
        
        $charges = $this->order->getCharges();
        $this->assertCount(1, $charges, 'Should not add duplicate charge');
    }

    public function testUpdateChargeExisting()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_update');
        $this->order->addCharge($charge);
        
        // Update charge
        $charge->setAmount(2000);
        $this->order->updateCharge($charge);
        
        $charges = $this->order->getCharges();
        $this->assertCount(1, $charges);
        $this->assertEquals(2000, $charges[0]->getAmount());
    }

    public function testUpdateChargeWithOverwriteId()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_id_test');
        $charge->setId('ID_ORIGINAL');
        $this->order->addCharge($charge);
        
        // Update with different ID but overwrite
        $updatedCharge = $this->createChargeWithAmount(2000, 'ch_id_test');
        $updatedCharge->setId('ID_NEW');
        
        $this->order->updateCharge($updatedCharge, true);
        
        $charges = $this->order->getCharges();
        $this->assertEquals('ID_ORIGINAL', $charges[0]->getId());
        $this->assertEquals(2000, $charges[0]->getAmount());
    }

    public function testUpdateChargeNonExisting()
    {
        $charge1 = $this->createChargeWithAmount(1000, 'ch_1');
        $this->order->addCharge($charge1);
        
        $charge2 = $this->createChargeWithAmount(2000, 'ch_2');
        $this->order->updateCharge($charge2);
        
        $charges = $this->order->getCharges();
        $this->assertCount(2, $charges, 'Should add charge when updating non-existing');
    }

    public function testUpdateChargeWithRefundedAmountEqualsPaid()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_refund');
        $charge->setPaidAmount(1000);
        $charge->setStatus(ChargeStatus::paid());
        $this->order->addCharge($charge);
        
        // Update with refund
        $charge->setRefundedAmount(1000);
        $this->order->updateCharge($charge);
        
        $charges = $this->order->getCharges();
        $this->assertTrue($charges[0]->getStatus()->equals(ChargeStatus::canceled()));
    }

    // ========== TESTES DE STATUS ==========

    public function testApplyOrderStatusWithNoCharges()
    {
        $this->order->setStatus(OrderStatus::pending());
        $this->order->applyOrderStatusFromCharges();
        
        // Should not change status when no charges
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::pending()));
    }

    public function testApplyOrderStatusAllChargesPaid()
    {
        $charge1 = $this->createChargeWithStatus('ch_1', ChargeStatus::paid());
        $charge2 = $this->createChargeWithStatus('ch_2', ChargeStatus::paid());
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        $this->order->applyOrderStatusFromCharges();
        
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::paid()));
    }

    public function testApplyOrderStatusAllChargesOverpaid()
    {
        $charge = $this->createChargeWithStatus('ch_over', ChargeStatus::overpaid());
        
        $this->order->addCharge($charge);
        $this->order->applyOrderStatusFromCharges();
        
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::paid()));
    }

    public function testApplyOrderStatusMixedPaidAndOverpaid()
    {
        $charge1 = $this->createChargeWithStatus('ch_1', ChargeStatus::paid());
        $charge2 = $this->createChargeWithStatus('ch_2', ChargeStatus::overpaid());
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        $this->order->applyOrderStatusFromCharges();
        
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::paid()));
    }

    public function testApplyOrderStatusWithCanceled()
    {
        $charge1 = $this->createChargeWithStatus('ch_1', ChargeStatus::paid());
        $charge2 = $this->createChargeWithStatus('ch_2', ChargeStatus::canceled());
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        $this->order->applyOrderStatusFromCharges();
        
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::canceled()));
    }

    public function testApplyOrderStatusWithFailed()
    {
        $charge1 = $this->createChargeWithStatus('ch_1', ChargeStatus::pending());
        $charge2 = $this->createChargeWithStatus('ch_2', ChargeStatus::failed());
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        $this->order->applyOrderStatusFromCharges();
        
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::failed()));
    }

    public function testApplyOrderStatusAllChargesPending()
    {
        $charge1 = $this->createChargeWithStatus('ch_1', ChargeStatus::pending());
        $charge2 = $this->createChargeWithStatus('ch_2', ChargeStatus::pending());
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        $this->order->applyOrderStatusFromCharges();
        
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::pending()));
    }

    public function testApplyOrderStatusAllChargesUnderpaid()
    {
        $charge = $this->createChargeWithStatus('ch_under', ChargeStatus::underpaid());
        
        $this->order->addCharge($charge);
        $this->order->setStatus(OrderStatus::pending());
        $this->order->applyOrderStatusFromCharges();
        
        // Underpaid should not trigger status change
        $this->assertTrue($this->order->getStatus()->equals(OrderStatus::pending()));
    }

    // ========== TESTES DE TRANSAÇÕES ==========

    public function testGetPixOrBilletTransactionWithPix()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_pix');
        $transaction = $this->createTransactionWithType('tran_pix', 'pix');
        $charge->addTransaction($transaction);
        
        $this->order->addCharge($charge);
        
        $result = $this->order->getPixOrBilletTransaction();
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertEquals('pix', $result->getTransactionType()->getType());
    }

    public function testGetPixOrBilletTransactionWithBoleto()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_boleto');
        $transaction = $this->createTransactionWithType('tran_boleto', 'boleto');
        $charge->addTransaction($transaction);
        
        $this->order->addCharge($charge);
        
        $result = $this->order->getPixOrBilletTransaction();
        $this->assertInstanceOf(Transaction::class, $result);
        $this->assertEquals('boleto', $result->getTransactionType()->getType());
    }

    public function testGetPixOrBilletTransactionWithCreditCard()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_cc');
        $transaction = $this->createTransactionWithType('tran_cc', 'credit_card');
        $charge->addTransaction($transaction);
        
        $this->order->addCharge($charge);
        
        $result = $this->order->getPixOrBilletTransaction();
        $this->assertNull($result);
    }

    public function testGetPixOrBilletTransactionWithNoTransactions()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_empty');
        $this->order->addCharge($charge);
        
        $result = $this->order->getPixOrBilletTransaction();
        $this->assertNull($result);
    }

    // ========== TESTES DE SPLIT ==========

    public function testGetSplitInfoWithSplit()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_split');
        $transaction = $this->createTransactionWithSplit('tran_split');
        $charge->addTransaction($transaction);
        
        $this->order->addCharge($charge);
        
        $splitInfo = $this->order->getSplitInfo();
        $this->assertIsArray($splitInfo);
        $this->assertNotEmpty($splitInfo);
    }

    public function testGetSplitInfoWithoutSplit()
    {
        $charge = $this->createChargeWithAmount(1000, 'ch_no_split');
        $transaction = $this->createTransactionWithType('tran_no_split', 'credit_card');
        $charge->addTransaction($transaction);
        
        $this->order->addCharge($charge);
        
        $splitInfo = $this->order->getSplitInfo();
        $this->assertIsArray($splitInfo);
        $this->assertEmpty($splitInfo);
    }

    public function testGetSplitInfoWithMultipleCharges()
    {
        $charge1 = $this->createChargeWithAmount(1000, 'ch_split_1');
        $transaction1 = $this->createTransactionWithSplit('tran_split_1');
        $charge1->addTransaction($transaction1);
        
        $charge2 = $this->createChargeWithAmount(2000, 'ch_split_2');
        $transaction2 = $this->createTransactionWithSplit('tran_split_2');
        $charge2->addTransaction($transaction2);
        
        $this->order->addCharge($charge1);
        $this->order->addCharge($charge2);
        
        $splitInfo = $this->order->getSplitInfo();
        $this->assertIsArray($splitInfo);
        $this->assertCount(2, $splitInfo);
    }

    public function testSetAndGetCustomer()
    {
        $customer = new Customer();
        $customer->setName('Test Customer');
        
        $this->order->setCustomer($customer);
        
        $this->assertInstanceOf(Customer::class, $this->order->getCustomer());
        $this->assertEquals('Test Customer', $this->order->getCustomer()->getName());
    }

    // ========== HELPER METHODS ==========

    private function createChargeWithAmount($amount, $idSuffix)
    {
        $charge = new Charge();
        $charge->setAmount($amount);
        $charge->setPagarmeId(new ChargeId('ch_' . $idSuffix . '1234567890'));
        $charge->setStatus(ChargeStatus::pending());
        return $charge;
    }

    private function createChargeWithStatus($idSuffix, ChargeStatus $status)
    {
        $charge = $this->createChargeWithAmount(1000, $idSuffix);
        $charge->setStatus($status);
        return $charge;
    }

    private function createTransactionWithType($idSuffix, $type)
    {
        $transaction = new Transaction();
        $transaction->setPagarmeId(new TransactionId($idSuffix . '1234567890'));
        $transaction->setChargeId(new ChargeId('ch_1234567890'));
        $transaction->setTransactionType(new TransactionType($type));
        $transaction->setAmount(1000);
        $transaction->setCreatedAt(new \DateTime());
        
        // Set empty postData
        $postData = new \stdClass();
        $transaction->setPostData($postData);
        
        return $transaction;
    }

    private function createTransactionWithSplit($idSuffix)
    {
        $transaction = $this->createTransactionWithType($idSuffix, 'credit_card');
        
        // Create split data
        $postData = new \stdClass();
        $split = new \stdClass();
        $split->recipient = new \stdClass();
        $split->recipient->name = 'Recipient Test';
        $split->recipient->id = 'rp_123456';
        $split->type = 'percentage';
        $split->amount = 100;
        $postData->split = [$split];
        
        $transaction->setPostData($postData);
        
        return $transaction;
    }
}
