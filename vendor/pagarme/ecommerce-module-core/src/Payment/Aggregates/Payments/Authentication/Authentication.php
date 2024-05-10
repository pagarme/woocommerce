<?php

namespace Pagarme\Core\Payment\Aggregates\Payments\Authentication;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\Type\ThreeDSecure;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PagarmeCoreApiLib\Models\CreatePaymentAuthenticationRequest;
use PagarmeCoreApiLib\Models\CreateThreeDSecureRequest;
use stdClass;

final class Authentication extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var ThreeDSecure
     */
    private $threeDSecure;

    /**
     * @var string
     */
    private $status;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return void
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return ThreeDSecure
     */
    public function getThreeDSecure()
    {
        return $this->threeDSecure;
    }

    /**
     * @param ThreeDSecure $threeDSecure
     * @return void
     */
    public function setThreeDSecure(ThreeDSecure $threeDSecure)
    {
        $this->threeDSecure = $threeDSecure;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return void
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return CreateThreeDSecureRequest
     */
    public function getThreeDSecureSDK()
    {
        return $this->threeDSecure->convertToSDKRequest();
    }

    /**
     * @return CreatePaymentAuthenticationRequest
     */
    public function convertToSDKRequest()
    {
        $authenticationRequest = new CreatePaymentAuthenticationRequest();
        $authenticationRequest->type = $this->getType();
        if (self::isThreeDSecureType($this)) {
            $authenticationRequest->threedSecure = $this->getThreeDSecureSDK();
        }
        return $authenticationRequest;
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->type = $this->getType();
        $obj->transactionId = $this->getThreeDSecure();
        return $obj;
    }

    /**
     * @param stdClass $object
     * @return self
     */
    public static function createFromStdClass(stdClass $object)
    {
        $authentication = new self();
        $authentication->setType($object->type);
        $authentication->setStatus($object->status);
        if (self::isThreeDSecureType($authentication)) {
            $authentication->setThreeDSecure(ThreeDSecure::createFromStdClass($object->threeDSecure));
        }
        return $authentication;
    }

    /**
     * @param Authentication $authentication
     * @return bool
     */
    private static function isThreeDSecureType(Authentication $authentication)
    {
        return $authentication->getType() === ThreeDSecure::TYPE;
    }
}
