<?php

namespace Woocommerce\Pagarme\Tests\Model\Payment;

use Mockery;
use stdClass;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Payment\CreditCard;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CreditCardTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        global $wp;
        $wp = new stdClass;
    }
    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function testGetConfigDataProviderWithOrderPayShouldDisableJavascriptTdsEnabledConfig()
    {
        $creditCardModel = new CreditCard();

        global $wp;
        $wp->query_vars = [
            'order-pay' => 1
        ];

        $result = $creditCardModel->getConfigDataProvider();

        $this->assertFalse($result['tdsEnabled']);
    }

    public function testGetConfigDataProviderWithoutOrderPayShouldNotDisableJavascriptTdsEnabledConfig()
    {
        $creditCardModel = new CreditCard();

        $configMock = Mockery::mock('overload:Woocommerce\Pagarme\Model\Config');
        $configMock->shouldReceive('isTdsEnabled')
            ->andReturnTrue();
        $configMock->shouldReceive('getTdsMinAmount')
            ->andReturn(10);

        $result = $creditCardModel->getConfigDataProvider();
        
        $this->assertTrue($result['tdsEnabled']);
    }
}