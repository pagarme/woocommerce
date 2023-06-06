<?php

namespace Pagarme\Core\Hub\Aggregates;

use Pagarme\Core\Hub\ValueObjects\HubInstallToken;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;

final class InstallToken extends AbstractEntity
{
    const LIFE_SPAN = 43200; //time in seconds

    /**
     *
     * @var HubInstallToken
     */
    private $token;
    /**
     *
     * @var bool
     */
    private $used;
    /**
     *
     * @var int
     */
    private $createdAtTimestamp;
    /**
     *
     * @var int
     */
    private $expireAtTimestamp;

    /**
     *
     * @return HubInstallToken
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     *
     * @param  HubInstallToken $token
     * @return InstallToken
     */
    public function setToken(HubInstallToken $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     *
     * @param  bool $used
     * @return InstallToken
     */
    public function setUsed($used)
    {
        $this->used = filter_var($used, FILTER_VALIDATE_BOOLEAN);
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isExpired()
    {
        return !(time() < $this->expireAtTimestamp);
    }

    /**
     *
     * @return int
     */
    public function getCreatedAtTimestamp()
    {
        return $this->createdAtTimestamp;
    }

    /**
     *
     * @param  int $createdAtTimestamp
     * @return InstallToken
     */
    public function setCreatedAtTimestamp($createdAtTimestamp)
    {
        $this->createdAtTimestamp = intval($createdAtTimestamp);
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getExpireAtTimestamp()
    {
        return $this->expireAtTimestamp;
    }

    /**
     *
     * @param  int $expireAtTimestamp
     * @return InstallToken
     */
    public function setExpireAtTimestamp($expireAtTimestamp)
    {
        $this->expireAtTimestamp = intval($expireAtTimestamp);
        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isDisabled()
    {
        return $this->isExpired();
    }

    public function setDisabled($isDisabled)
    {
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->id = $this->id;
        $obj->token = $this->token;
        $obj->used = $this->used;
        $obj->expired = $this->isExpired();
        $obj->createdAtTimestamp = $this->createdAtTimestamp;
        $obj->expireAtTimestamp = $this->expireAtTimestamp;

        return $obj;
    }
}
