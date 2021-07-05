<?php

namespace Pagarme\Core\Hub\Factories;

use Pagarme\Core\Hub\Aggregates\InstallToken;
use Pagarme\Core\Hub\ValueObjects\HubInstallToken;

final class InstallTokenFactory
{
    public function createFromSeed($seed)
    {
        $token = hash('sha512', $seed . '|' . microtime());
        $token = new HubInstallToken($token);

        $lifeSpam = InstallToken::LIFE_SPAN; //30 minutes;
        $createdTime = time();
        $expireTime = $createdTime + $lifeSpam;

        $installToken = new InstallToken;
        $installToken->setToken($token);
        $installToken->setUsed(false);
        $installToken->setCreatedAtTimestamp($createdTime);
        $installToken->setExpireAtTimestamp($expireTime);

        return $installToken;
    }

    public function create()
    {
        return $this->createFromSeed(time());
    }

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $installToken = new InstallToken;
        $installToken->setId($data['id']);
        $installToken->setToken(new HubInstallToken($data['token']));
        $installToken->setUsed($data['used']);
        $installToken->setCreatedAtTimestamp($data['createdAtTimestamp']);
        $installToken->setExpireAtTimestamp($data['expireAtTimestmap']);

        return $installToken;

    }

    public function createFromDBData($data)
    {
        $installToken = new InstallToken;
        $installToken->setId($data['id']);
        $installToken->setToken(new HubInstallToken($data['token']));
        $installToken->setUsed($data['used']);
        $installToken->setCreatedAtTimestamp($data['created_at_timestamp']);
        $installToken->setExpireAtTimestamp($data['expire_at_timestamp']);

        return $installToken;
    }
}