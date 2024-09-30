<?php

namespace Pagarme\Core\Payment\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Aggregates\SavedCard;
use Pagarme\Core\Payment\Factories\SavedCardFactory;

final class SavedCardRepository extends AbstractRepository
{
    /**
     * @param CustomerId $customerId
     * @return Savedcard[]
     * @throws \Exception
     */
    public function findByOwnerId(CustomerId $customerId, $cardType = null, $includeEmptyType = true)
    {
        $id = $customerId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "SELECT * FROM $table WHERE owner_id = '$id'";
        if ($cardType && is_array($cardType)) {
            if ($includeEmptyType) {
                array_push($cardType, '');
            }
            $cardType = implode('\', \'', $cardType);
            $query .= " AND type IN ('$cardType')";
        }
        $result = $this->db->fetch($query);

        $factory = new SavedCardFactory();
        $savedCards = [];
        foreach ($result->rows as $row) {
            $savedCards[] = $factory->createFromDbData($row);
        }
        return $savedCards;
    }

    /** @param SavedCard $object */
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);

        $obj = json_decode(json_encode($object));

        if ($object->getOwnerId() === null) {
            throw new InvalidParamException('
            You can\'t save a card without an onwer!' , null
            );
        }

        $query = "
          INSERT INTO $table 
            (
                pagarme_id, 
                owner_id,
                owner_name,
                first_six_digits, 
                last_four_digits,
                brand,
                type,
                created_at
            )
          VALUES 
            (
                '{$obj->pagarmeId}',
                '{$obj->ownerId}',
                '{$obj->ownerName}',
                '{$obj->firstSixDigits}',
                '{$obj->lastFourDigits}',
                '{$obj->brand}',
                '{$obj->type}',
                '{$obj->createdAt}'
            )          
        ";

        $this->db->query($query);
    }

    protected function update(AbstractEntity &$object)
    {
        // TODO: Implement update() method.
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "DELETE FROM $table where id = {$object->getId()}";

        $this->db->query($query);
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = '$objectId'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new SavedCardFactory();
            $savedCard = $factory->createFromDbData($result->row);

            return $savedCard;
        }
        return null;
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $id = $pagarmeId->getValue();
        $table = $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);
        $query = "SELECT * FROM $table WHERE pagarme_id = '$id'";

        $result = $this->db->fetch($query);

        if ($result->num_rows > 0) {
            $factory = new SavedCardFactory();
            $savedCard = $factory->createFromDbData($result->row);

            return $savedCard;
        }
        return null;
    }

    public function listEntities($limit, $listDisabled)
    {
        $table =
            $this->db->getTable(AbstractDatabaseDecorator::TABLE_SAVED_CARD);

        $query = "SELECT * FROM `$table` as t";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $factory = new SavedCardFactory();

        $listSavedCard = [];
        foreach ($result->rows as $row) {
            $savedCard = $factory->createFromDBData($row);
            $listSavedCard[] = $savedCard;
        }

        return $listSavedCard;
    }
}
