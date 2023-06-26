<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use PagarmeCoreApiLib\Models\CreateCreditCardPaymentRequest;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Pagarme\Core\Payment\ValueObjects\CardId;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;

final class SavedDebitCardPayment extends AbstractCreditCardPayment
{
    /** @var CustomerId */
    private $owner;

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

    static public function getBaseCode()
    {
        return PaymentMethod::debitCard()->getMethod();
    }

    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = parent::convertToPrimitivePaymentRequest();

        $paymentRequest->cardId = $this->getIdentifier()->getValue();

        return $paymentRequest;
    }

    /**
     * @param int $installments
     */
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
