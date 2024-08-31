<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain;
use Mockery;
use stdClass;
use WC_Order;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Controller\Orders;
use Woocommerce\Pagarme\Model\WooOrderRepository;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequestInterface;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequest;
use Woocommerce\Pagarme\Model\Payment\Data\Card;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use WC_Cart;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CheckoutTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $_POST = [];
        $_SERVER = [];

        Brain\Monkey\setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
        Brain\Monkey\tearDown();
    }

    public function testProcessWithTdsAuthenticatedCreditCardPaymentMethodShouldSetAuthenticationNode()
    {
        $gatewayMock = Mockery::mock(Gateway::class);
        $configMock = Mockery::mock(Config::class);
        $ordersMock = Mockery::mock(Orders::class);
        $wooOrderRepositoryMock = Mockery::mock(WooOrderRepository::class);

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $cardMock = Mockery::mock(Card::class);
        $cardMock->shouldReceive('getBrand')
            ->andReturn('visa');
        $cardMock->shouldReceive('getInstallment')
            ->andReturn(1);
        $cardMock->shouldReceive('getAuthentication')
            ->andReturn("{\"trans_status\":\"Y\",\"tds_server_trans_id\":\"test-trans-id\"}");
        $cardMock->shouldReceive('getToken')
            ->andReturn('token_test');
        $cardMock->shouldReceive('getOrderValue')
            ->andReturnFalse();
        $cardMock->shouldReceive('getSaveCard')
            ->andReturnFalse();
        $cardMock->shouldReceive('getWalletId')
            ->andReturnFalse();

        $paymentRequestMock = Mockery::mock(PaymentRequest::class);
        $paymentRequestMock->shouldReceive('getPaymentMethod')
            ->andReturn('credit_card');
        $paymentRequestMock->shouldReceive('getCards')
            ->andReturn([$cardMock]);
        $paymentRequestMock->shouldReceive('getData')
            ->andReturn([]);

        $_POST[PaymentRequestInterface::PAGARME_PAYMENT_REQUEST_KEY] = $paymentRequestMock;

        $subscriptionMock = Mockery::mock('alias:Woocommerce\Pagarme\Model\Subscription');
        $subscriptionMock->shouldReceive('getRecurrenceCycle')
            ->andReturnNull();

        $orderIdMock = Mockery::mock(OrderId::class);
        $orderIdMock->shouldReceive('getValue')
            ->andReturn(1);

        $order = new Order();
        $orderMock = Mockery::mock($order);
        $orderMock->shouldReceive('getPagarmeId')
            ->andReturn($orderIdMock);

        $orderMock->shouldReceive('getStatus')
            ->andReturn(OrderStatus::paid());

        $wcOrderMock = Mockery::mock(WC_Order::class);
        $wcOrderMock->shouldReceive('get_total')
            ->andReturn(10);
        $wcOrderMock->shouldReceive('get_id')
            ->andReturn(1);
        $wcOrderMock->shouldReceive('set_total')
            ->andReturnSelf();
        $wcOrderMock->shouldReceive('get_meta')
            ->andReturn("");
        $wcOrderMock->shouldReceive('update_meta_data')
            ->andReturnSelf();

        $ordersMock->shouldReceive('create_order')
            ->withArgs(function ($wcOrder, $paymentMethod, $fields) use ($wcOrderMock) {
                return $wcOrder === $wcOrderMock
                    && $paymentMethod === 'credit_card'
                    && $fields['authentication']['trans_status'] === 'Y'
                    && $fields['authentication']['tds_server_trans_id'] === 'test-trans-id';
            })
            ->andReturn($orderMock);

        $orderModelMock = Mockery::mock('overload:Woocommerce\Pagarme\Model\Order');
        $orderModelMock->shouldReceive('getTotalAmountByCharges')
            ->andReturn(10);
        $orderModelMock->shouldReceive('calculateInstallmentFee')
            ->andReturn(0);
        $orderModelMock->shouldReceive('update_by_pagarme_status')
            ->andReturnSelf();
        $orderModelMock->shouldReceive('getWcOrder')
            ->andReturn($wcOrderMock);
        $orderModelMock->shouldReceive('update_meta')
            ->andReturn([]);
        $wcCheckoutMock = Mockery::mock(WC_Cart::class);
        $wcCheckoutMock->shouldReceive('empty_cart')
            ->andReturnSelf();
        $woocommerce = new stdClass();
        $woocommerce->cart = $wcCheckoutMock;
        Brain\Monkey\Functions\stubs([
            'WC' => $woocommerce,
        ]);

        Brain\Monkey\Functions\stubs([
            'wp_strip_all_tags',
        ]);

        Brain\Monkey\Functions\expect('add_action')
            ->once();

        Brain\Monkey\Functions\expect('do_action')
            ->once();

        $checkout = new Checkout($gatewayMock, $configMock, $ordersMock, $wooOrderRepositoryMock);
        
        $this->assertTrue($checkout->process($wcOrderMock));
    }
}
