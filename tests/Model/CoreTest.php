<?php

namespace Woocommerce\Pagarme\Tests\Model;

use Brain;
use Mockery;
use PHPUnit\Framework\TestCase;
use Woocommerce\Pagarme\Core;

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

    public function testGetWebhookUrlWithoutCustomUrlWithoutSubfolder()
    {
        $wpHomeAddress = "https://domain.test/";
        $apiPath = "wc-api/";
        $webhookName = "pagarme-webhook";
        $webhookLink = $wpHomeAddress . $apiPath . $webhookName;

        $utils = Mockery::mock("alias:Woocommerce\Pagarme\Helper\Utils");
        $utils->shouldReceive('add_prefix')->with('-webhook')->andReturn($webhookName);

        $wooCommerce = Mockery::mock("overload:WooCommerce");
        $wooCommerce->shouldReceive('api_request_url')->with($webhookName)->andReturn($webhookLink);

        $getWebhookUrl = Core::getWebhookUrl();

        $this->assertEquals($webhookLink, $getWebhookUrl);
    }

    public function testGetWebhookUrlWithoutCustomUrlWithSubfolder()
    {
        $wpHomeAddress = "https://domain.test/subfolder/";
        $apiPath = "wc-api/";
        $webhookName = "pagarme-webhook";
        $webhookLink = $wpHomeAddress . $apiPath . $webhookName;

        $utils = Mockery::mock("alias:Woocommerce\Pagarme\Helper\Utils");
        $utils->shouldReceive('add_prefix')->with('-webhook')->andReturn($webhookName);

        $wooCommerce = Mockery::mock("overload:WooCommerce");
        $wooCommerce->shouldReceive('api_request_url')->with($webhookName)->andReturn($webhookLink);

        $getWebhookUrl = Core::getWebhookUrl();

        $this->assertEquals($webhookLink, $getWebhookUrl);
    }
}
