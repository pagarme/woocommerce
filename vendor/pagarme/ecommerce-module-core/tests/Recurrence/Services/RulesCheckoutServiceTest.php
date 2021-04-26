<?php

namespace Pagarme\Core\Test\Recurrence\Services;

use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\RulesCheckoutService;
use PHPUnit\Framework\TestCase;

class RulesCheckoutServiceTest extends TestCase
{
    public function testShouldReturnFalseBecauseThePaymentMethodIsIncompatible()
    {
        $rulesCheckoutService = new RulesCheckoutService();

        $productSubscriptionInCart = new ProductSubscription();
        $productSubscriptionInCart->setBoleto(true);
        $productSubscriptionInCart->setCreditCard(true);

        $productSubscriptionSelected = new ProductSubscription();
        $productSubscriptionSelected->setBoleto(false);
        $productSubscriptionSelected->setCreditCard(false);

        $repetitionInCart = new Repetition();
        $repetitionSelected = new Repetition();

        $result = $rulesCheckoutService->runRulesCheckoutSubscription(
            $productSubscriptionInCart,
            $productSubscriptionSelected,
            $repetitionInCart,
            $repetitionSelected
        );

        $this->assertFalse($result);
    }

    public function testShouldReturnFalseBecauseTheRepetitionIsIncompatible()
    {
        $rulesCheckoutService = new RulesCheckoutService();

        $productSubscriptionInCart = new ProductSubscription();
        $productSubscriptionSelected = new ProductSubscription();

        $repetitionInCart = new Repetition();
        $repetitionInCart->setIntervalCount(2);
        $repetitionInCart->setInterval('month');

        $repetitionSelected = new Repetition();
        $repetitionSelected->setIntervalCount(3);
        $repetitionSelected->setInterval('year');

        $result = $rulesCheckoutService->runRulesCheckoutSubscription(
            $productSubscriptionInCart,
            $productSubscriptionSelected,
            $repetitionInCart,
            $repetitionSelected
        );

        $this->assertFalse($result);
    }

    public function testShouldReturnTrueBecauseTheRepetitionAndPaymentMethodIsCompatible()
    {
        $rulesCheckoutService = new RulesCheckoutService();

        $productSubscriptionInCart = new ProductSubscription();
        $productSubscriptionInCart->setBoleto(true);
        $productSubscriptionInCart->setCreditCard(true);

        $productSubscriptionSelected = new ProductSubscription();
        $productSubscriptionSelected->setBoleto(true);
        $productSubscriptionSelected->setCreditCard(true);

        $repetitionInCart = new Repetition();
        $repetitionInCart->setIntervalCount(2);
        $repetitionInCart->setInterval('month');

        $repetitionSelected = new Repetition();
        $repetitionSelected->setIntervalCount(2);
        $repetitionSelected->setInterval('month');

        $result = $rulesCheckoutService->runRulesCheckoutSubscription(
            $productSubscriptionInCart,
            $productSubscriptionSelected,
            $repetitionInCart,
            $repetitionSelected
        );

        $this->assertTrue($result);
    }
}