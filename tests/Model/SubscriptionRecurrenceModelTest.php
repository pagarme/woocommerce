<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain;
use Mockery;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Subscription;

class SubscriptionRecurrenceModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Brain\Monkey\setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        Brain\Monkey\tearDown();
    }

    /**
     * @test
     * @group recurrence
     */
    public function hasFixedEndDateReturnsTrueWhenProductHasDefinedLength()
    {
        $productId = 123;
        $length = 10; // 10 billing cycles

        Brain\Monkey\Functions\when('WC_Subscriptions_Product::get_length')
            ->returnArg(0)
            ->times(1);

        $reflection = new \ReflectionClass(Subscription::class);
        $method = $reflection->getMethod('hasFixedEndDate');
        $method->setAccessible(true);

        $result = $method->invoke(null, $productId);

        $this->assertTrue($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function hasFixedEndDateReturnsFalseWhenProductHasNoDefinedLength()
    {
        $productId = 124;

        Brain\Monkey\Functions\when('WC_Subscriptions_Product::get_length')
            ->returnArg(0)
            ->times(1);

        $reflection = new \ReflectionClass(Subscription::class);
        $method = $reflection->getMethod('hasFixedEndDate');
        $method->setAccessible(true);

        // Simulate get_length returning 0
        \Mockery::mock('overload:WC_Subscriptions_Product')
            ->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(0);

        $result = $method->invoke(null, $productId);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelFromProductIdReturnsSubscriptionWhenFixedDuration()
    {
        $productId = 125;

        // Mock WC_Subscriptions_Product::get_length to return 5 (fixed duration)
        $wcsProductMock = Mockery::mock('overload:WC_Subscriptions_Product');
        $wcsProductMock->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(5);

        $reflection = new \ReflectionClass(Subscription::class);
        $method = $reflection->getMethod('getRecurrenceModelFromProductId');
        $method->setAccessible(true);

        $result = $method->invoke(null, $productId);

        $this->assertEquals('subscription', $result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelFromProductIdReturnsStandingOrderWhenNoFixedDuration()
    {
        $productId = 126;

        // Mock WC_Subscriptions_Product::get_length to return 0 (indefinite)
        $wcsProductMock = Mockery::mock('overload:WC_Subscriptions_Product');
        $wcsProductMock->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(0);

        $reflection = new \ReflectionClass(Subscription::class);
        $method = $reflection->getMethod('getRecurrenceModelFromProductId');
        $method->setAccessible(true);

        $result = $method->invoke(null, $productId);

        $this->assertEquals('standing_order', $result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function canProcessSubscriptionsReturnsFalseWhenPluginNotPresent()
    {
        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(false);

        $reflection = new \ReflectionClass(Subscription::class);
        $method = $reflection->getMethod('canProcessSubscriptions');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function canProcessSubscriptionsReturnsFalseWhenNoSubscriptionInCart()
    {
        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('function_exists')
            ->with('wcs_cart_contains_renewal')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('wcs_cart_contains_renewal')
            ->andReturn(false);

        $wcsCartMock = Mockery::mock('alias:WC_Subscriptions_Cart');
        $wcsCartMock->shouldReceive('cart_contains_subscription')
            ->andReturn(false);

        $reflection = new \ReflectionClass(Subscription::class);
        $method = $reflection->getMethod('canProcessSubscriptions');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelReturnsNullWhenPluginNotPresent()
    {
        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(false);

        $result = Subscription::getRecurrenceModel();

        $this->assertNull($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelReturnsNullWhenNoSubscriptionInCart()
    {
        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('function_exists')
            ->with('wcs_cart_contains_renewal')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('wcs_cart_contains_renewal')
            ->andReturn(false);

        $wcsCartMock = Mockery::mock('alias:WC_Subscriptions_Cart');
        $wcsCartMock->shouldReceive('cart_contains_subscription')
            ->andReturn(false);

        $result = Subscription::getRecurrenceModel();

        $this->assertNull($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelReturnsSubscriptionWhenCartContainsFixedDurationProduct()
    {
        $productId = 127;
        $cartItem = [
            'product_id' => $productId,
            'quantity' => 1,
        ];

        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('function_exists')
            ->with('wcs_cart_contains_renewal')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('wcs_cart_contains_renewal')
            ->andReturn(false);

        $wcsCartMock = Mockery::mock('alias:WC_Subscriptions_Cart');
        $wcsCartMock->shouldReceive('cart_contains_subscription')
            ->andReturn(true);

        Brain\Monkey\Functions\when('WC()')
            ->returnArg(0);

        $wcsProductMock = Mockery::mock('overload:WC_Subscriptions_Product');
        $wcsProductMock->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(12); // 12 billing cycles

        $wcMock = Mockery::mock();
        $wcMock->cart = Mockery::mock();
        $wcMock->cart->cart_contents = [$cartItem];

        Brain\Monkey\Functions\when('WC()')
            ->return($wcMock);

        $result = Subscription::getRecurrenceModel();

        $this->assertEquals('subscription', $result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelReturnsStandingOrderWhenCartContainsIndefiniteProduct()
    {
        $productId = 128;
        $cartItem = [
            'product_id' => $productId,
            'quantity' => 1,
        ];

        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('function_exists')
            ->with('wcs_cart_contains_renewal')
            ->andReturn(true);

        Brain\Monkey\Functions\expect('wcs_cart_contains_renewal')
            ->andReturn(false);

        $wcsCartMock = Mockery::mock('alias:WC_Subscriptions_Cart');
        $wcsCartMock->shouldReceive('cart_contains_subscription')
            ->andReturn(true);

        $wcsProductMock = Mockery::mock('overload:WC_Subscriptions_Product');
        $wcsProductMock->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(0); // 0 = indefinite

        $wcMock = Mockery::mock();
        $wcMock->cart = Mockery::mock();
        $wcMock->cart->cart_contents = [$cartItem];

        Brain\Monkey\Functions\when('WC()')
            ->return($wcMock);

        $result = Subscription::getRecurrenceModel();

        $this->assertEquals('standing_order', $result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelFromOrderReturnsNullWhenPluginNotPresent()
    {
        $orderMock = Mockery::mock('WC_Order');

        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(false);

        $result = Subscription::getRecurrenceModelFromOrder($orderMock);

        $this->assertNull($result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelFromOrderReturnsSubscriptionWhenOrderContainsFixedDurationProduct()
    {
        $productId = 129;

        $itemMock = Mockery::mock();
        $itemMock->shouldReceive('get_product_id')
            ->andReturn($productId);

        $orderMock = Mockery::mock('WC_Order');
        $orderMock->shouldReceive('get_items')
            ->andReturn([$itemMock]);

        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        $wcsProductMock = Mockery::mock('overload:WC_Subscriptions_Product');
        $wcsProductMock->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(6); // 6 billing cycles

        $result = Subscription::getRecurrenceModelFromOrder($orderMock);

        $this->assertEquals('subscription', $result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelFromOrderReturnsStandingOrderWhenOrderContainsIndefiniteProduct()
    {
        $productId = 130;

        $itemMock = Mockery::mock();
        $itemMock->shouldReceive('get_product_id')
            ->andReturn($productId);

        $orderMock = Mockery::mock('WC_Order');
        $orderMock->shouldReceive('get_items')
            ->andReturn([$itemMock]);

        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        $wcsProductMock = Mockery::mock('overload:WC_Subscriptions_Product');
        $wcsProductMock->shouldReceive('get_length')
            ->with($productId)
            ->andReturn(0); // indefinite

        $result = Subscription::getRecurrenceModelFromOrder($orderMock);

        $this->assertEquals('standing_order', $result);
    }

    /**
     * @test
     * @group recurrence
     */
    public function getRecurrenceModelFromOrderReturnsNullWhenOrderHasNoItems()
    {
        $orderMock = Mockery::mock('WC_Order');
        $orderMock->shouldReceive('get_items')
            ->andReturn([]);

        Brain\Monkey\Functions\expect('class_exists')
            ->with('WC_Subscriptions')
            ->andReturn(true);

        $result = Subscription::getRecurrenceModelFromOrder($orderMock);

        $this->assertNull($result);
    }
}
