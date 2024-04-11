<?php

namespace Woocommerce\Pagarme\Tests\Service;

use Mockery;
use Pagarme\Core\Middle\Model\Account\PaymentEnum;
use PagarmeCoreApiLib\Models\GetAccountResponse;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;
use Woocommerce\Pagarme\Service\AccountService;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AccountServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetAccountShouldCallAccountServiceAndValidateAccountSettings()
    {
        $accountId = 1;
        $utilsMock = Mockery::mock('alias:Woocommerce\Pagarme\Helper\Utils');
        $siteUrl = 'https://woocommerce.test';
        $utilsMock->shouldReceive('get_site_url')
            ->andReturn($siteUrl);
        $coreAuthMock = Mockery::mock(CoreAuth::class);
        $configMock = Mockery::mock(Config::class);
        $configMock->shouldReceive('getIsSandboxMode')
            ->andReturnTrue();

        $availablePayments = [
            PaymentEnum::PIX => true,
            PaymentEnum::BILLET => true,
            PaymentEnum::CREDIT_CARD => true,
            PaymentEnum::VOUCHER => true
        ];
        $configMock->shouldReceive('availablePaymentMethods')
            ->andReturn($availablePayments);

        $wcOrderMock = Mockery::mock('WC_Order');

        $accountService = new AccountService($coreAuthMock, $configMock, $wcOrderMock);

        $getAccountResponseMock = Mockery::mock(GetAccountResponse::class);
        $accountProxyMock = Mockery::mock('overload:Pagarme\Core\Middle\Proxy\AccountProxy');

        $accountProxyMock->shouldReceive('getAccount')
            ->with($accountId)
            ->andReturn($getAccountResponseMock);

        $accountMock = Mockery::mock('alias:Pagarme\Core\Middle\Model\Account');
        $accountMock->shouldReceive('createFromSdk')
            ->andReturnSelf();
        $accountMock->shouldReceive('validate')
            ->withArgs(function ($storeConfig) use ($availablePayments, $siteUrl) {
                return $storeConfig->isSandbox()
                    && current($storeConfig->getStoreUrls()) === $siteUrl
                    && $storeConfig->getEnabledPaymentMethods() === $availablePayments;
            })
            ->andReturnSelf();

        $account = $accountService->getAccount(1);
        $this->assertSame($account, $accountMock);
    }
}
