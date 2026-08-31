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

   public function testShouldGetTdsTokenFromNxWithLiveEndpoint()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnFalse();
      $configMock->shouldReceive('getSecretKey')->andReturn('test_secret_key');

      $tdsTokenService = new TdsTokenService($configMock);

      $nxToken = 'nx_token_123';
      Mockery::mock('overload:Pagarme\Core\Middle\Client', [
         'BASE_URI' => 'https://hubapi.pagar.me/'
      ]);

      $wpResponseMock = [
         'response' => ['code' => 200],
         'body' => json_encode(['tds_token' => $nxToken])
      ];

      Mockery::mock('overload:wp_remote_get', $wpResponseMock);

      $accountId = 'acc_test';
      $this->assertSame($nxToken, $tdsTokenService->getTdsToken($accountId));
   }

   public function testShouldFallbackToLegacyTokenWhenNxFails()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnFalse();
      $configMock->shouldReceive('getSecretKey')->andReturn('test_secret_key');

      $tdsTokenService = new TdsTokenService($configMock);

      $legacyToken = 'legacy_token_456';
      $getTdsTokenResponseMock = Mockery::mock(GetTdsTokenResponse::class);
      $getTdsTokenResponseMock->tdsToken = $legacyToken;

      $tdsTokenProxyMock = Mockery::mock('overload:Pagarme\Core\Middle\Proxy\TdsTokenProxy');
      $tdsTokenProxyMock->shouldReceive('getTdsToken')
         ->with('live', 'acc_test')
         ->andReturn($getTdsTokenResponseMock);

      $accountId = 'acc_test';
      $this->assertSame($legacyToken, $tdsTokenService->getTdsToken($accountId));
   }

   public function testShoudGetTdsTokenWithTestEnviroment()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnTrue();

      $tdsTokenService = new TdsTokenService($configMock);

      $token = 'tokentds';
      $getTdsTokenResponseMock = Mockery::mock(GetTdsTokenResponse::class);
      $getTdsTokenResponseMock->tdsToken = $token;

      $tdsTokenProxyMock = Mockery::mock('overload:Pagarme\Core\Middle\Proxy\TdsTokenProxy');
      $tdsTokenProxyMock->shouldReceive('getTdsToken')
         ->with('test', 'acc_test')
         ->andReturn($getTdsTokenResponseMock);

      $this->assertSame($token, $tdsTokenService->getTdsToken('acc_test'));
   }
}
