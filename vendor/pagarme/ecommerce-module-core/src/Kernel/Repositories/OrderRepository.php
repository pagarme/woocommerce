<?php

namespace Pagarme\Core\Kernel\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

final class OrderRepository extends AbstractRepository
{
    /**
     *
     * @param  Order $object
     * @throws \Exception
     */
    protected function create(AbstractEntity &$object)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $order = json_decode(json_encode($object));

        $query = "
          INSERT INTO $orderTable (`pagarme_id`, `code`, `status`) 
          VALUES ('{$order->pagarmeId}', '{$order->code}', '{$order->status}');
         ";

        $this->db->query($query);

        $chargeRepository = new ChargeRepository();
        foreach ($object->getCharges() as $charge) {
            $chargeRepository->save($charge);
            $object->updateCharge($charge, true);
        }
    }

    /**
     *
     * @param  Order $object
     * @throws \Exception
     */
    protected function update(AbstractEntity &$object)
    {
        $order = json_decode(json_encode($object));
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "
            UPDATE $orderTable SET
              status = '{$order->status}'
            WHERE id = {$order->id}
        ";

        $this->db->query($query);

        //update Charges;
        $chargeRepository = new ChargeRepository();
        foreach ($object->getCharges() as $charge) {
            $chargeRepository->save($charge);
            $object->updateCharge($charge, true);
        }
    }

    public function findByCode($codeId)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);
        $codeId = filter_var($codeId, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = '{$codeId}';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    /**
     * @param AbstractValidString $pagarmeId
     * @return Order|null
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $id = $pagarmeId->getValue();
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);

        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE pagarme_id = '{$id}';";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function findByPlatformId($platformID)
    {
        $orderTable = $this->db->getTable(AbstractDatabaseDecorator::TABLE_ORDER);
        $platformID = filter_var($platformID, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "SELECT * FROM `$orderTable` ";
        $query .= "WHERE code = '{$platformID}' ORDER BY id DESC;";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $factory = new OrderFactory();

        return $factory->createFromDbData($result->row);
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }
}