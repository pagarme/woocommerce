<?php

namespace Pagarme\Core\Kernel\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\TransactionId;
use Pagarme\Core\Kernel\ValueObjects\TransactionStatus;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use Pagarme\Core\Payment\Factories\SavedCardFactory;

class TransactionFactory implements FactoryInterface
{
    public function createFromPostData($postData)
    {
        $transaction = new Transaction;

        $transaction->setPagarmeId(new TransactionId($postData['id']));

        $baseStatus = explode('_', $postData['status'] ?? '');
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $baseType = explode('_', $postData['transaction_type'] ?? '');
        $type = $baseType[0];
        for ($i = 1; $i < count($baseType); $i++) {
            $type .= ucfirst(($baseType[$i]));
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        if (isset($postData['amount'])) {
            $transaction->setAmount($postData['amount']);
        }

        $paidAmountIndex = isset($postData['paid_amount']) ? 'paid_amount' : 'amount';
        if (isset($postData[$paidAmountIndex])) {
            $transaction->setPaidAmount($postData[$paidAmountIndex]);
        }

        $acquirerName = '';
        if (isset($postData['acquirer_name'])) {
            $acquirerName = $postData['acquirer_name'];
        }

        $acquirerMessage = '';
        if (isset($postData['acquirer_message'])) {
            $acquirerMessage = $postData['acquirer_message'];
        }

        $acquirerNsu = 0;
        if (isset($postData['acquirer_nsu'])) {
            $acquirerNsu = $postData['acquirer_nsu'];
        }

        $acquirerTid = 0;
        if (isset($postData['acquirer_tid'])) {
            $acquirerTid = $postData['acquirer_tid'];
        }

        $acquirerAuthCode = 0;
        if (isset($postData['acquirer_auth_code'])) {
            $acquirerAuthCode = $postData['acquirer_auth_code'];
        }

        $transaction->setAcquirerName($acquirerName);
        $transaction->setAcquirerMessage($acquirerMessage);
        $transaction->setAcquirerNsu($acquirerNsu);
        $transaction->setAcquirerTid($acquirerTid);
        $transaction->setAcquirerAuthCode($acquirerAuthCode);

        $createdAt = \DateTime::createFromFormat(
            'Y-m-d\TH:i:s',
            substr($postData['created_at'], 0, 19)
        );

        $transaction->setCreatedAt($createdAt);
        $brand = null;
        $installments = null;
        if (isset($postData['card'])) {
            $brand = $postData['card']['brand'];
            $installments = isset($postData['installments']) ? $postData['installments'] : 1;

            $transaction->setBrand($brand);
            $transaction->setInstallments($installments);
        }

        if (isset($postData['pdf'])) {
            $transaction->setBoletoUrl($postData['pdf']);
        }

        $transaction->setPostData(json_decode(json_encode($postData)));

        if (isset($postData['card'])) {
            $cardFactory = new SavedCardFactory();
            $postData['card']['type'] = $transaction->getTransactionType()->getType();
            $card = $cardFactory->createFromTransactionData($postData['card']);
            $transaction->setCardData($card);
        }

        return $transaction;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $transaction = new Transaction();

        $transaction->setId($dbData['id']);
        $transaction->setChargeId(new ChargeId($dbData['charge_id']));
        $transaction->setPagarmeId(new TransactionId($dbData['pagarme_id']));

        $transaction->setAmount($dbData['amount']);
        $transaction->setPaidAmount($dbData['paid_amount']);

        $transaction->setAcquirerName($dbData['acquirer_name']);
        $transaction->setAcquirerMessage($dbData['acquirer_message']);
        $transaction->setAcquirerNsu($dbData['acquirer_nsu']);
        $transaction->setAcquirerTid($dbData['acquirer_tid']);
        $transaction->setAcquirerAuthCode($dbData['acquirer_auth_code']);

        $baseStatus = explode('_', $dbData['status'] ?? '');
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $baseType = explode('_', $dbData['type'] ?? '');
        $type = $baseType[0];
        for ($i = 1; $i < count($baseType); $i++) {
            $type .= ucfirst(($baseType[$i]));
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', $dbData['created_at']);
        $transaction->setCreatedAt($createdAt);

        if (isset($dbData['boleto_url'])) {
            $transaction->setBoletoUrl($dbData['boleto_url']);
        }

        $transaction->setPostData(json_decode(json_encode($dbData)));

        if (!empty($dbData['card_data']) && $dbData['card_data'] !== "null") {
            $cardData = json_decode($dbData['card_data']);
            $cardFactory = new SavedCardFactory();
            $cardData->type = $transaction->getTransactionType()->getType();
            $card = $cardFactory->createFromTransactionJson($cardData);

            $transaction->setCardData($card);
        }

        return $transaction;
    }
}