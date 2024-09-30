<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use Pagarme\Core\Payment\ValueObjects\PaymentMethod;
use PagarmeCoreApiLib\Models\CreateGooglePayPaymentRequest;

final class GooglePayPayment extends AbstractPayment
{

    /**
     * @var string $payload
     */
    public $payload;

    /**
     * @var array $additionalInformation
     */
    public $additionalInformation;

    /**
     * @var array $billingAddress
     */
    public $billingAddress;

    /**
     * @return array
     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * @param array $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @param array $additionalInformation
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;
    }

    static public function getBaseCode()
    {
        return PaymentMethod::creditCard()->getMethod();
    }

    public function getGooglePayload()
    {
        return $this->getParsedGooglePayload();
    }

    private function getParsedGooglePayload()
    {
        $payload = json_decode($this->getAdditionalInformation()->googlepayData, true);
        $this->parseVersion($payload);
        $this->parseSignedMessage($payload);
        $this->parseIntermediateSigningKey($payload);
        $payload['merchant_identifier'] = $this->getMerchantIdentifier();
        return $payload;
    }

    private function parseIntermediateSigningKey(&$payload)
    {
        $payload['intermediate_signing_key'] = $payload['intermediateSigningKey'];
        $payload['intermediate_signing_key']['signed_key'] = $payload['intermediate_signing_key']['signedKey'];
        unset($payload['intermediateSigningKey']);
        unset($payload['intermediate_signing_key']['signedKey']);
    }
    
    private function parseSignedMessage(&$payload)
    {
        $payload['signed_message'] = $payload['signedMessage'];
        unset($payload['signedMessage']);
    }

    private function parseVersion(&$payload)
    {
        $payload['version'] = $payload['protocolVersion'];
        unset($payload['protocolVersion']);
    }

    private function getMerchantIdentifier()
    {
        return $this->moduleConfig->getGooglePayConfig()->getMerchantId();
    }

    private function getStatementDescriptor()
    {
        return $this->moduleConfig->getCardStatementDescriptor();
    }

    private function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return CreateGooglePayPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $payload = new \stdClass();
        $payload->type = "google_pay";
        $payload->google_pay = $this->getGooglePayload();
        $card = new \stdClass();
        $card->billing_address = $this->getBillingAddress();
        return new CreateGooglePayPaymentRequest($this->getStatementDescriptor(), $payload, $card);
    }
}
