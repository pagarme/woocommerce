<?php

namespace Pagarme\Core\Recurrence\Factories;

abstract class TreatFactoryChargeDataBase
{
    protected function extractTransactionsFromDbData($dbData)
    {
        $transactions = [];
        if ($dbData['tran_id'] !== null) {
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
                    'card_data' => $this->treatCardData($tranCardData, $index)
                ];
                $transactions[] = $transaction;
            }
        }

        return $transactions;
    }

    private function treatCardData(array $tranCardData, $index)
    {
        if (!isset($tranCardData[$index])) {
            return null;
        }
        return $tranCardData[$index];
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
}
