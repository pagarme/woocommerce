<?php

namespace Woocommerce\Pagarme\Tests\Service;

use Mockery;
use PagarmeCoreApiLib\Models\GetTdsTokenResponse;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Service\TdsTokenService;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class TdsTokenServiceTest extends TestCase
{
   public function tearDown(): void
   {
      Mockery::close();
   }

   public function testShoudGetTdsTokenWithLiveEndpoint()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')
         ->andReturnFalse();

      $tdsTokenService = new TdsTokenService($configMock);

      $token = 'tokentds';
      $getTdsTokenResponseMock = Mockery::mock(GetTdsTokenResponse::class);
      $getTdsTokenResponseMock->tdsToken = $token;

      $tdsTokenProxyMock = Mockery::mock('overload:Pagarme\Core\Middle\Proxy\TdsTokenProxy');


      $accountId = 'acc_test';
      $tdsTokenProxyMock->shouldReceive('getTdsToken')
         ->with('live', $accountId)
         ->andReturn($getTdsTokenResponseMock);

      $this->assertSame($token, $tdsTokenService->getTdsToken($accountId));
   }

   public function testShoudGetTdsTokenWithTestEnviroment()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')
         ->andReturnTrue();

      $tdsTokenService = new TdsTokenService($configMock);

      $token = 'tokentds';
      $getTdsTokenResponseMock = Mockery::mock(GetTdsTokenResponse::class);
      $getTdsTokenResponseMock->tdsToken = $token;

      $tdsTokenProxyMock = Mockery::mock('overload:Pagarme\Core\Middle\Proxy\TdsTokenProxy');


      $accountId = 'acc_test';
      $tdsTokenProxyMock->shouldReceive('getTdsToken')
         ->with('test', $accountId)
         ->andReturn($getTdsTokenResponseMock);

      $this->assertSame($token, $tdsTokenService->getTdsToken($accountId));
   }
}
