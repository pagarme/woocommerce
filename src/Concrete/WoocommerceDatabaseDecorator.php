<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Abstractions\AbstractDatabaseDecorator;

final class WoocommerceDatabaseDecorator extends AbstractDatabaseDecorator
{
    protected function setTableArray()
    {
        $this->tableArray = [
            AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION =>
            $this->getTableName('pagarme_module_core_configuration'),

            AbstractDatabaseDecorator::TABLE_WEBHOOK =>
            $this->getTableName('pagarme_module_core_webhook'),

            AbstractDatabaseDecorator::TABLE_ORDER =>
            $this->getTableName('pagarme_module_core_order'),

            AbstractDatabaseDecorator::TABLE_CHARGE =>
            $this->getTableName('pagarme_module_core_charge'),

            AbstractDatabaseDecorator::TABLE_TRANSACTION =>
            $this->getTableName('pagarme_module_core_transaction'),

            AbstractDatabaseDecorator::TABLE_SAVED_CARD =>
            $this->getTableName('pagarme_module_core_saved_card'),

            AbstractDatabaseDecorator::TABLE_CUSTOMER =>
            $this->getTableName('pagarme_module_core_customer'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_PLAN =>
            $this->getTableName('pagarme_module_core_recurrence_products_plan'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_PRODUCTS_SUBSCRIPTION =>
            $this->getTableName('pagarme_module_core_recurrence_products_subscription'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUB_PRODUCTS =>
            $this->getTableName('pagarme_module_core_recurrence_sub_products'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_CHARGE =>
            $this->getTableName('pagarme_module_core_recurrence_charge'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION =>
            $this->getTableName('pagarme_module_core_recurrence_subscription'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_REPETITIONS =>
            $this->getTableName('pagarme_module_core_recurrence_subscription_repetitions'),

            AbstractDatabaseDecorator::TABLE_RECURRENCE_SUBSCRIPTION_ITEM =>
            $this->getTableName('pagarme_module_core_recurrence_subscription_items'),

            AbstractDatabaseDecorator::TABLE_HUB_INSTALL_TOKEN =>
            $this->getTableName('pagarme_module_core_hub_install_token')
        ];
    }

    protected function doQuery($query)
    {
        global $wpdb;
        $wpdb->query($query);
        $wpdb->insert_id;
    }

    protected function formatResults($queryResult)
    {
        $retn = new \stdClass;
        $retn->num_rows = count($queryResult);
        $retn->row = array();
        if (!empty($queryResult)) {
            $retn->row = (array) $queryResult[0];
        }
        $retn->rows = json_decode(
            json_encode($queryResult),
            true
        );
        return $retn;
    }

    protected function doFetch($query)
    {
        global $wpdb;

        return $wpdb->get_results($query);
    }

    public function getLastId()
    {
        global $wpdb;

        return $wpdb->insert_id;
    }

    protected function setTablePrefix()
    {
        // The getTableName method already retrieves the table with the prefix.
        $this->tablePrefix = '';
    }

    protected function setLastInsertId($lastInsertId)
    {
        // Not necessary to be implemented on Woocommerce, there is no such a concept
    }

    private function getTableName($table)
    {
        global $wpdb;

        return $wpdb->prefix . $table;
    }
}
