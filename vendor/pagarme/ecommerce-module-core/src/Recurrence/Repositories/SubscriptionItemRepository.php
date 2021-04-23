<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\Aggregates\Subscription;
use Pagarme\Core\Recurrence\Factories\SubscriptionItemFactory;

class SubscriptionItemRepository extends AbstractRepository
{
    /**
     * @param AbstractValidString $pagarmeId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $pagarmeId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}                  
             WHERE pagarme_id = '{$id}'             
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();
        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    public function findBySubscriptionId(AbstractValidString $pagarmeId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );
        $id = $pagarmeId->getValue();

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}
             WHERE subscription_id = '{$id}'
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $listSubscriptionItem = [];
        foreach ($result->rows as $row) {
            $subscriptionItem = $factory->createFromDbData($row);
            $listSubscriptionItem[] = $subscriptionItem;
        }

        return $listSubscriptionItem;
    }

    public function findByCode($code)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
            SELECT *
              FROM {$subscriptionItemTable}                  
             WHERE code = '{$code}'             
        ";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
          INSERT INTO 
            $subscriptionItemTable 
            (
                pagarme_id, 
                subscription_id,
                code,                
                quantity
            )
          VALUES
        ";

        $query .= "
            (
                '{$object->getPagarmeId()->getValue()}',
                '{$object->getSubscriptionId()->getValue()}',
                '{$object->getCode()}',
                '{$object->getQuantity()}'
            );
        ";

        $this->db->query($query);
    }

    /**
     * @param Subscription|AbstractEntity $object
     * @throws Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "
            UPDATE {$subscriptionItemTable} SET
              pagarme_id = '{$object->getPagarmeId()->getValue()}',
              code = '{$object->getCode()}',
              subscription_id = '{$object->getSubscriptionId()->getValue()}',
              quantity = '{$object->getQuantity()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $objectId
     * @return AbstractEntity|Subscription|null
     * @throws InvalidParamException
     */
    public function find($objectId)
    {
        $subscriptionItemTable = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM
        );

        $query = "SELECT * FROM {$subscriptionItemTable} WHERE id = '" . $objectId . "'";
        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new SubscriptionItemFactory();

        $subscriptionItem = $factory->createFromDbData($result->row);

        return $subscriptionItem;
    }

    /**
     * @param $limit
     * @param $listDisabled
     * @return Subscription[]|array
     * @throws InvalidParamException
     */
    public function listEntities($limit, $listDisabled)
    {
        //@TODO Implement listEntities method
    }
}
