<?php

namespace Pagarme\Core\Hub\Repositories;

use Pagarme\Aggregates\IAggregateRoot;
use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Hub\Aggregates\InstallToken;
use Pagarme\Core\Hub\Factories\InstallTokenFactory;

final class InstallTokenRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $stdObject = json_decode(json_encode($object));
        $token = $stdObject->token;
        $used = $stdObject->used ? 'true' : 'false';
        $created_at_timestamp = $stdObject->createdAtTimestamp;
        $expire_at_timestamp = $stdObject->expireAtTimestamp;

        $query = "
             INSERT INTO `$table`" .
            " (token, used, created_at_timestamp, expire_at_timestamp) " .
            " VALUES ('$token',$used,$created_at_timestamp,$expire_at_timestamp)"
          ;

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $stdObject = json_decode(json_encode($object));
        $token = $stdObject->token;
        $used = $stdObject->used ? 'true' : 'false';
        $created_at_timestamp = $stdObject->createdAtTimestamp;
        $expire_at_timestamp = $stdObject->expireAtTimestamp;

        $query = "UPDATE `$table`" .
            " SET " .
            "
                token = '$token' ,
                used = $used ,
                created_at_timestamp = $created_at_timestamp ,
                expire_at_timestamp = $expire_at_timestamp
            " .
            " WHERE id = {$stdObject->id}";

        $this->db->query($query);
    }

    public function delete(AbstractEntity $object)
    {
        // TODO: Implement delete() method.
    }

    public function deleteAllInactive()
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $currentTime = time();
        $query = "DELETE FROM `$table`"
            . " WHERE used <> 1";

        $this->db->query($query);
    }

    public function find($objectId)
    {
        // TODO: Implement find() method.
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $token = $pagarmeId->getValue();

        $query = "SELECT * FROM `$table` as t ";
        $query .= "WHERE t.token = '$token';";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new InstallTokenFactory();
            return $factory->createFromDBData($result->row);
        }

        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN);

        $query = "SELECT * FROM `$table` as t"
            . " WHERE used = 0";

        if (!$listDisabled) {
            $query .= " AND t.expire_at_timestamp > " . time();
        }

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $factory = new InstallTokenFactory();
        $installTokens = [];

        foreach ($result->rows as $row) {
            $installToken = $factory->createFromDBData($row);
            $installTokens[] = $installToken;
        }

        return $installTokens;
    }
}
