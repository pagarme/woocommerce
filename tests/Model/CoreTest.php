<?php

namespace Woocommerce\Pagarme\Tests\Model;

use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Core;
use Brain;
use Mockery;

class CoreTest extends TestCase
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
    public function testGetWebhookWithoutCustomUrlAndWithPath()
    {
        $domain = "https://mycustomdomain.test";
        $webhookLink = $domain . "/wc-api/pagarme-webhook/";
        
        $utils = Mockery::mock("alias:Woocommerce\Pagarme\Helper\Utils");
        $utils->shouldReceive('get_site_url')->andReturn($domain);
        $utils->shouldReceive('add_prefix')->andReturn('pagarme-webhook');
        
        $getUrl = Core::get_webhook_url();

        $this->assertEquals($webhookLink, $getUrl);
    }

    public function testGetWebhookWithoutCustomUrlWithPath()
    {
        $domain = "https://mycustomdomain.test";
        $domainWithPath = "https://mycustomdomain.test/app/customPath";
        $webhookLink = $domain . "/wc-api/pagarme-webhook/";
        
        $utils = Mockery::mock("alias:Woocommerce\Pagarme\Helper\Utils");
        $utils->shouldReceive('get_site_url')->andReturn($domainWithPath);
        $utils->shouldReceive('add_prefix')->andReturn('pagarme-webhook');
        
        $getUrl = Core::get_webhook_url();

        $this->assertEquals($webhookLink, $getUrl);
    }
    public function testGetWebhookWithoutCustomUrl()
    {
        $customDomain = "https://mycustomdomain.test";
        $webhookLink = $customDomain . "/wc-api/pagarme-webhook/";
        
        $utils = Mockery::mock("alias:Woocommerce\Pagarme\Helper\Utils");
        $utils->shouldReceive('get_site_url')->andReturn($customDomain);
        $utils->shouldReceive('add_prefix')->andReturn('pagarme-webhook');
        
        $getUrl = Core::get_webhook_url($customDomain);

        $this->assertEquals($webhookLink, $getUrl);
    }
}
