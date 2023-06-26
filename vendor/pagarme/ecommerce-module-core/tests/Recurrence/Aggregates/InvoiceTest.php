<?php

namespace Pagarme\Core\Test\Recurrence\Aggregates;

use Pagarme\Core\Kernel\ValueObjects\Id\InvoiceId;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\Aggregates\Cycle;
use Pagarme\Core\Recurrence\Aggregates\Invoice;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    /**
     * @var Invoice
     */
    private $invoice;

    protected function setUp(): void
    {
        $this->invoice = new Invoice();
    }

    public function testInvoiceObject()
    {
        $cycle = new Cycle();
        $cycle->setCycleStart(new \DateTime());
        $cycle->setCycleEnd((new \DateTime)->add(new \DateInterval('P10D')));

        $this->invoice->setPagarmeId(new InvoiceId('in_45asDadb8Xd95451'));
        $this->invoice->setId(1);
        $this->invoice->setCustomer(new Customer());
        $this->invoice->setPaymentMethod('credit_card');
        $this->invoice->setStatus('paid');
        $this->invoice->setAmount(100);
        $this->invoice->setCharge(new Charge());
        $this->invoice->setInstallments(true);
        $this->invoice->setCycle($cycle);
        $this->invoice->setSubscriptionId(new SubscriptionId('sub_hdgeifuaudiv9ek3'));
        $this->invoice->setTotalDiscount(100);
        $this->invoice->setTotalIncrement(100);

        $this->assertEquals('in_45asDadb8Xd95451', $this->invoice->getPagarmeId()->getValue());
        $this->assertEquals(1, $this->invoice->getId());
        $this->assertEquals('credit_card', $this->invoice->getPaymentMethod());
        $this->assertEquals('paid', $this->invoice->getStatus());
        $this->assertEquals(100, $this->invoice->getAmount());
        $this->assertEquals(100, $this->invoice->getTotalDiscount());
        $this->assertEquals(100, $this->invoice->getTotalIncrement());
        $this->assertInstanceOf(\DateTime::class, $this->invoice->getCycleStart());
        $this->assertInstanceOf(\DateTime::class,$this->invoice->getCycleEnd());
        $this->assertContainsOnlyInstancesOf(Customer::class, [$this->invoice->getCustomer()]);
        $this->assertContainsOnlyInstancesOf(Charge::class, [$this->invoice->getCharge()]);
        $this->assertContainsOnlyInstancesOf(Cycle::class, [$this->invoice->getCycle()]);
        $this->assertContainsOnlyInstancesOf(SubscriptionId::class, [$this->invoice->getSubscriptionId()]);
        $this->assertContainsOnly('boolean', [$this->invoice->getInstallments()]);
    }

    public function testReturnInvoiceObjectSerialized()
    {
        $this->assertJson(json_encode($this->invoice));
    }

    public function testShouldReturnNullOnCycleStartAndCycleEnd()
    {
        $this->assertNull($this->invoice->getCycleStart());
        $this->assertNull($this->invoice->getCycleEnd());
    }
}
