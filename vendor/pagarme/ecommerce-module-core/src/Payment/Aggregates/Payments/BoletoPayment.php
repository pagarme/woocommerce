<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use DateTime;
use Exception;
use PagarmeCoreApiLib\Models\CreateBoletoPaymentRequest;
use Pagarme\Core\Payment\ValueObjects\BoletoBank;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;

final class BoletoPayment extends AbstractPayment
{
    /** @var BoletoBank */
    private $bank;
    /** @var string */
    private $instructions;

    /** @var DateTime|null */
    private $dueAt;

    /**
     * @return BoletoBank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * @param BoletoBank $bank
     */
    public function setBank(BoletoBank $bank)
    {
        $this->bank = $bank;
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * @param string $instructions
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    }

    /**
     * @return DateTime|null
     */
    public function getDueAt()
    {
        return $this->dueAt;
    }

    /**
     * @param DateTime|string|null $dueAt
     * @throws Exception
     */
    public function setDueAt($dueAt)
    {
        $formattedDueAt = $dueAt;
        if (is_string($dueAt)) {
            $formattedDueAt = new DateTime($dueAt);
        }
        $this->dueAt = $formattedDueAt;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->bank = $this->bank;
        $obj->instructions = $this->instructions;
        $obj->dueAt = $this->dueAt;

        return $obj;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::boleto()->getMethod();
    }

    /**
     * @return CreateBoletoPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $paymentRequest = new CreateBoletoPaymentRequest();

        $bank = $this->getBank();
        if ($bank && method_exists($bank, 'getCode')) {
            $paymentRequest->bank = $this->getBank()->getCode();
        }
        $paymentRequest->instructions = $this->getInstructions();
        $paymentRequest->dueAt = $this->getDueAt();

        return $paymentRequest;
    }
}
