<?php

namespace Pagarme\Core\Test\Kernel\Aggregates;

use DateTime;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\TransactionStatus;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @var Transaction
     */
    private $transaction;

    protected function setUp(): void
    {
        $this->transaction = new Transaction();
        parent::setUp();
    }

    // ========== TESTES BÁSICOS ==========

    public function testTransactionShouldBeCreated()
    {
        $transaction = new Transaction();
        $this->assertTrue($transaction !== null);
        $this->assertInstanceOf(Transaction::class, $transaction);
    }

    public function testSetAndGetTransactionType()
    {
        $type = new TransactionType('credit_card');
        $this->transaction->setTransactionType($type);
        
        $this->assertEquals($type, $this->transaction->getTransactionType());
        $this->assertInstanceOf(TransactionType::class, $this->transaction->getTransactionType());
    }

    public function testSetAndGetAmount()
    {
        $this->transaction->setAmount(1500);
        
        $this->assertEquals(1500, $this->transaction->getAmount());
        $this->assertIsInt($this->transaction->getAmount());
    }

    public function testSetAmountThrowsExceptionWhenNegative()
    {
        $this->expectException(InvalidParamException::class);
        $this->expectExceptionMessage('Amount should be greater than or equal to 0!');
        
        $this->transaction->setAmount(-100);
    }

    public function testSetAndGetPaidAmount()
    {
        $this->transaction->setPaidAmount(1000);
        
        $this->assertEquals(1000, $this->transaction->getPaidAmount());
    }

    public function testSetPaidAmountThrowsExceptionWhenNegative()
    {
        $this->expectException(InvalidParamException::class);
        $this->expectExceptionMessage('Paid amount should be greater than or equal to 0!');
        
        $this->transaction->setPaidAmount(-50);
    }

    public function testGetPaidAmountReturnsAmountWhenNull()
    {
        $this->transaction->setAmount(2000);
        
        // paidAmount not set, should return amount
        $this->assertEquals(2000, $this->transaction->getPaidAmount());
    }

    public function testSetAndGetStatus()
    {
        $status = TransactionStatus::paid();
        $this->transaction->setStatus($status);
        
        $this->assertEquals($status, $this->transaction->getStatus());
        $this->assertInstanceOf(TransactionStatus::class, $this->transaction->getStatus());
    }

    public function testSetAndGetCreatedAt()
    {
        $date = new DateTime('2026-01-13 10:00:00');
        $this->transaction->setCreatedAt($date);
        
        $this->assertEquals($date, $this->transaction->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $this->transaction->getCreatedAt());
    }

    public function testSetAndGetChargeId()
    {
        $chargeId = new ChargeId('ch_1234567890abcdef');
        $this->transaction->setChargeId($chargeId);
        
        $this->assertEquals($chargeId, $this->transaction->getChargeId());
        $this->assertInstanceOf(ChargeId::class, $this->transaction->getChargeId());
    }

    // ========== TESTES DE ACQUIRER ==========

    public function testSetAndGetAcquirerName()
    {
        $this->transaction->setAcquirerName('Pagarme');
        
        $this->assertEquals('Pagarme', $this->transaction->getAcquirerName());
        $this->assertIsString($this->transaction->getAcquirerName());
    }

    public function testSetAndGetAcquirerTid()
    {
        $tid = 'TID123456789';
        $this->transaction->setAcquirerTid($tid);
        
        $this->assertEquals($tid, $this->transaction->getAcquirerTid());
    }

    public function testSetAndGetAcquirerNsu()
    {
        $nsu = 'NSU987654321';
        $this->transaction->setAcquirerNsu($nsu);
        
        $this->assertEquals($nsu, $this->transaction->getAcquirerNsu());
    }

    public function testSetAndGetAcquirerAuthCode()
    {
        $authCode = 'AUTH123456';
        $this->transaction->setAcquirerAuthCode($authCode);
        
        $this->assertEquals($authCode, $this->transaction->getAcquirerAuthCode());
    }

    public function testSetAndGetAcquirerMessage()
    {
        $message = 'Transaction approved';
        $this->transaction->setAcquirerMessage($message);
        
        $this->assertEquals($message, $this->transaction->getAcquirerMessage());
    }

    // ========== TESTES DE CARTÃO ==========

    public function testSetAndGetBrand()
    {
        $brand = 'Visa';
        $this->transaction->setBrand($brand);
        
        $this->assertEquals($brand, $this->transaction->getBrand());
        $this->assertIsString($this->transaction->getBrand());
    }

    public function testSetAndGetInstallments()
    {
        $this->transaction->setInstallments(3);
        
        $this->assertEquals(3, $this->transaction->getInstallments());
        $this->assertIsInt($this->transaction->getInstallments());
    }

    public function testSetAndGetCardData()
    {
        $cardData = '{"first_six": "411111", "last_four": "1111"}';
        $this->transaction->setCardData($cardData);
        
        $this->assertEquals($cardData, $this->transaction->getCardData());
    }

    // ========== TESTES DE BOLETO/PIX ==========

    public function testSetAndGetBoletoUrl()
    {
        $url = 'https://api.pagarme.com/boleto/12345';
        $this->transaction->setBoletoUrl($url);
        
        $this->assertEquals($url, $this->transaction->getBoletoUrl());
        $this->assertIsString($this->transaction->getBoletoUrl());
    }

    public function testSetAndGetPostData()
    {
        $postData = new \stdClass();
        $postData->status = 'paid';
        $postData->amount = 1000;
        
        $this->transaction->setPostData($postData);
        
        $this->assertEquals($postData, $this->transaction->getPostData());
        $this->assertIsObject($this->transaction->getPostData());
    }

    // ========== TESTES DE GATEWAY ==========

    public function testGetGatewayResponse()
    {
        $postData = new \stdClass();
        $postData->gateway_response = new \stdClass();
        $postData->gateway_response->code = '200';
        
        $this->transaction->setPostData($postData);
        
        $gatewayResponse = $this->transaction->getGatewayResponse();
        $this->assertIsObject($gatewayResponse);
        $this->assertEquals('200', $gatewayResponse->code);
    }

    public function testGetGatewayErrorMessages()
    {
        $postData = new \stdClass();
        $postData->gateway_response = new \stdClass();
        $postData->gateway_response->errors = [
            'Invalid card number',
            'Insufficient funds'
        ];
        
        $this->transaction->setPostData($postData);
        
        $errors = $this->transaction->getGatewayErrorMessages();
        $this->assertIsArray($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('Invalid card number', $errors[0]);
    }

    public function testGetGatewayErrorMessagesReturnsEmptyWhenNoErrors()
    {
        $postData = new \stdClass();
        $postData->gateway_response = new \stdClass();
        
        $this->transaction->setPostData($postData);
        
        $errors = $this->transaction->getGatewayErrorMessages();
        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }

    // ========== TESTES DE SERIALIZAÇÃO ==========

    public function testJsonSerialize()
    {
        $chargeId = new ChargeId('ch_1234567890abcdef');
        $type = new TransactionType('credit_card');
        $status = TransactionStatus::paid();
        $date = new DateTime('2026-01-13 12:00:00');
        
        $this->transaction->setChargeId($chargeId);
        $this->transaction->setTransactionType($type);
        $this->transaction->setStatus($status);
        $this->transaction->setAmount(1500);
        $this->transaction->setPaidAmount(1500);
        $this->transaction->setCreatedAt($date);
        $this->transaction->setAcquirerName('Pagarme');
        $this->transaction->setAcquirerTid('TID123');
        $this->transaction->setAcquirerNsu('NSU456');
        $this->transaction->setAcquirerAuthCode('AUTH789');
        $this->transaction->setAcquirerMessage('Approved');
        $this->transaction->setBrand('Visa');
        $this->transaction->setInstallments(2);
        $this->transaction->setBoletoUrl('https://boleto.url');
        $this->transaction->setCardData('{"card": "data"}');
        
        $postData = new \stdClass();
        $postData->test = 'value';
        $this->transaction->setPostData($postData);
        
        $json = json_encode($this->transaction);
        $this->assertJson($json);
        
        $data = json_decode($json, true);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('paidAmount', $data);
        $this->assertArrayHasKey('chargeId', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('acquirerName', $data);
        $this->assertArrayHasKey('acquirerTid', $data);
        $this->assertArrayHasKey('acquirerNsu', $data);
        $this->assertArrayHasKey('acquirerAuthCode', $data);
        $this->assertArrayHasKey('acquirerMessage', $data);
        $this->assertArrayHasKey('brand', $data);
        $this->assertArrayHasKey('installments', $data);
        $this->assertArrayHasKey('boletoUrl', $data);
        $this->assertArrayHasKey('cardData', $data);
        $this->assertArrayHasKey('postData', $data);
        $this->assertArrayHasKey('createdAt', $data);
        
        $this->assertEquals(1500, $data['amount']);
        $this->assertEquals('Pagarme', $data['acquirerName']);
        $this->assertEquals('Visa', $data['brand']);
        $this->assertEquals(2, $data['installments']);
    }

    public function testCompleteTransactionFlow()
    {
        // Simulate a complete credit card transaction
        $chargeId = new ChargeId('ch_complete123456');
        $type = new TransactionType('credit_card');
        $status = TransactionStatus::paid();
        $date = new DateTime();
        
        $this->transaction->setChargeId($chargeId);
        $this->transaction->setTransactionType($type);
        $this->transaction->setAmount(5000);
        $this->transaction->setPaidAmount(5000);
        $this->transaction->setStatus($status);
        $this->transaction->setCreatedAt($date);
        $this->transaction->setBrand('Mastercard');
        $this->transaction->setInstallments(3);
        $this->transaction->setAcquirerName('Stone');
        $this->transaction->setAcquirerTid('TID999');
        $this->transaction->setAcquirerNsu('NSU888');
        $this->transaction->setAcquirerAuthCode('AUTH777');
        $this->transaction->setAcquirerMessage('Transaction approved');
        
        $postData = new \stdClass();
        $postData->gateway_response = new \stdClass();
        $postData->gateway_response->code = '200';
        $this->transaction->setPostData($postData);
        
        // Assertions
        $this->assertEquals(5000, $this->transaction->getAmount());
        $this->assertEquals(5000, $this->transaction->getPaidAmount());
        $this->assertEquals('credit_card', $this->transaction->getTransactionType()->getType());
        $this->assertTrue($this->transaction->getStatus()->equals(TransactionStatus::paid()));
        $this->assertEquals('Mastercard', $this->transaction->getBrand());
        $this->assertEquals(3, $this->transaction->getInstallments());
        $this->assertEquals('Stone', $this->transaction->getAcquirerName());
        $this->assertIsObject($this->transaction->getGatewayResponse());
    }

    public function testPixTransactionFlow()
    {
        $chargeId = new ChargeId('ch_pix123456');
        $type = new TransactionType('pix');
        $status = TransactionStatus::paid();
        
        $this->transaction->setChargeId($chargeId);
        $this->transaction->setTransactionType($type);
        $this->transaction->setAmount(10000);
        $this->transaction->setPaidAmount(10000);
        $this->transaction->setStatus($status);
        $this->transaction->setCreatedAt(new DateTime());
        
        $postData = new \stdClass();
        $postData->qr_code = 'PIX_QR_CODE_DATA';
        $postData->qr_code_url = 'https://pix.url';
        $this->transaction->setPostData($postData);
        
        $this->assertEquals('pix', $this->transaction->getTransactionType()->getType());
        $this->assertEquals(10000, $this->transaction->getAmount());
        $this->assertObjectHasProperty('qr_code', $this->transaction->getPostData());
    }

    public function testBoletoTransactionFlow()
    {
        $chargeId = new ChargeId('ch_boleto123456');
        $type = new TransactionType('boleto');
        $status = TransactionStatus::pending();
        
        $this->transaction->setChargeId($chargeId);
        $this->transaction->setTransactionType($type);
        $this->transaction->setAmount(7500);
        $this->transaction->setStatus($status);
        $this->transaction->setCreatedAt(new DateTime());
        $this->transaction->setBoletoUrl('https://boleto.pagarme.com/123456');
        
        $postData = new \stdClass();
        $postData->boleto_barcode = '12345678901234567890123456789012345678901234';
        $this->transaction->setPostData($postData);
        
        $this->assertEquals('boleto', $this->transaction->getTransactionType()->getType());
        $this->assertEquals(7500, $this->transaction->getAmount());
        $this->assertEquals('https://boleto.pagarme.com/123456', $this->transaction->getBoletoUrl());
        $this->assertObjectHasProperty('boleto_barcode', $this->transaction->getPostData());
    }
}
