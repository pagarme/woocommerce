<?php

namespace Woocommerce\Pagarme\Tests\Block\Checkout;

use Brain;
use Mockery;
use WC_Cart;
use stdClass;
use WC_Order;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Block\Checkout\Gateway;
use Woocommerce\Pagarme\Model\Gateway as GatewayModel;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

class GatewayTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        global $wp;
        $wp = new stdClass;

        Brain\Monkey\setUp();
    }
    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
        Brain\Monkey\tearDown();
    }

    public function testCartTotalsWithoutOrderPayShouldReturnCartTotal()
    {
        $jsonMock = Mockery::mock(Json::class);
        $configMock = Mockery::mock(Config::class);
        $gatewayModelMock = Mockery::mock(GatewayModel::class);
        $data = [
            'config' => $configMock,
            'gateway' => $gatewayModelMock,
        ];

        global $wp;
        $wp->query_vars = [
            'order-pay' => 1
        ];

        $orderMock = Mockery::mock(WC_Order::class);
        $orderMock->shouldReceive('get_total')
            ->andReturn(10);

        Brain\Monkey\Functions\stubs([
            'wc_get_order' => $orderMock
        ]);

        $gatewayBlock = new Gateway($jsonMock, $data);
        $this->assertEquals(10, $gatewayBlock->getCartTotals());
    }

    public function testCartTotalsWithOrderPayShouldReturnOrderTotal()
    {
        $jsonMock = Mockery::mock(Json::class);

        $configMock = Mockery::mock(Config::class);
        $gatewayModelMock = Mockery::mock(GatewayModel::class);
        $data = [
            'config' => $configMock,
            'gateway' => $gatewayModelMock,
        ];

        global $wp;
        $wp->query_vars = [];

        $wcCheckoutMock = Mockery::mock(WC_Cart::class);
        $wcCheckoutMock->total = 20;
        $woocommerce = new stdClass();
        $woocommerce->cart = $wcCheckoutMock;
        Brain\Monkey\Functions\stubs([
            'WC' => $woocommerce,
        ]);

        $gatewayBlock = new Gateway($jsonMock, $data);
        $this->assertEquals(20, $gatewayBlock->getCartTotals());
    }
}
