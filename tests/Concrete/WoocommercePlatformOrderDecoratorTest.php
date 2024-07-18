<?php

namespace Woocommerce\Pagarme\Tests\Concrete;

use Mockery;
use WC_Order;
use PHPUnit\Framework\TestCase;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Marketplace\Aggregates\Split;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformOrderDecorator;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WoocommercePlatformOrderDecoratorTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetPaymentMethodCollectionWithTdsAuthenticatedCreditCardPaymentMethodShouldSetAuthenticationNode()
    {
        $paymentFactoryMock = Mockery::mock('overload:Pagarme\Core\Payment\Factories\PaymentFactory');

        $platformOrderDecorator = $this->returnBasicPlatformOrderDecorator();

        $customerIdMock = Mockery::mock(CustomerId::class);
        $customerIdMock->shouldReceive('getValue')
            ->andReturn(1);

        $customer = new Customer();
        $customerMock = Mockery::mock($customer);
        $customerMock->shouldReceive('getPagarmeId')
            ->andReturn($customerIdMock);
        $platformOrderDecorator->setCustomer($customerMock);

        $platformOrderMock = Mockery::mock(WC_Order::class);
        $platformOrderMock->shouldReceive('get_total')
            ->andReturn(10);
        $platformOrderMock->shouldReceive('get_total_tax')
            ->andReturn(0);

        $platformOrderDecorator->setPlatformOrder($platformOrderMock);


        $paymentFactoryMock->shouldReceive('createFromJson')
            ->withArgs(function ($data) {
                $formatedData = json_decode($data, true);

                $card = current($formatedData[AbstractCreditCardPayment::getBaseCode()]);

                return $card['authentication']['status'] === 'Y'
                    && $card['authentication']['threeDSecure']['transactionId'] === 'test-trans-id';
            })
            ->andReturn([]);

        $result = $platformOrderDecorator->getPaymentMethodCollection();

        $this->assertIsArray($result);
    }

    public function testHandleSplitOrderWithoutCallFilter()
    {
        $platformOrderDecorator = $this->returnBasicPlatformOrderDecorator();
        $this->assertNull($platformOrderDecorator->handleSplitOrder());
    }

    public function testHandleSplitOrderWithCallFilter()
    {
        require_once("vendor/wordpress/wordpress/src/wp-includes/plugin.php");
        $platformOrderDecorator = $this->returnBasicPlatformOrderDecorator();
        add_filter('pagarme_split_order', function($order, $paymentMethod){
            return [
                'sellers' => [
                    [
                        'marketplaceCommission' => 400,
                        'commission'            => 800,
                        'pagarmeId'             => "re_xxxxxxxxxxxxxxx"
                    ]
                ],
                'marketplace' => [
                    'totalCommission' => 400
                ]
            ];
        }, 10, 2);
        $splitReturn = $platformOrderDecorator->handleSplitOrder();
        $this->assertInstanceOf(Split::class, $splitReturn);
    }

    public function testHandleSplitOrderWithWrongCallFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        require_once("vendor/wordpress/wordpress/src/wp-includes/plugin.php");
        $platformOrderDecorator = $this->returnBasicPlatformOrderDecorator();
        add_filter('pagarme_split_order', function($order, $paymentMethod){
            return [
                'sellers' => [
                    [
                        'commission'            => 800,
                        'pagarmeId'             => "re_xxxxxxxxxxxxxxx"
                    ]
                ],
                'marketplace' => [
                    'totalCommission' => 400
                ]
            ];
        }, 10, 2);
        $platformOrderDecorator->handleSplitOrder();
    }
    

    private function returnBasicPlatformOrderDecorator()
    {
        Mockery::mock('overload:Pagarme\Core\Kernel\Services\LocalizationService');
        Mockery::mock('overload:Pagarme\Core\Kernel\Services\OrderService');
        Mockery::mock('overload:Pagarme\Core\Kernel\Services\OrderLogService');
        $paymentMock = Mockery::mock('overload:Woocommerce\Pagarme\Model\Payment');
        $paymentMock->shouldReceive('get_payment_data')
            ->andReturn([
                [
                    'payment_method' => 'credit_card',
                    'credit_card' => [
                        'installments' => 1,
                        'statement_descriptor' => '',
                        'capture' => null,
                        'card' => [
                            'billing_address' => [
                                'street' => 'test street',
                                'complement' => 'test',
                                'number' => '123',
                                'zip_code' => '1234567',
                                'neighborhood' => 'neighborhood',
                                'city' => 'city',
                                'state' => 'SP',
                                'country' => 'BR',
                            ],
                            'card_token' => 'token_test'
                        ]
                    ],
                    'amount' => 1000
                ]
            ]);

        $paymentMethodName = 'credit_card';

        $formData = [
            'payment_method' => $paymentMethodName,
            'brand' => 'visa',
            'installments' => 1,
            'authentication' => ['trans_status' => 'Y', 'tds_server_trans_id' => 'test-trans-id'],
            'pagarmetoken1' => 'token_test'
        ];

        return new WoocommercePlatformOrderDecorator($formData, 'credit_card');
    }

}
