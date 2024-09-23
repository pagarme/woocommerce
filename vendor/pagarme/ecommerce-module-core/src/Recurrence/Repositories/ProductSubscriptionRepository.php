<?php

namespace Pagarme\Core\Recurrence\Repositories;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Abstractions\AbstractRepository;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Factories\ProductSubscriptionFactory;

class ProductSubscriptionRepository extends AbstractRepository
{
    protected function create(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "
            INSERT INTO $table (
                `product_id`,
                `credit_card`,
                `allow_installments`,
                `boleto`,
                `sell_as_normal_product`,
                `billing_type`,
                `apply_discount_in_all_product_cycles`
            ) VALUES (
                '{$object->getProductId()}',
                '{$object->getCreditCard()}',
                '{$object->getAllowInstallments()}',
                '{$object->getBoleto()}',
                '{$object->getSellAsNormalProduct()}',
                '{$object->getBillingType()}',
                '{$object->getApplyDiscountInAllProductCycles()}'
            )
        ";

        $this->db->query($query);

        $object->setId($this->db->getLastId());

        $this->saveRepetitions($object);
    }

    protected function update(AbstractEntity &$object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "
            UPDATE $table SET
                `product_id` = '{$object->getProductId()}',
                `credit_card` = '{$object->getCreditCard()}',
                `allow_installments` = '{$object->getAllowInstallments()}',
                `boleto` = '{$object->getBoleto()}',
                `sell_as_normal_product` = '{$object->getSellAsNormalProduct()}',
                `billing_type` = '{$object->getBillingType()}',
                `apply_discount_in_all_product_cycles` = '{$object->getApplyDiscountInAllProductCycles()}'
            WHERE id = {$object->getId()}
        ";

        $this->db->query($query);

        $this->saveRepetitions($object);
    }

    public function saveRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as &$repetition) {
            $repetition->setId(null);
            $repetition->setSubscriptionId($object->getId());
            $repetition = $repetitionRepository->save($repetition);
        }
    }

    public function delete(AbstractEntity $object)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );

        $query = "DELETE FROM $table WHERE id = {$object->getId()}";

        $result = $this->db->query($query);

        $this->deleteRepetitions($object);

        return $result;
    }

    public function deleteRepetitions(AbstractEntity &$object)
    {
        $repetitionRepository = new RepetitionRepository();
        foreach ($object->getRepetitions() as $repetition) {
            $repetitionRepository->delete($repetition);
        }
    }

    public function find($objectId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );
        $objectId = filter_var($objectId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = $objectId";

        $result = $this->db->fetch($query);

        if ($result->num_rows === 0) {
            return null;
        }

        $productSubscriptionFactory = new ProductSubscriptionFactory();
        $productSubscription =
            $productSubscriptionFactory->createFromDbData($result->row);

        $repetitionRepository = new RepetitionRepository();
        $repetitions = $repetitionRepository->findBySubscriptionId($objectId);

        foreach ($repetitions as $repetition) {
            $productSubscription->addRepetition($repetition);
        }

        return $productSubscription;
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        // TODO: Implement findByPagarmeId() method.
    }

    public function listEntities($limit, $listDisabled)
    {
        $table = $this->db->getTable(
                AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
            );

        $query = "SELECT * FROM `$table` as t";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->db->fetch($query . ";");

        $productSubscriptions = [];
        foreach ($result->rows as $row) {

            $factory = new ProductSubscriptionFactory();
            $productSubscription = $factory->createFromDBData($row);

            $repetitionRepository = new RepetitionRepository();
            $repetitions = $repetitionRepository->findBySubscriptionId(
                $productSubscription->getId()
            );

            $productSubscription->setRepetitions($repetitions);

            $productSubscriptions[] = $productSubscription;
        }

        return $productSubscriptions;
    }

    public function findByProductId($productId)
    {
        $table = $this->db->getTable(
            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION
        );
        $productId = filter_var($productId, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE product_id = $productId";

        $result = $this->db->fetch($query);
        if ($result->num_rows === 0) {
            return null;
        }
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $productSubscription =
            $productSubscriptionFactory->createFromDbData($result->row);

        $repetitionRepository = new RepetitionRepository();
        $repetitions = $repetitionRepository->findBySubscriptionId(
            $productSubscription->getId()
        );

        foreach ($repetitions as $repetition) {
            $productSubscription->addRepetition($repetition);
        }

        return $productSubscription;
    }
}
