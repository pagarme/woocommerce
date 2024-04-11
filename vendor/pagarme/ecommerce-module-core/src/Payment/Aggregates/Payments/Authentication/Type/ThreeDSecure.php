<?php

namespace Pagarme\Core\Payment\Aggregates\Payments\Authentication\Type;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use PagarmeCoreApiLib\Models\CreateThreeDSecureRequest;
use stdClass;

final class ThreeDSecure extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    const TYPE = 'threed_secure';

    /**
     * @var string
     */
    private $mpi;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @return string
     */
    public function getMpi()
    {
        return $this->mpi;
    }

    /**
     * @param string $mpi
     * @return void
     */
    public function setMpi(string $mpi)
    {
        $this->mpi = $mpi;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId(string $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return CreateThreeDSecureRequest
     */
    public function convertToSDKRequest()
    {
        $threeDSecureRequest = new CreateThreeDSecureRequest();
        $threeDSecureRequest->mpi = $this->getMpi();
        $threeDSecureRequest->transactionId = $this->getTransactionId();

        return $threeDSecureRequest;
    }

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->mpi = $this->getMpi();
        $obj->transactionId = $this->getTransactionId();
        return $obj;
    }

    /**
     * @param stdClass $object
     * @return self
     */
    public static function createFromStdClass(stdClass $object)
    {
        $threeDSecure = new self();
        $threeDSecure->setMpi($object->mpi);
        $threeDSecure->setTransactionId($object->transactionId);
        return $threeDSecure;
    }
}
