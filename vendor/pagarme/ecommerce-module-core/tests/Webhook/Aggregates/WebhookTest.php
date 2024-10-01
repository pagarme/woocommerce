<?php

namespace Pagarme\Core\Test\Webhook\Aggregates;

use PHPUnit\Framework\TestCase;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Webhook\ValueObjects\WebhookId;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Webhook\ValueObjects\WebhookType;

class WebhookIdTests extends TestCase
{
    public function testWebHookObjectKernel()
    {
        $webhook = new Webhook();
        $webhook->setId(1);
        $webhook->setPagarmeId(new WebhookId('hook_xxxxxxxxxxxxxxxx'));
        $webhook->setType(WebhookType::fromPostType('charge.paid'));
        $webhook->setEntity(new Charge());
        $webhook->setComponent([]);

        $this->assertEquals(1, $webhook->getId());
        $this->assertEquals('hook_xxxxxxxxxxxxxxxx', $webhook->getPagarmeId()->getValue());
        $this->assertEquals('charge', $webhook->getType()->getEntityType());
        $this->assertEquals('paid', $webhook->getType()->getAction());
        $this->assertEquals('Kernel', $webhook->getComponent());
        $this->assertInstanceOf(Charge::class, $webhook->getEntity());
    }

    public function testWebHookObjectRecurrence()
    {
        $webhook = new Webhook();
        $webhook->setId(1);
        $webhook->setPagarmeId(new WebhookId('hook_xxxxxxxxxxxxxxxx'));
        $webhook->setType(WebhookType::fromPostType('subscription.create'));
        $webhook->setEntity(new Charge());
        $webhook->setComponent(['invoice']);

        $this->assertEquals(1, $webhook->getId());
        $this->assertEquals('hook_xxxxxxxxxxxxxxxx', $webhook->getPagarmeId()->getValue());
        $this->assertEquals('subscription', $webhook->getType()->getEntityType());
        $this->assertEquals('create', $webhook->getType()->getAction());
        $this->assertEquals('Recurrence', $webhook->getComponent());
    }

    public function testWebHookObjectMarketplace()
    {
        $webhook = new Webhook();
        $webhook->setId(1);
        $webhook->setPagarmeId(new WebhookId('hook_xxxxxxxxxxxxxxxx'));
        $webhook->setType(WebhookType::fromPostType('recipient.updated'));
        $webhook->setEntity(new Recipient());
        $webhook->setComponent([]);

        $this->assertEquals(1, $webhook->getId());
        $this->assertEquals('hook_xxxxxxxxxxxxxxxx', $webhook->getPagarmeId()->getValue());
        $this->assertEquals('recipient', $webhook->getType()->getEntityType());
        $this->assertEquals('updated', $webhook->getType()->getAction());
        $this->assertEquals('Marketplace', $webhook->getComponent());
    }
}
