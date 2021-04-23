<?php

namespace Pagarme\Core\Payment\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Factories\CustomerFactory;


final class CustomerRepository extends AbstractRepository
{
    public function findByCode($customerCode)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "SELECT * FROM $table WHERE code = '$customerCode'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new CustomerFactory();
            $customer = $factory->createFromDbData($result->row);

            return $customer;
        }
        return null;
    }

    /** @param Customer $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);

        $obj = json_decode(json_encode($object));

        $query = "
          INSERT INTO $table 
            (
                code, 
                pagarme_id
            )
          VALUES 
            (
                '{$obj->code}',
                '{$obj->pagarmeId}'
            )          
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        // TODO: Implement update() method.
    }

    public function deleteByCode($customerCode)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "DELETE FROM $table WHERE code = '$customerCode'";

        return $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $id = $pagarmeId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_CUSTOMER);
        $query = "SELECT * FROM $table WHERE pagarme_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new CustomerFactory();
            $customer = $factory->createFromDbData(end($result->rows));

            return $customer;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}