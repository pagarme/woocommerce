<?php

namespace Pagarme\Core\Hub\Services;

use Pagarme\Core\Hub\Aggregates\InstallToken;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Hub\Factories\HubCommandFactory;
use Pagarme\Core\Hub\Factories\InstallTokenFactory;
use Pagarme\Core\Hub\Repositories\InstallTokenRepository;
use Pagarme\Core\Hub\ValueObjects\HubInstallToken;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Unirest\Request;

final class HubIntegrationService
{
    private $logService;

    public function __construct()
    {
        $this->logService = new LogService('Hub', true);
    }

    /**
     *
     * @param  $installSeed
     * @return \Pagarme\Core\Hub\ValueObjects\HubInstallToken
     */
    public function startHubIntegration($installSeed)
    {
        $tokenRepo = new InstallTokenRepository();

        $notExpiredTokens = $tokenRepo->listEntities(0, true);

        if (count($notExpiredTokens) === 1) {
            $activeToken = current($notExpiredTokens);
            if ($activeToken->getExpireAtTimestamp() > time()) {
                return $activeToken->getToken();
            }
        }

        $tokenRepo->deleteAllInactive();

        $installFactory = new InstallTokenFactory();
        $installToken = $installFactory->createFromSeed($installSeed);

        $tokenRepo->save($installToken);

        return $installToken->getToken();
    }

    public function endHubIntegration(
        $installToken,
        $authorizationCode,
        $hubCallbackUrl = null,
        $webhookUrl = null
    ) {
        $tokenRepo = new InstallTokenRepository();

        $rawToken = $installToken;

        $installToken = $tokenRepo->findByPagarmeId(new HubInstallToken($installToken));

        if (is_null($installToken)) {
            $message = "Received an invalid installToken. NULL: $rawToken";
            $exception = new \Exception($message);
            $this->logService->exception($exception);
            throw $exception;
        }
        if (empty($installToken)) {
            $message = "installToken not found in database. Raw Token: $rawToken";

            $exception = new \Exception($message);

            $this->logService->exception($exception);
            throw $exception;
        }

        $isValidToken = is_a($installToken, InstallToken::class)
            && !$installToken->isExpired()
            && !$installToken->isUsed();

        if (!$isValidToken) {
            $messageFormat = "Received an invalid installToken.
            Is expired: %s | Is used: %s | Is a token: %s | Raw token: %s";

            $message =  sprintf(
                $messageFormat,
                $installToken->isExpired() ? "true" : "false",
                $installToken->isUsed() ? "true" : "false",
                is_a($installToken, InstallToken::class) ? "true" : "false",
                $installToken->getToken()->getValue()
            );
            $exception = new \Exception($message);

            $this->logService->exception($exception);
            throw $exception;
        }

        $body = [
            "code" => $authorizationCode
        ];

        $this->logService->info(
            sprintf(
                'Valid install token received: %s',
                $installToken->getToken()->getValue()
            )
        );

        if ($hubCallbackUrl) {
            $body['hub_callback_url'] = $hubCallbackUrl;
        }

        if ($webhookUrl) {
            $body['webhook_url'] = $webhookUrl;
        }

        $url = 'https://hubapi.mundipagg.com/auth/apps/access-tokens';
        $headers = [
            'PublicAppKey' => MPSetup::getHubAppPublicAppKey(),
            'Content-Type' => 'application/json'
        ];

        $this->logService->info(
            sprintf(
                'Sending request to %s;',
                $url
            ),
            $body
        );

        $result = Request::post(
            $url,
            $headers,
            json_encode($body)
        );

        if ($result->code === 201) {
            $this->executeCommandFromPost($result->body);

            //if its ok
            $installToken->setUsed(true);
            $tokenRepo->save($installToken);

            $this->logService->info(
                sprintf(
                    "Hub successfully installed for authorization code: %s",
                    $body["code"]
                )
            );
            return;
        }

        $exception = new \Exception(
            sprintf(
                "Received unexpected response from hub. HTTP Code: %s",
                $result->code
            )
        );

        $this->logService->info(
            $exception->getMessage(),
            $result
        );

        throw $exception;
    }

    public function getHubStatus()
    {
        $moduleConfig = MPSetup::getModuleConfiguration();

        return $moduleConfig->isHubEnabled() ? 'enabled' : 'disabled';
    }

    public function executeCommandFromPost($body)
    {
        $commandFactory = new HubCommandFactory();
        $command = $commandFactory->createFromStdClass($body);
        $command->execute();
    }
}
