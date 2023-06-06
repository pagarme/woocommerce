<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use PagarmeCoreApiLib\Models\CreatePixPaymentRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;

final class PixPayment extends AbstractPayment
{
    /**
     * @var integer|null $expiresIn
     */
    public $expiresIn;

    /**
     * @var \DateTime|null $expiresAt
     */
    public $expiresAt;

    /**
     * @var array $additionalInformation
     */
    public $additionalInformation;


    /**
     * @return int|null
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param int|null $expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTime|null $expiresAt
     */
    public function setExpiresAt(\DateTime $expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return array
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * @param array $additionalInformation
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();
        $obj->expiresIn = $this->getExpiresIn();
        $obj->expiresAt = $this->getExpiresAt();
        $obj->additionalInformation = $this->getAdditionalInformation();

        return $obj;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::pix()->getMethod();
    }

    /**
     * @return CreatePixPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = new CreatePixPaymentRequest();

        $paymentRequest->expiresIn = $this->getExpiresIn();
        $paymentRequest->expiresAt = $this->getExpiresAt();
        $paymentRequest->additionalInformation= $this->getAdditionalInformation();

        return $paymentRequest;
    }
}
