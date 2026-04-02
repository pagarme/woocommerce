<?php

namespace Pagarme\Core\Hub\Factories;

use Exception;
use Pagarme\Core\Hub\Commands\AbstractCommand;
use Pagarme\Core\Hub\Commands\CommandType;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\AccountId;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Id\MerchantId;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use ReflectionClass;
use ReflectionException;

class HubCommandFactory
{
    /**
     * @param  $object
     * @return AbstractCommand
     * @throws ReflectionException|InvalidParamException
     * @throws Exception
     */
    public function createFromStdClass($object)
    {
        $commandClass = (new ReflectionClass(AbstractCommand::class))->getNamespaceName();
        $commandClass .= "\\" . $object->command . "Command";

        if (!class_exists($commandClass)) {
            throw new Exception("Invalid Command class! $commandClass");
        }

        /**
         * @var AbstractCommand $command
         */
        $command = new $commandClass();

        $command->setAccessToken(new HubAccessTokenKey($object->access_token));

        if (!empty($object->account_id)) {
            $command->setAccountId(new AccountId($object->account_id));
        }

        if (!empty($object->paymentProfileId)) {
            $command->setPaymentProfileId($object->paymentProfileId);
        }

        if (!empty($object->poiType)) {
            $command->setPoiType($object->poiType);
        } else {
            $command->setPoiType([]);
        }

        $type = $object->type;
        $command->setType(CommandType::$type());

        $publicKeyClass = PublicKey::class;
        if (
            $command->getType()->equals(CommandType::Sandbox())
            || $command->getType()->equals(CommandType::Development())
        ) {
            $publicKeyClass = TestPublicKey::class;
        }

        $command->setAccountPublicKey(
            new $publicKeyClass($object->account_public_key)
        );

        $command->setInstallId(new GUID($object->install_id));

        if (!empty($object->merchant_id)) {
            $command->setMerchantId(new MerchantId($object->merchant_id));
        }

        return $command;
    }
}
