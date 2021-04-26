<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Factories\RepetitionFactory;
use Pagarme\Core\Recurrence\Factories\SubProductFactory;
use Pagarme\Core\Recurrence\Interfaces\RecurrenceEntityInterface;

class SubProductRepository extends AbstractRepository
{

    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            INSERT INTO $table (
                `product_id`,
                `product_recurrence_id`,
                `recurrence_type`,
                `cycles`,
                `quantity`,
                `pagarme_id`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getProductRecurrenceId()}',
                '{$object->getRecurrenceType()}',
                '{$object->getCycles()}',
                '{$object->getQuantity()}',
                '{$object->getPagarmeIdValue()}'
            )
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "
            UPDATE $table SET
                `product_id` = '{$object->getProductId()}',
                `product_recurrence_id` = '{$object->getProductRecurrenceId()}',
                `recurrence_type` = '{$object->getRecurrenceType()}',
                `cycles` = '{$object->getCycles()}',
                `quantity` = '{$object->getQuantity()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $this->db->query($query);
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        // TODO: Implement findByPagarmeId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByRecurrence($recurrenceEntity)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = {$recurrenceEntity->getId()}" .
            " AND recurrence_type = '{$recurrenceEntity->getRecurrenceType()}'";

        $result = $this->db->fetch($query);
        $subProducts = [];

        if ($result->num_rows === 0) {
            return $subProducts;
        }

        foreach ($result->rows as $row) {
            $subProductFactory = new SubProductFactory();
            $subProducts[] = $subProductFactory->createFromDbData($row);
        }

        return $subProducts;
    }

    public function findByRecurrenceIdAndProductId($recurrenceId, $productId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS);

        $query = "SELECT * FROM $table" .
            " WHERE product_recurrence_id = {$recurrenceId}" .
            " AND product_id = '{$productId}'";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $subProductFactory = new SubProductFactory();
        return $subProductFactory->createFromDbData($result->row);
    }
}