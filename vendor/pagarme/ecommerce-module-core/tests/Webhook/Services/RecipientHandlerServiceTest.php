<?php

namespace Pagarme\Core\Test\Webhook\Services;

use Mockery;
use PHPUnit\Framework\TestCase;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Webhook\ValueObjects\WebhookType;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
use Pagarme\Core\Webhook\Services\RecipientHandlerService;
use Pagarme\Core\Marketplace\Interfaces\RecipientInterface;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RecipientHandlerServiceTest extends TestCase
{
    public function testHandleShouldUpdateRecipientWithWebhookEntityStatus()
    {
        $webhookType = WebhookType::fromPostType('recipient.updated');
        $webhook = new Webhook();
        $webhook->setType($webhookType);
        $recipient = new Recipient();
        $recipient->setPagarmeId(new RecipientId('rp_xxxxxxxxxxxxxxxx'));
        $recipient->setStatus(RecipientInterface::ACTIVE);
        $webhook->setEntity($recipient);

        $foundedRecipient = new Recipient();
        $recipientServiceMock = Mockery::mock('overload:Pagarme\Core\Marketplace\Services\RecipientService');
        $recipientServiceMock->shouldReceive('findSavedByPagarmeId')->andReturn($foundedRecipient);
        $recipientServiceMock->shouldReceive('saveRecipient')->withArgs(function ($updatedRecipient) {
            return $updatedRecipient->getStatus() === RecipientInterface::ACTIVE;
        });

        $recipientHandlerService = new RecipientHandlerService();
        $result = $recipientHandlerService->handle($webhook);

        $this->assertSame($result['message'], RecipientHandlerService::RECIPIENT_UPDATED_MESSAGE);
        $this->assertSame($result['code'], RecipientHandlerService::STATUS_CODE);
    }

    public function testHandleShouldNotFoundRecipient()
    {
        $webhookType = WebhookType::fromPostType('recipient.updated');
        $webhook = new Webhook();
        $webhook->setType($webhookType);
        $recipient = new Recipient();
        $recipient->setPagarmeId(new RecipientId('rp_xxxxxxxxxxxxxxxx'));
        $recipient->setStatus(RecipientInterface::ACTIVE);
        $webhook->setEntity($recipient);

        $recipientServiceMock = Mockery::mock('overload:Pagarme\Core\Marketplace\Services\RecipientService');
        $recipientServiceMock->shouldReceive('findSavedByPagarmeId')->andReturnNull();

        $logServiceMock = Mockery::mock('overload:Pagarme\Core\Kernel\Services\LogService');
        $logServiceMock->shouldReceive('info')->andReturnSelf();

        $recipientHandlerService = new RecipientHandlerService();
        $result = $recipientHandlerService->handle($webhook);

        $this->assertSame($result['message'], sprintf(RecipientHandlerService::RECIPIENT_NOT_FOUNDED_MESSAGE, $recipient->getPagarmeId()->getValue()));
        $this->assertSame($result['code'], RecipientHandlerService::STATUS_CODE);
    }

    public function testHandleShouldNotFoundHandler()
    {
        $webhookName = 'recipient.created';
        $webhookType = WebhookType::fromPostType($webhookName);
        $webhook = new Webhook();
        $webhook->setType($webhookType);

        $logServiceMock = Mockery::mock('overload:Pagarme\Core\Kernel\Services\LogService');
        $logServiceMock->shouldReceive('info')->andReturnSelf();

        $recipientHandlerService = new RecipientHandlerService();
        $result = $recipientHandlerService->handle($webhook);

        $this->assertSame($result['message'], sprintf(RecipientHandlerService::WEBHOOK_NOT_IMPLEMENTED_MESSAGE, $webhookName));
        $this->assertSame($result['code'], RecipientHandlerService::STATUS_CODE);
    }
}
