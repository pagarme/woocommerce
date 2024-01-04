<?php

namespace Woocommerce\Pagarme\Tests\Service;

use Mockery;
use Pagarme\Core\Middle\Model\Account\PaymentEnum;
use Pagarme\Core\Middle\Proxy\AccountProxy;
use PagarmeCoreApiLib\Models\GetAccountResponse;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;
use Woocommerce\Pagarme\Service\AccountService;
use Woocommerce\Pagarme\Tests\BaseTest;

class AccountServiceTest extends BaseTest
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetAccountShouldCallAccountServiceAndValidateAccountSettings()
    {
        $accountId = 1;
        $utilsMock = Mockery::mock('alias:Woocommerce\Pagarme\Helper\Utils');
        $utilsMock->shouldReceive('get_site_url')
            ->andReturn('https://woocommerce.test');
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

        $accountService = Mockery::mock(AccountService::class, [$coreAuthMock, $configMock, $wcOrderMock])
            ->makePartial();
        $accountService->shouldAllowMockingProtectedMethods();

        $getAccountResponseMock = Mockery::mock(GetAccountResponse::class);
        $accountProxyMock = Mockery::mock(AccountProxy::class);

        $accountProxyMock->shouldReceive('getAccount')
            ->with($accountId)
            ->andReturn($getAccountResponseMock);

        $accountService->shouldReceive('getAccountProxy')
            ->andReturn($accountProxyMock);

        $accountMock = Mockery::mock('alias:Pagarme\Core\Middle\Model\Account');
        $accountMock->shouldReceive('createFromSdk')
            ->andReturnSelf();
        $accountMock->shouldReceive('validate')
            ->withArgs(function ($storeConfig) use ($availablePayments) {
                return $storeConfig->isSandbox()
                    && current($storeConfig->getStoreUrls()) === 'https://woocommerce.test'
                    && $storeConfig->getEnabledPaymentMethods() === $availablePayments;
            })
            ->andReturnSelf();

        $account = $accountService->getAccount(1);
        $this->assertSame($account, $accountMock);
    }
}
