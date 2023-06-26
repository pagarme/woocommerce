<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use PagarmeCoreApiLib\Models\CreateCreditCardPaymentRequest;
use PagarmeCoreApiLib\Models\CreateCardRequest;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Pagarme\Core\Payment\ValueObjects\CardId;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;

final class SavedVoucherCardPayment extends AbstractCreditCardPayment
{
    /** @var CustomerId */
    private $owner;
    private $cvv;

    /**
     * @return CustomerId
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param CustomerId $owner
     */
    public function setOwner(CustomerId $owner)
    {
        $this->owner = $owner;
    }

    public function setCvv($cvv)
    {
        $this->cvv = $cvv;
    }

    public function getCvv()
    {
        return $this->cvv;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->cardId = $this->identifier;
        $obj->owner = $this->owner;

        return $obj;
    }

    public function setIdentifier(AbstractCardIdentifier $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param CardId $identifier
     */
    public function setCardId(CardId $cardId)
    {
        $this->setIdentifier($cardId);
    }

    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = parent::convertToPrimitivePaymentRequest();

        $paymentRequest->card->cvv = $this->getCvv();
        $paymentRequest->card->holderDocument = $this->getCustomer()->getDocument();
        $paymentRequest->cardId = $this->getIdentifier()->getValue();

        return $paymentRequest;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::voucher()->getMethod();
    }

    public function setInstallments($installments)
    {
        if ($installments < 1) {
            throw new InvalidParamException(
                "Installments should be at least 1",
                $installments
            );
        }

        $this->installments = $installments;
    }
}
