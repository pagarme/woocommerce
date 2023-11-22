<?php

namespace Pagarme\Core\Kernel\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
//use Pagarme\Core\Kernel\Factories\TransactionFactory;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Factories\CustomerFactory;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Throwable;

/**
 * Class ChargeFactory
 * @package Pagarme\Core\Kernel\Factories
 */
class ChargeFactory implements FactoryInterface
{
    /**
     *
     * @param  array $postData
     * @return Charge
     */
    public function createFromPostData($postData)
    {
        $charge = new Charge;
        $status = $postData['status'];

        $charge->setPagarmeId(new ChargeId($postData['id']));
        $charge->setCode($postData['code']);
        $charge->setAmount($postData['amount']);
        $paidAmount = isset($postData['paid_amount']) ? $postData['paid_amount'] : 0;
        $charge->setPaidAmount($paidAmount);

        if (!empty($postData['order']['id'])) {
            $orderId = $postData['order']['id'];
            $charge->setOrderId(new OrderId($orderId));
        }

        $this->setLastTransaction($postData, $charge);

        try {
            ChargeStatus::$status();
        }catch(Throwable $e) {
            throw new InvalidParamException(
                "Invalid charge status!",
                $status
            );
        }
        $charge->setStatus(ChargeStatus::$status());

        if (!empty($postData['metadata'])) {
            $metadata = json_decode(json_encode($postData['metadata']));
            $charge->setMetadata($metadata);
        }

        if (!empty($postData['customer'])) {
            $customerFactory = new CustomerFactory();
            $customer = $customerFactory->createFromPostData($postData['customer']);
            $charge->setCustomer($customer);
        }

        return $charge;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $charge = new Charge();

        $charge->setId($dbData['id']);
        $charge->setPagarmeId(new ChargeId($dbData['pagarme_id']));
        $charge->setOrderId(new OrderId($dbData['order_id']));

        $charge->setCode($dbData['code']);

        $charge->setAmount($dbData['amount']);
        $charge->setPaidAmount($dbData['paid_amount']);
        $charge->setCanceledAmount($dbData['canceled_amount']);
        $charge->setRefundedAmount($dbData['refunded_amount']);

        $status = $dbData['status'];
        $charge->setStatus(ChargeStatus::$status());

        if (!empty($dbData['metadata'])) {
            $metadata = json_decode($dbData['metadata']);
            $charge->setMetadata($metadata);
        }

        $transactionFactory = new TransactionFactory();
        $transactions = $this->extractTransactionsFromDbData($dbData);
        foreach ($transactions as $transaction) {
            $newTransaction = $transactionFactory->createFromDbData($transaction);
            $charge->addTransaction($newTransaction);
        }

        if (!empty($dbData['customer_id'])) {
            $customerRepository = new CustomerRepository();
            $customer = $customerRepository->findByPagarmeId(
                new CustomerId($dbData['customer_id'])
            );

            if ($customer) {
                $charge->setCustomer($customer);
            }
        }

        return $charge;
    }

    /**
     * @param $dbData
     * @return array
     */
    private function extractTransactionsFromDbData($dbData)
    {
        $transactions = [];
        if (isset($dbData['tran_id']) && $dbData['tran_id'] !== null) {
            $tranId = explode(',', $dbData['tran_id']);
            $tranPagarmeId = explode(',', $dbData['tran_pagarme_id'] ?? '');
            $tranChargeId = explode(',', $dbData['tran_charge_id'] ?? '');
            $tranAmount = explode(',', $dbData['tran_amount'] ?? '');
            $tranPaidAmount = explode(',', $dbData['tran_paid_amount'] ?? '');
            $tranType = explode(',', $dbData['tran_type'] ?? '');
            $tranStatus = explode(',', $dbData['tran_status'] ?? '');
            $tranCreatedAt = explode(',', $dbData['tran_created_at'] ?? '');

            $tranAcquirerNsu = explode(',', $dbData['tran_acquirer_nsu'] ?? '');
            $tranAcquirerTid = explode(',', $dbData['tran_acquirer_tid'] ?? '');
            $tranAcquirerAuthCode = explode(
                ',',
                $dbData['tran_acquirer_auth_code'] ?? ''
             );
            $tranAcquirerName = explode(',', $dbData['tran_acquirer_name'] ?? '');
            $tranAcquirerMessage = explode(',', $dbData['tran_acquirer_message'] ?? '');
            $tranBoletoUrl = explode(',', $dbData['tran_boleto_url'] ?? '');
            $tranCardData = explode('---', $dbData['tran_card_data'] ?? '');
            $tranData = explode('---', $dbData['tran_data'] ?? '');

            foreach ($tranId as $index => $id) {
                $transaction = [
                    'id' => $id,
                    'pagarme_id' => $tranPagarmeId[$index],
                    'charge_id' => $tranChargeId[$index],
                    'amount' => $tranAmount[$index],
                    'paid_amount' => $tranPaidAmount[$index],
                    'type' => $tranType[$index],
                    'status' => $tranStatus[$index],
                    'acquirer_name' => $tranAcquirerName[$index],
                    'acquirer_tid' => $tranAcquirerTid[$index],
                    'acquirer_nsu' => $tranAcquirerNsu[$index],
                    'acquirer_auth_code' => $tranAcquirerAuthCode[$index],
                    'acquirer_message' => $tranAcquirerMessage[$index],
                    'created_at' => $tranCreatedAt[$index],
                    'boleto_url' => $this->treatBoletoUrl($tranBoletoUrl, $index),
                    'card_data' => $this->handleCreditCardData($tranCardData, $index),
                    'tran_data' => $this->handleTransactionData($tranData, $index)
                ];

                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

    /**
     * @param array $carData
     * @param int $index
     * @return string|null
     */
    private function handleCreditCardData(array $tranCardData, $index)
    {
        if (!isset($tranCardData[$index])) {
            return null;
        }
        return $tranCardData[$index];
    }

    private function handleTransactionData(array $tranData, $index)
    {
        if (!isset($tranData[$index])) {
            return null;
        }
        return $tranData[$index];
    }

    /**
     * @param array $tranBoletoUrl
     * @param int $index
     * @return string|null
     */
    private function treatBoletoUrl(array $tranBoletoUrl, $index)
    {
        if (!isset($tranBoletoUrl[$index])) {
            return null;
        }
        return $tranBoletoUrl[$index];
    }

    private function setLastTransaction($postData, &$charge)
    {
        $lastTransactionData = null;
        if (isset($postData['last_transaction'])) {
            $lastTransactionData = $postData['last_transaction'];
        }

        if ($lastTransactionData !== null) {
            $transactionFactory = new TransactionFactory();
            $lastTransaction = $transactionFactory->createFromPostData(
                $lastTransactionData
            );
            $lastTransaction->setChargeId($charge->getPagarmeId());
            $charge->addTransaction($lastTransaction);
        }
    }
}
