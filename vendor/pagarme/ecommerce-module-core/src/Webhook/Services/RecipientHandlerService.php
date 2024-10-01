<?php
namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Webhook\Exceptions\WebhookHandlerNotFoundException;
use Pagarme\Core\Marketplace\Services\RecipientService;
use Pagarme\Core\Marketplace\Aggregates\Recipient;

class RecipientHandlerService
{
    CONST STATUS_CODE = 200;
    
    const RECIPIENT_UPDATED_MESSAGE = 'Recipient updated';

    const RECIPIENT_NOT_FOUNDED_MESSAGE = 'Recipient with id %s not founded in the platform';

    const WEBHOOK_NOT_IMPLEMENTED_MESSAGE = 'Webhook %s not implemented';

    /**
     * @param Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws WebhookHandlerNotFoundException
     */
    public function handle(Webhook $webhook)
    {
        $handler = $this->getActionHandle($webhook->getType()->getAction());

        if (method_exists($this, $handler)) {
            return $this->$handler($webhook);
        }

        $type = "{$webhook->getType()->getEntityType()}.{$webhook->getType()->getAction()}";
        $message = sprintf(static::WEBHOOK_NOT_IMPLEMENTED_MESSAGE, $type);
        $this->getLogService()->info($message);

        return [
            "message" => $message,
            "code" => static::STATUS_CODE
        ];
    }

    protected function handleUpdated(Webhook $webhook)
    {
        $recipientService = new RecipientService();
        /** @var Recipient $recipientEntity */
        $recipientEntity = $webhook->getEntity();
        $foundedRecipent = $recipientService->findSavedByPagarmeId($recipientEntity->getPagarmeId());
        if (empty($foundedRecipent)) {
            $message = sprintf(static::RECIPIENT_NOT_FOUNDED_MESSAGE, $recipientEntity->getPagarmeId());
            $this->getLogService()->info($message);
            return [
                "message" => $message,
                "code" => static::STATUS_CODE
            ];
        }

        $foundedRecipent->setStatus($recipientEntity->getStatus());

        $recipientService->saveRecipient($foundedRecipent);

        return [
            "message" => static::RECIPIENT_UPDATED_MESSAGE,
            "code" => static::STATUS_CODE
        ];
    }

    protected function getActionHandle($action)
    {
        $baseActions = explode('_', $action ?? '');
        $action = '';
        foreach ($baseActions as $baseAction) {
            $action .= ucfirst($baseAction);
        }

        return 'handle' . $action;
    }

    protected function getLogService()
    {
        return new LogService('Webhook', true);
    }
}