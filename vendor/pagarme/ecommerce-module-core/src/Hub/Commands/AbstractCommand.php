<?php

namespace Pagarme\Core\Hub\Commands;


use Pagarme\Core\Kernel\Interfaces\CommandInterface;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\Id\AccountId;
use Pagarme\Core\Kernel\ValueObjects\Id\MerchantId;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;

abstract class AbstractCommand implements CommandInterface
{
    /**
     *
     * @var HubAccessTokenKey
     */
    protected $accessToken;
    /**
     *
     * @var AccountId
     */
    protected $accountId;
    /**
     *
     * @var PublicKey|TestPublicKey
     */
    protected $accountPublicKey;
    /**
     *
     * @var GUID
     */
    protected $installId;
    /**
     *
     * @var MerchantId
     */
    protected $merchantId;
    /**
     *
     * @var CommandType
     */
    protected $type;
    /**
     *
     * @var LogService
     */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService('Hub', true);
    }

    /**
     *
     * @return HubAccessTokenKey
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     *
     * @param  HubAccessTokenKey $accessToken
     * @return AbstractCommand
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     *
     * @return AccountId
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     *
     * @param  AccountId $accountId
     * @return AbstractCommand
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
        return $this;
    }

    /**
     *
     * @return PublicKey|TestPublicKey
     */
    public function getAccountPublicKey()
    {
        return $this->accountPublicKey;
    }

    /**
     *
     * @param  PublicKey|TestPublicKey $accountPublicKey
     * @return AbstractCommand
     */
    public function setAccountPublicKey($accountPublicKey)
    {
        $this->accountPublicKey = $accountPublicKey;
        return $this;
    }

    /**
     *
     * @return GUID
     */
    public function getInstallId()
    {
        return $this->installId;
    }

    /**
     *
     * @param  GUID $installId
     * @return AbstractCommand
     */
    public function setInstallId($installId)
    {
        $this->installId = $installId;
        return $this;
    }

    /**
     *
     * @return MerchantId
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     *
     * @param  MerchantId $merchantId
     * @return AbstractCommand
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     *
     * @return CommandType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param  CommandType $type
     * @return AbstractCommand
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
