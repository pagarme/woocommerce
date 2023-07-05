<?php

namespace Pagarme\Core\Test\Recurrence\Aggregates;

use PagarmeCoreApiLib\Models\CreatePlanRequest;
use PagarmeCoreApiLib\Models\UpdatePlanRequest;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Aggregates\SubProduct;
use Pagarme\Core\Recurrence\ValueObjects\PlanId;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use PHPUnit\Framework\TestCase;

class PlanTest extends TestCase
{
    private $plan;

    protected function setUp(): void
    {
        $this->plan = new Plan();
    }

    public function testJsonSerializeShouldReturnAnInstanceOfStdClass()
    {
        $this->assertInstanceOf(\stdClass::class, $this->plan->jsonSerialize());
    }

    public function testJsonSerializeShouldSetAllProperties()
    {
        $id = '1';
        $name = "Product Name";
        $description = "Product Description";
        $interval = IntervalValueObject::month(2);
        $planId =  new PlanId('plan_45asDadb8Xd95451');
        $productId = '4123';
        $creditCard = true;
        $boleto = false;
        $items = [
            new SubProduct(),
            new SubProduct()
        ];
        $status = 'ACTIVE';
        $billingType = 'PREPAID';
        $allowInstallments = true;
        $createdAt = new \Datetime();
        $updatedAt = new \Datetime();
        $intervalType = "month";
        $intervalCount = 10;

        $this->plan->setId($id);
        $this->assertEquals($this->plan->getId(), $id);

        $this->plan->setName($name);
        $this->assertEquals($this->plan->getName(), $name);

        $this->plan->setDescription($description);
        $this->assertEquals($this->plan->getDescription(), $description);

        $this->plan->setItems($items);
        $this->assertEquals($this->plan->getItems(), $items);

        $this->plan->setInterval($interval);
        $this->assertEquals($this->plan->getInterval(), $interval);

        $this->plan->setPagarmeId($planId);
        $this->assertEquals($this->plan->getPagarmeId(), $planId);

        $this->plan->setProductId($productId);
        $this->assertEquals($this->plan->getProductId(), $productId);

        $this->plan->setCreditCard($creditCard);
        $this->assertEquals($this->plan->getCreditCard(), $creditCard);

        $this->plan->setBoleto($boleto);
        $this->assertEquals($this->plan->getBoleto(), $boleto);

        $this->plan->setStatus($status);
        $this->assertEquals($this->plan->getStatus(), $status);

        $this->plan->setBillingType($billingType);
        $this->assertEquals($this->plan->getBillingType(), $billingType);

        $this->plan->setAllowInstallments($allowInstallments);
        $this->assertEquals($this->plan->getAllowInstallments(), $allowInstallments);

        $this->plan->setCreatedAt($createdAt);
        $this->assertIsString($this->plan->getCreatedAt());

        $this->plan->setUpdatedAt($updatedAt);
        $this->assertIsString($this->plan->getUpdatedAt());

        $this->plan->setIntervalType($intervalType);
        $this->assertEquals($this->plan->getIntervalType(), $intervalType);

        $this->plan->setIntervalCount($intervalCount);
        $this->assertEquals($this->plan->getIntervalCount(), $intervalCount);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Product id should be an integer! Passed value:
     */
    public function testShouldNotAddAnEmptyProductId()
    {
        $this->expectException(\Exception::class);
        $this->plan->setProductId("");
    }

    public function testShouldSetCorrectProductId()
    {
        $this->plan->setProductId("23");
        $this->assertEquals("23", $this->plan->getProductId());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Billing type should not be empty! Passed value:
     */
    public function testShouldNotAddAnEmptyBillingType()
    {
        $this->expectException(\Exception::class);
        $this->plan->setBillingType("");
    }

    public function testShouldSetCorrectBillingType()
    {
        $this->plan->setBillingType("PREPAID");
        $this->assertEquals("PREPAID", $this->plan->getBillingType());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Status should not be empty! Passed value:
     */
    public function testShouldNotAddAnEmptyStatus()
    {
        $this->expectException(\Exception::class);
        $this->plan->setStatus("");
    }

    public function testShouldSetCorrectStatus()
    {
        $this->plan->setStatus("active");
        $this->assertEquals("active", $this->plan->getStatus());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Trial period days should be an integer! Passed value:
     */
    public function testShouldNotAddAnNotIntegerTrialPeriodDays()
    {
        $this->expectException(\Exception::class);
        $this->plan->setTrialPeriodDays("");
    }

    public function testShouldSetCorrectTrialPeriodDays()
    {
        $this->plan->setTrialPeriodDays(10);
        $this->assertEquals(10, $this->plan->getTrialPeriodDays());
    }

    public function testShouldSetCorrectValueToBoleto()
    {
        $this->plan->setBoleto("1");
        $this->assertEquals("1", $this->plan->getBoleto());
    }

    public function testShouldSetCorrectValueToCreditCard()
    {
        $this->plan->setCreditCard("1");
        $this->assertEquals("1", $this->plan->getCreditCard());
    }

    public function testShouldSetCorrectValueToAllowInstallments()
    {
        $this->plan->setAllowInstallments("1");
        $this->assertEquals("1", $this->plan->getAllowInstallments());
    }

    public function testShouldReturnIntervalCountSetted()
    {
        $interval = IntervalValueObject::month(2);
        $this->plan->setInterval($interval);

        $this->assertEquals("month", $this->plan->getIntervalType());
        $this->assertEquals("2", $this->plan->getIntervalCount());
    }

    public function testAPlanAggregateShouldBeAPlanRecurrenceType()
    {
        $this->assertEquals("plan", $this->plan->getRecurrenceType());
    }

    public function testShouldReturnACreatePlanRequestObject()
    {
        $this->assertInstanceOf(CreatePlanRequest::class, $this->plan->convertToSdkRequest());
    }

    public function testShouldReturnACreatePlanRequestObjectWithPaymentMethods()
    {
        $this->plan->setCreditCard(true);
        $this->plan->setBoleto(true);
        $sdkObject = $this->plan->convertToSdkRequest();

        $this->assertInstanceOf(CreatePlanRequest::class, $sdkObject);
        $this->assertCount(2, $sdkObject->paymentMethods);
    }

    public function testShouldReturnACreatePlanRequestObjectWithItems()
    {
        $items = [
            new SubProduct(),
            new SubProduct()
        ];

        $this->plan->setItems($items);

        $sdkObject = $this->plan->convertToSdkRequest();

        $this->assertInstanceOf(CreatePlanRequest::class, $sdkObject);
        $this->assertCount(2, $sdkObject->items);
    }

    public function testShouldReturnAUpdatePlanRequestObject()
    {
        $update = true;
        $this->assertInstanceOf(UpdatePlanRequest::class, $this->plan->convertToSdkRequest($update));
    }

    public function testShouldReturnAUpdatePlanRequestObjectWithPaymentMethods()
    {
        $update = true;
        $this->plan->setCreditCard(true);
        $this->plan->setBoleto(true);
        $sdkObject = $this->plan->convertToSdkRequest($update);

        $this->assertInstanceOf(UpdatePlanRequest::class, $sdkObject);
        $this->assertCount(2, $sdkObject->paymentMethods);
    }

    public function testShouldReturnAUpdatePlanRequestObjectWithItems()
    {
        $update = true;
        $items = [
            new SubProduct(),
            new SubProduct()
        ];

        $this->plan->setItems($items);

        $sdkObject = $this->plan->convertToSdkRequest($update);

        $this->assertInstanceOf(UpdatePlanRequest::class, $sdkObject);
        $this->assertCount(2, $sdkObject->items);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Interval not find
     */
    public function testShouldReturnAnExceptionIfTrySetAnInvalidIntervalType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Interval not find");
        $this->plan->setIntervalType("wrong interval Type");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Interval count not compatible
     */
    public function testShouldReturnAnExceptionIfTrySetAnInvalidIntervalCount()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Interval count not compatible");
        $this->plan->setIntervalCount("wrong interval count");
    }
}
