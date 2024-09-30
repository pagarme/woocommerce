<?php

namespace Pagarme\Core\Kernel\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Factories\ChargeFactory;
use Pagarme\Core\Kernel\Helper\StringFunctionsHelper;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;

final class ChargeRepository extends AbstractRepository
{

    public function findByOrderId(OrderId $orderId)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $id = $orderId->getValue();
        
        $this->db->query("SET group_concat_max_len = 8096;");

        $query = "
            SELECT 
                c.*, 
                GROUP_CONCAT(t.id) as tran_id, 
                GROUP_CONCAT(t.pagarme_id) as tran_pagarme_id,
                GROUP_CONCAT(t.charge_id) as tran_charge_id,
                GROUP_CONCAT(t.amount) as tran_amount,
                GROUP_CONCAT(t.paid_amount) as tran_paid_amount,
                GROUP_CONCAT(t.acquirer_name) as tran_acquirer_name,                
                GROUP_CONCAT(t.acquirer_message) as tran_acquirer_message,                
                GROUP_CONCAT(t.acquirer_nsu) as tran_acquirer_nsu,                
                GROUP_CONCAT(t.acquirer_tid) as tran_acquirer_tid,                
                GROUP_CONCAT(t.acquirer_auth_code) as tran_acquirer_auth_code,                
                GROUP_CONCAT(t.type) as tran_type,
                GROUP_CONCAT(t.status) as tran_status,
                GROUP_CONCAT(t.created_at) as tran_created_at,
                GROUP_CONCAT(t.boleto_url) as tran_boleto_url,
                GROUP_CONCAT(t.card_data SEPARATOR '---') as tran_card_data,
                GROUP_CONCAT(t.transaction_data SEPARATOR '---') as tran_data
            FROM
                $chargeTable as c 
                LEFT JOIN $transactionTable as t  
                  ON c.pagarme_id = t.charge_id 
            WHERE c.order_id = '$id'
            GROUP BY c.id;
        ";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return [];
        }

        $factory = new ChargeFactory();

        $charges = [];
        foreach ($result->rows as &$row) {
            $row['tran_card_data'] = StringFunctionsHelper::removeLineBreaks(
                $row['tran_card_data']
            );

            $row['tran_data'] = StringFunctionsHelper::removeLineBreaks(
                $row['tran_data']
            );

            $charges[] = $factory->createFromDbData($row);
        }

        return $charges;
    }

    /**
     *
     * @param  Charge $object
     * @throws \Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);

        $simpleObject = json_decode(json_encode($object));

        $query = "
          INSERT INTO 
            $chargeTable 
            (
                pagarme_id, 
                order_id, 
                code, 
                amount, 
                paid_amount,
                canceled_amount,
                refunded_amount,
                status,
                metadata,
                customer_id
            )
          VALUES 
        ";

        $metadata = json_encode($simpleObject->metadata);

        $query .= "
            (
                '{$simpleObject->pagarmeId}',
                '{$simpleObject->orderId}',
                '{$simpleObject->code}',
                {$simpleObject->amount},
                {$simpleObject->paidAmount},
                {$simpleObject->canceledAmount},
                {$simpleObject->refundedAmount},
                '{$simpleObject->status}',
                '{$metadata}',
                '{$simpleObject->customerId}'
            );
        ";

        $this->db->query($query);

        $transactionRepository = new TransactionRepository();
        foreach ($object->getTransactions() as $transaction) {
            $transactionRepository->save($transaction);
            $object->updateTransaction($transaction, true);
        }
    }

    protected function update(AbstractEntity &$object)
    {
        $charge = json_decode(json_encode($object));
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);

        $metadata = json_encode($charge->metadata);

        $query = "
            UPDATE $chargeTable SET
              amount = {$charge->amount},
              paid_amount = {$charge->paidAmount},                         
              refunded_amount = {$charge->refundedAmount},                         
              canceled_amount = {$charge->canceledAmount},
              status = '{$charge->status}',
              metadata = '{$metadata}',
              customer_id = '{$charge->customerId}'
            WHERE id = {$charge->id}
        ";

        $this->db->query($query);

        //update Transactions;
        $transactionRepository = new TransactionRepository();
        foreach ($object->getTransactions() as $transaction) {
            $transactionRepository->save($transaction);
            $object->updateTransaction($transaction, true);
        }
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $id = $pagarmeId->getValue();

        $this->db->query("SET group_concat_max_len = 8096;");

        $query = "
            SELECT 
                c.*, 
                GROUP_CONCAT(t.id) as tran_id, 
                GROUP_CONCAT(t.pagarme_id) as tran_pagarme_id,
                GROUP_CONCAT(t.charge_id) as tran_charge_id,
                GROUP_CONCAT(t.amount) as tran_amount,
                GROUP_CONCAT(t.paid_amount) as tran_paid_amount,
                GROUP_CONCAT(t.acquirer_name) as tran_acquirer_name,                
                GROUP_CONCAT(t.acquirer_message) as tran_acquirer_message,                
                GROUP_CONCAT(t.acquirer_nsu) as tran_acquirer_nsu,                
                GROUP_CONCAT(t.acquirer_tid) as tran_acquirer_tid,                
                GROUP_CONCAT(t.acquirer_auth_code) as tran_acquirer_auth_code,                
                GROUP_CONCAT(t.type) as tran_type,
                GROUP_CONCAT(t.status) as tran_status,
                GROUP_CONCAT(t.created_at) as tran_created_at,
                GROUP_CONCAT(t.boleto_url) as tran_boleto_url,
                GROUP_CONCAT(t.card_data SEPARATOR '---') as tran_card_data,
                GROUP_CONCAT(t.transaction_data SEPARATOR '---') as tran_data
            FROM
                $chargeTable as c 
                LEFT JOIN $transactionTable as t  
                  ON c.pagarme_id = t.charge_id 
            WHERE c.pagarme_id = '$id'
            GROUP BY c.id;
        ";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new ChargeFactory();

        return $factory->createFromDbData($result->row);
    }

    /**
     * @param $code
     * @return Charge[]
     * @throws \Exception
     */
    public function findChargeWithOutOrder($code)
    {
        $chargeTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_CHARGE
        );

        $orderTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_ORDER
        );
        $code = filter_var($code, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "SELECT charge.* 
                    FROM `{$chargeTable}` as charge  
               LEFT JOIN `{$orderTable}` as o on charge.order_id = o.pagarme_id 
                   WHERE o.id is null 
                     AND charge.code = '{$code}'";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return [];
        }

        $factory = new ChargeFactory();
        $chargeList = [];
        foreach ($result->rows as $chargedDb) {
            $chargeList[] = $factory->createFromDbData($chargedDb);
        }

        return $chargeList;
    }

    /**
     * @param $code
     * @return Charge[]
     * @throws \Exception
     */
    public function findChargesByCode($code)
    {
        $chargeTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CHARGE);
        $transactionTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_TRANSACTION);

        $this->db->query("SET group_concat_max_len = 8096;");

        $query = "
            SELECT
                c.*,
                t.id as tran_id,
                t.pagarme_id as tran_pagarme_id,
                t.charge_id as tran_charge_id,
                t.amount as tran_amount,
                t.paid_amount as tran_paid_amount,
                t.acquirer_name as tran_acquirer_name,
                t.acquirer_message as tran_acquirer_message,
                t.acquirer_nsu as tran_acquirer_nsu,
                t.acquirer_tid as tran_acquirer_tid,
                t.acquirer_auth_code as tran_acquirer_auth_code,
                t.type as tran_type,
                t.status as tran_status,
                t.created_at as tran_created_at,
                t.boleto_url as tran_boleto_url,
                t.card_data as tran_card_data,
                t.transaction_data as tran_data
            FROM
                $chargeTable as c
                LEFT JOIN $transactionTable as t on c.pagarme_id = t.charge_id
            WHERE c.code = '$code'
            ORDER BY c.id DESC;
        ";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return [];
        }

        $factory = new ChargeFactory();

        $charges = [];
        foreach ($result->rows as &$row) {
            $row['tran_card_data'] = StringFunctionsHelper::removeLineBreaks(
                $row['tran_card_data']
            );

            $row['tran_data'] = StringFunctionsHelper::removeLineBreaks(
                $row['tran_data']
            );

            $charges[] = $factory->createFromDbData($row);
        }

        return $charges;
    }
}