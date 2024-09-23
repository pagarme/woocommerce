<?php

namespace Pagarme\Core\Webhook\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Webhook\Factories\WebhookFactory;

class WebhookRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "INSERT INTO $table (pagarme_id) VALUES ('{$object->getPagarmeId()->getValue()}')";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {

    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $objectId = filter_var($objectId, FILTER_SANITIZE_SPECIAL_CHARS);
        $query = "SELECT * FROM $table WHERE id = '$objectId'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new WebhookFactory();
            $webhook = $factory->createFromDbData($result->row);

            return $webhook;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        // TODO: Implement listEntities() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $id = $pagarmeId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_WEBHOOK);
        $query = "SELECT * FROM $table WHERE pagarme_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new WebhookFactory();
            $webhook = $factory->createFromDbData($result->row);

            return $webhook;
        }
        return null;
    }
}