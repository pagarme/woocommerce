<?php

namespace Pagarme\Core\Test\Recurrence\Aggregates;

use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use PHPUnit\Framework\TestCase;

class ProductSubscriptionTest extends TestCase
{
    private $productSubscription;

    protected function setUp(): void
    {
        $this->productSubscription = new ProductSubscription();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Product id should not be empty! Passed value:
     */
    public function testShouldNotAddAnEmptyProductId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Product id should not be empty! Passed value:");
        $this->productSubscription->setProductId("");
    }

    public function testShouldSetCorrectProductId()
    {
        $this->productSubscription->setProductId("23");
        $this->assertEquals("23", $this->productSubscription->getProductId());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Billing type should not be empty! Passed value:
     */
    public function testShouldNotAddAnEmptyBillingType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Billing type should not be empty! Passed value:");
        $this->productSubscription->setBillingType("");
    }

    public function testShouldSetCorrectBillingType()
    {
        $this->productSubscription->setBillingType("PREPAID");
        $this->assertEquals("PREPAID", $this->productSubscription->getBillingType());
    }

    public function testShouldCanAddAnRepetition()
    {
        $this->productSubscription->addRepetition(new Repetition());
        $this->productSubscription->addRepetition(new Repetition());
        $this->assertCount(2, $this->productSubscription->getRepetitions());
    }

    public function testShouldReturnTheRecurrenceType()
    {
        $this->assertEquals(
            'subscription',
            $this->productSubscription->getRecurrenceType()
        );
    }

    public function testShouldReturnACompleteProductSubscription()
    {
        $id = 1;
        $productId = "23";
        $creditCard = true;
        $boleto = true;
        $allowInstallments = true;
        $repetitions = [];
        $sellAsNormalProduct = true;
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $this->productSubscription->setId($id);
        $this->productSubscription->setProductId($productId);
        $this->productSubscription->setCreditCard($creditCard);
        $this->productSubscription->setBoleto($boleto);
        $this->productSubscription->setAllowInstallments($allowInstallments);
        $this->productSubscription->setRepetitions($repetitions);
        $this->productSubscription->setSellAsNormalProduct($sellAsNormalProduct);
        $this->productSubscription->setCreatedAt($createdAt);
        $this->productSubscription->setUpdatedAt($updatedAt);

        $this->assertEquals($id, $this->productSubscription->getId());
        $this->assertEquals($productId, $this->productSubscription->getProductId());
        $this->assertTrue($this->productSubscription->getCreditCard());
        $this->assertTrue($this->productSubscription->getBoleto());
        $this->assertTrue($this->productSubscription->getAllowInstallments());
        $this->assertEquals($repetitions, $this->productSubscription->getRepetitions());
        $this->assertTrue($this->productSubscription->getSellAsNormalProduct());
        $this->assertEquals($createdAt->format(productSubscription::DATE_FORMAT), $this->productSubscription->getCreatedAt());
        $this->assertEquals($updatedAt->format(productSubscription::DATE_FORMAT), $this->productSubscription->getUpdatedAt());
    }

    public function testShoudReturnJsonEncoded()
    {
        $id = 1;
        $productId = "23";
        $creditCard = true;
        $boleto = true;
        $allowInstallments = true;
        $repetitions = [];
        $sellAsNormalProduct = true;
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $this->productSubscription->setId($id);
        $this->productSubscription->setProductId($productId);
        $this->productSubscription->setCreditCard($creditCard);
        $this->productSubscription->setBoleto($boleto);
        $this->productSubscription->setAllowInstallments($allowInstallments);
        $this->productSubscription->setRepetitions($repetitions);
        $this->productSubscription->setSellAsNormalProduct($sellAsNormalProduct);
        $this->productSubscription->setCreatedAt($createdAt);
        $this->productSubscription->setUpdatedAt($updatedAt);

        $this->assertJson(json_encode($this->productSubscription));
    }

    public function testShouldReturnFalseBecauseDoesntHasTheSamePaymentMethodConfig()
    {
        $productSubscription = new ProductSubscription();
        $productSubscription->setBoleto(false);
        $productSubscription->setCreditCard(true);

        $productSubscription2 = new ProductSubscription();
        $productSubscription2->setBoleto(true);
        $productSubscription2->setCreditCard(false);

        $this->assertFalse($productSubscription->checkProductHasSamePaymentMethod($productSubscription2));
    }

    public function testShouldReturnTrueBecauseThePaymentMethodConfigAreCompatibles()
    {
        $productSubscription = new ProductSubscription();
        $productSubscription->setBoleto(true);
        $productSubscription->setCreditCard(true);

        $productSubscription2 = new ProductSubscription();
        $productSubscription2->setBoleto(true);
        $productSubscription2->setCreditCard(true);

        $this->assertTrue($productSubscription->checkProductHasSamePaymentMethod($productSubscription2));
    }
}
