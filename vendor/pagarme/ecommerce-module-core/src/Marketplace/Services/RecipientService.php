<?php

namespace Pagarme\Core\Marketplace\Services;

use Pagarme\Core\Kernel\Aggregates\Configuration as ConfigurationAggregate;
use PagarmeCoreApiLib\APIException;
use PagarmeCoreApiLib\Models\UpdateRecipientBankAccountRequest;
use PagarmeCoreApiLib\Models\UpdateRecipientRequest;
use PagarmeCoreApiLib\Models\UpdateTransferSettingsRequest;
use PagarmeCoreApiLib\PagarmeCoreApiClient;
use PagarmeCoreApiLib\Configuration;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\Id\RecipientId;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;

class RecipientService
{
    /**
     * @var LogService
     */
    protected $logService;

    /**
     * @var RecipientFactory
     */
    protected $recipientFactory;

    /**
     * @var RecipientRepository
     */
    protected $recipientRepository;

    /**
     * @var ConfigurationAggregate
     */
    protected $config;

    /**
     * @var LocalizationService
     */
    protected $i18n;

    /**
     * @var PagarmeCoreApiClient
     */
    protected $pagarmeCoreApi;

    public function __construct()
    {
        AbstractModuleCoreSetup::bootstrap();
        $secretKey = null;
        $this->config = AbstractModuleCoreSetup::getModuleConfiguration();

        if ($this->config->getSecretKey() != null) {
            $secretKey = $this->config->getSecretKey()->getValue();
        }

        $password = '';
        Configuration::$basicAuthPassword = '';

        $this->pagarmeCoreApi = new PagarmeCoreApiClient($secretKey, $password);
        $this->logService = new LogService('RecipientService', true);
        $this->recipientRepository = new RecipientRepository();
        $this->recipientFactory = new RecipientFactory();
        $this->i18n = new LocalizationService();
    }

    public function saveFormRecipient($formData)
    {
        $recipientFactory = $this->recipientFactory;

        $recipient = $recipientFactory->createFromPostData($formData);

        $recipientId = $recipient->getPagarmeId();

        if (!$recipientId && empty($formData['existing_recipient'])) {
            $result = $this->createRecipientAtPagarme($recipient);
            $recipientId = $result->id;
        }

        $recipient->setPagarmeId(new RecipientId($recipientId));

        return $this->saveRecipient($recipient);
    }

    public function createRecipientAtPagarme(Recipient $recipient)
    {
        $createRecipientRequest = $recipient->convertToSdkRequest();
        $recipientController = $this->pagarmeCoreApi->getRecipients();

        try {
            $logService = $this->logService;
            $logService->info(
                'Create recipient request: ' .
                    json_encode($createRecipientRequest, JSON_PRETTY_PRINT)
            );

            $result = $recipientController->createRecipient(
                $createRecipientRequest
            );

            $logService->info(
                'Create recipient response: ' .
                    json_encode($result, JSON_PRETTY_PRINT)
            );

            return $result;
        } catch (\Exception $exception) {
            $logService->exception($exception);
            throw new \Exception(__("Can't create recipient. Please review the information and try again."));
        }
    }

    public function updateRecipientAtPagarme(Recipient $recipient)
    {
        $recipient = $this->recipientRepository->attachDocumentFromDb($recipient);

        /**
         * @var UpdateRecipientRequest $updateRecipientRequest
         * @var UpdateTransferSettingsRequest $updateTransferSettingsRequest
         * @var UpdateRecipientBankAccountRequest $updateBankAccountRequest
         */
        list($updateRecipientRequest, $updateBankAccountRequest, $updateTransferSettingsRequest) = $recipient->convertToSdkRequest(true);
        $recipientController = $this->pagarmeCoreApi->getRecipients();

        $recipientPrevious = $this->attachBankAccount($recipient);

        try {
            $logService = $this->logService;
            //Update Recipient
            $logService->info(
                'Update recipient request: ' .
                    json_encode($updateRecipientRequest, JSON_PRETTY_PRINT)
            );

            $result = $recipientController->updateRecipient(
                $recipient->getPagarmeId(),
                $updateRecipientRequest
            );

            $logService->info(
                'Update recipient response: ' .
                    json_encode($result, JSON_PRETTY_PRINT)
            );

            //Update Default Bank Account
            if (!$recipientPrevious->bankAccountEquals($updateBankAccountRequest)) {
                $logService->info(
                    'Update bank account request: ' .
                        json_encode($updateBankAccountRequest, JSON_PRETTY_PRINT)
                );

                $result = $recipientController->updateRecipientDefaultBankAccount(
                    $recipient->getPagarmeId(),
                    $updateBankAccountRequest
                );

                $logService->info(
                    'Update bank account response: ' .
                        json_encode($result, JSON_PRETTY_PRINT)
                );
            }

            //Update Transfer Settings
            $logService->info(
                'Update transfer settings request: ' .
                    json_encode($updateBankAccountRequest, JSON_PRETTY_PRINT)
            );

            $result = $recipientController->updateRecipientTransferSettings(
                $recipient->getPagarmeId(),
                $updateTransferSettingsRequest
            );

            $logService->info(
                'Update transfer settings response: ' .
                    json_encode($result, JSON_PRETTY_PRINT)
            );

            return $result;
        } catch (\Exception $exception) {
            $logService->exception($exception);
            throw new \Exception(__("Can't update recipient. Please review the information and try again."));
        }
    }

    public function saveRecipient(Recipient $recipient)
    {
        $action = !!$recipient->getId() ? ['Editing a', 'edited'] : ['Creating new', 'created'];
        $this->logService->info("{$action[0]} recipient at platform");
        $this->recipientRepository->save($recipient);
        $this->logService->info("Recipient {$action[1]}: " . $recipient->getId());

        return $recipient;
    }

    /**
     * @param $sellerId
     * @throws CouldNotSaveException
     */
    public function findRecipient($sellerId)
    {
        $recipient = $this->recipientRepository->findBySellerId($sellerId);

        if (empty($recipient)) {
            $this->logService->info(
                __("The seller does not have a registered recipient.")
            );

            $message = $this->i18n->getDashboard(
                "Payment could not be made. " .
                    "Please contact the store administrator."
            );

            throw new \Exception($message);
        }

        return $recipient;
    }

    public function findById(int $recipientId)
    {
        return $this->recipientRepository->find($recipientId);
    }

    public function attachBankAccount(Recipient $recipient)
    {
        try {
            $bankAccount = $this->pagarmeCoreApi->getRecipients()->getRecipient($recipient->getPagarmeId())->defaultBankAccount;
        } catch (APIException $e) {
            throw $e;
        }
        return $this->recipientRepository->attachBankAccount($recipient, $bankAccount);
    }

    public function attachTransferSettings(Recipient $recipient)
    {
        try {
            $transferSettings = $this->pagarmeCoreApi->getRecipients()->getRecipient($recipient->getPagarmeId())->transferSettings;
        } catch (APIException $e) {
            throw $e;
        }
        return $this->recipientRepository->attachTransferSettings($recipient, $transferSettings);
    }

    public function findByPagarmeId($pagarmeId)
    {
        $recipientController = $this->pagarmeCoreApi->getRecipients();
        try {
            $logService = $this->logService;
            return $recipientController->getRecipient($pagarmeId);
        } catch (APIException $e) {
            $logService->exception($e);
            throw new \Exception(__("Can't get recipient. Please review the information and try again."));
        }
    }

    public function delete($id)
    {
        $recipient = $this->recipientRepository->find($id);

        if (empty($recipient)) {
            throw new \Exception("Recipient not found - ID : {$id} ");
        }

        return $this->recipientRepository->delete($recipient);
    }
}
