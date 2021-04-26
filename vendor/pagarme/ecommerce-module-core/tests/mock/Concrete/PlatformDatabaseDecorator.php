<?php

namespace Pagarme\Core\Test\Mock\Concrete;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;

class PlatformDatabaseDecorator extends AbstractDatabaseDecorator
{

    public function getLastId()
    {
        return $this->db->lastInsertId();
    }

    protected function setTableArray()
    {
        $this->tableArray = [
            AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION =>
                'pagarme_module_core_configuration',

            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION =>
                'pagarme_module_core_recurrence_products_subscription',

            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS =>
                'pagarme_module_core_recurrence_subscription_repetitions',

            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS =>
                'pagarme_module_core_recurrence_sub_products',

            AbstractDatabaseDecorator::TABLE_RECURRENCE_CHARGE =>
                'pagarme_module_core_recurrence_charge',
        ];
    }

    protected function setTablePrefix()
    {
        // TODO: Implement setTablePrefix() method.
    }

    protected function doQuery($query)
    {
        $stmt = $this->db->prepare($query);
        $this->setLastInsertId($this->db->lastInsertId());
        return $stmt->execute();
    }

    /**
     *
     * @return array
     */
    protected function doFetch($query)
    {
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();

    }

    protected function formatResults($queryResult)
    {
        $retn = new \stdClass;
        $retn->num_rows = count($queryResult);
        $retn->row = array();
        if (!empty($queryResult)) {
            $retn->row = $queryResult[0];
        }
        $retn->rows = $queryResult;
        return $retn;
    }

    protected function setLastInsertId($insertId)
    {
        $this->db->lastInsertId = $insertId;
    }
}