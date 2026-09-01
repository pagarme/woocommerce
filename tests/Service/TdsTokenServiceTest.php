<?php

namespace Woocommerce\Pagarme\Tests\Service;

use Brain;
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
    * @param int $statusCode
    * @param array $body
    * @return void
    */
   private function stubNxResponse($statusCode, array $body)
   {
      Brain\Monkey\Functions\stubs([
         'wp_remote_post' => ['response' => ['code' => $statusCode]],
         'is_wp_error' => false,
         'wp_remote_retrieve_response_code' => $statusCode,
         'wp_remote_retrieve_body' => json_encode($body),
      ]);
   }

   /**
    * @param string $token
    * @param string $environment
    * @return void
    */
   private function stubLegacyProxy($token, $environment)
   {
      $getTdsTokenResponseMock = Mockery::mock(GetTdsTokenResponse::class);
      $getTdsTokenResponseMock->tdsToken = $token;

      $tdsTokenProxyMock = Mockery::mock('overload:Pagarme\Core\Middle\Proxy\TdsTokenProxy');
      $tdsTokenProxyMock->shouldReceive('getTdsToken')
         ->with($environment, 'acc_test')
         ->andReturn($getTdsTokenResponseMock);
   }

   public function testShouldGetTdsTokenFromNxWithEngineNx()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnFalse();
      $configMock->shouldReceive('getSecretKey')->andReturn('test_secret_key');

      $this->stubNxResponse(200, ['tds_token' => 'nx_token_123']);

      $tdsTokenService = new TdsTokenService($configMock);

      $this->assertSame(
         ['token' => 'nx_token_123', 'engine' => TdsTokenService::ENGINE_NX],
         $tdsTokenService->getTdsToken('acc_test')
      );
   }

   public function testShouldFallbackToLegacyTokenWhenNxReturnsError()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnFalse();
      $configMock->shouldReceive('getSecretKey')->andReturn('test_secret_key');

      $this->stubNxResponse(500, []);
      $this->stubLegacyProxy('legacy_token_456', 'live');

      $tdsTokenService = new TdsTokenService($configMock);

      $this->assertSame(
         ['token' => 'legacy_token_456', 'engine' => TdsTokenService::ENGINE_LEGACY],
         $tdsTokenService->getTdsToken('acc_test')
      );
   }

   public function testShouldFallbackToLegacyTokenWhenNxResponseHasNoToken()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnFalse();
      $configMock->shouldReceive('getSecretKey')->andReturn('test_secret_key');

      $this->stubNxResponse(200, ['unexpected_key' => 'value']);
      $this->stubLegacyProxy('legacy_token_456', 'live');

      $tdsTokenService = new TdsTokenService($configMock);

      $this->assertSame(
         ['token' => 'legacy_token_456', 'engine' => TdsTokenService::ENGINE_LEGACY],
         $tdsTokenService->getTdsToken('acc_test')
      );
   }

   public function testShouldUseTestEnvironmentOnLegacyFallbackWhenSandbox()
   {
      Mockery::mock('overload:Woocommerce\Pagarme\Model\CoreAuth');
      $configMock = Mockery::mock(Config::class);
      $configMock->shouldReceive('getIsSandboxMode')->andReturnTrue();
      $configMock->shouldReceive('getSecretKey')->andReturn('test_secret_key');

      $this->stubNxResponse(500, []);
      $this->stubLegacyProxy('tokentds', 'test');

      $tdsTokenService = new TdsTokenService($configMock);

      $this->assertSame(
         ['token' => 'tokentds', 'engine' => TdsTokenService::ENGINE_LEGACY],
         $tdsTokenService->getTdsToken('acc_test')
      );
   }
}
