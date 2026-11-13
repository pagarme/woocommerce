<?php

namespace Woocommerce\Pagarme\Tests\Model;

use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Model\ApiEndpoints;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ApiEndpointsTest extends TestCase
{
    public function testDefaultsPointToProductionWhenNoOverrideIsSet()
    {
        $this->assertSame('https://api.pagar.me', ApiEndpoints::getApiUrl());
        $this->assertSame('https://hubapi.pagar.me', ApiEndpoints::getHubApiUrl());
        $this->assertSame('https://cdn.mundipagg.com', ApiEndpoints::getCdnUrl());
    }

    public function testFrontendUrlsAreDerivedFromTheSameSource()
    {
        $this->assertSame([
            'tokens'        => 'https://api.pagar.me/core/v5/tokens',
            'bin'           => 'https://api.pagar.me/bin/v1/',
            'cardBrandsCdn' => 'https://cdn.mundipagg.com/assets/images/logos/brands/png/',
        ], ApiEndpoints::getFrontendUrls());
    }

    public function testEnvironmentVariableOverridesDefault()
    {
        putenv('PAGARME_API_URL=https://api.staging.test/');

        $this->assertSame('https://api.staging.test', ApiEndpoints::getApiUrl());
        $this->assertSame('https://api.staging.test/core/v5/tokens', ApiEndpoints::getTokensUrl());

        putenv('PAGARME_API_URL');
    }

    public function testConstantOverridesDefaultWhenEnvironmentVariableIsNotSet()
    {
        if (!defined('PAGARME_HUB_API_URL')) {
            define('PAGARME_HUB_API_URL', 'https://hub.staging.test');
        }

        $this->assertSame('https://hub.staging.test', ApiEndpoints::getHubApiUrl());
    }
}
