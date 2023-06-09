<?php

namespace Pagarme\Core\Kernel\Aggregates;

use DateTime;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\TransactionStatus;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;

final class Transaction extends AbstractEntity
{
    /**
     *
     * @var TransactionType
     */
    private $transactionType;
    /**
     *
     * @var int
     */
    private $amount;
    /**
     *
     * @var int
     */
    private $paidAmount;

    /**
     *
     * @var TransactionStatus
     */
    private $status;
    /**
     *
     * @var \DateTime
     */
    private $createdAt;
    /**
     *
     * @var ChargeId
     */
    private $chargeId;

    /**
     *
     * @var string
     */
    private $acquirerName;
    /**
     *
     * @var string
     */
    private $acquirerTid;
    /**
     *
     * @var string
     */
    private $acquirerNsu;
    /**
     *
     * @var string
     */
    private $acquirerAuthCode;
    /**
     *
     * @var string
     */
    private $acquirerMessage;

    /**
     *
     * @var string
     */
    private $brand;

    /**
     *
     * @var int
     */
    private $installments;

    /** @var string */
    private $boletoUrl;

    private $postData;

    /** @var string */
    private $cardData;

    /**
     *
     * @return TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     *
     * @param TransactionType $transactionType
     */
    public function setTransactionType(TransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @param int $amount
     */
    public function setPaidAmount($paidAmount)
    {
        if ($paidAmount < 0) {
            throw new InvalidParamException(
                'Paid amount should be greater than or equal to 0!',
                $paidAmount
            );
        }

        $this->paidAmount = $paidAmount;
    }

    /**
     *
     * @return int
     */
    public function getPaidAmount()
    {
        return $this->paidAmount !== null ? $this->paidAmount : $this->getAmount();
    }

    /**
     *
     * @param int $amount
     */
    public function setAmount($amount)
    {
        if ($amount < 0) {
            throw new InvalidParamException(
                'Amount should be greater than or equal to 0!',
                $amount
            );
        }

        $this->amount = $amount;
    }

    /**
     *
     * @return TransactionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param  DateTime $createdAt
     * @return Transaction
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }


    /**
     *
     * @param TransactionStatus $status
     */
    public function setStatus(TransactionStatus $status)
    {
        $this->status = $status;
    }

    /**
     *
     * @return ChargeId
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }

    /**
     *
     * @param  ChargeId $chargeId
     * @return Transaction
     */
    public function setChargeId(ChargeId $chargeId)
    {
        $this->chargeId = $chargeId;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAcquirerName()
    {
        return $this->acquirerName;
    }

    /**
     *
     * @param  string $acquirerName
     * @return Transaction
     */
    public function setAcquirerName($acquirerName)
    {
        $this->acquirerName = $acquirerName;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAcquirerTid()
    {
        return $this->acquirerTid;
    }

    /**
     *
     * @param  string $acquirerTid
     * @return Transaction
     */
    public function setAcquirerTid($acquirerTid)
    {
        $this->acquirerTid = $acquirerTid;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAcquirerNsu()
    {
        return $this->acquirerNsu;
    }

    /**
     *
     * @param  string $acquirerNsu
     * @return Transaction
     */
    public function setAcquirerNsu($acquirerNsu)
    {
        $this->acquirerNsu = $acquirerNsu;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAcquirerAuthCode()
    {
        return $this->acquirerAuthCode;
    }

    /**
     *
     * @param  string $acquirerAuthCode
     * @return Transaction
     */
    public function setAcquirerAuthCode($acquirerAuthCode)
    {
        $this->acquirerAuthCode = $acquirerAuthCode;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getAcquirerMessage()
    {
        return $this->acquirerMessage;
    }

    /**
     *
     * @param  string $acquirerMessage
     * @return Transaction
     */
    public function setAcquirerMessage($acquirerMessage)
    {
        $this->acquirerMessage = $acquirerMessage;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     *
     * @param  string $brand
     * @return Transaction
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     *
     * @param  int $installments
     * @return Transaction
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;
        return $this;
    }

    /**
     * @return string
     */
    public function getBoletoUrl()
    {
        return $this->boletoUrl;
    }

    /**
     * @param string $boletoUrl
     */
    public function setBoletoUrl($boletoUrl)
    {
        $this->boletoUrl = $boletoUrl;
    }

    /**
     * @return mixed
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @param mixed $postData
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;
    }



    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->pagarmeId = $this->getPagarmeId();
        $obj->chargeId = $this->getChargeId();
        $obj->amount = $this->getAmount();
        $obj->paidAmount = $this->getPaidAmount();
        $obj->acquirerName = $this->getAcquirerName();
        $obj->acquirerMessage = $this->getAcquirerMessage();
        $obj->acquirerNsu = $this->getAcquirerNsu();
        $obj->acquirerTid = $this->getAcquirerTid();
        $obj->acquirerAuthCode = $this->getAcquirerAuthCode();
        $obj->type = $this->getTransactionType();
        $obj->status = $this->getStatus();
        $obj->createdAt = $this->getCreatedAt()->format('Y-m-d H:i:s');
        $obj->brand = $this->getBrand();
        $obj->installments = $this->getInstallments();
        $obj->boletoUrl = $this->getBoletoUrl();
        $obj->cardData = $this->getCardData();
        $obj->postData = $this->getPostData();

        return $obj;
    }

    /**
     * @return string
     */
    public function getCardData()
    {
        return $this->cardData;
    }

    /**
     * @param string $cardData
     * @return Transaction
     */
    public function setCardData($cardData)
    {
        $this->cardData = $cardData;
        return $this;
    }
}
