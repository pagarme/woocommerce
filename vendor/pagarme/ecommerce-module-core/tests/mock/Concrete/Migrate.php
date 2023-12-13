<?php

namespace Pagarme\Core\Test\Mock\Concrete;

class Migrate
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setUpConfiguration()
    {
        $this->runConfigurationMigration();
    }

    public function up()
    {
        $this->upRecurrenceProductSubscription();
        $this->upRecurrenceSubscriptionRepetitions();
        $this->upRecurrenceSubProduct();
        $this->upRecurrenceCharge();
    }

    public function down()
    {
        $this->downConfigurationMigration();
        $this->downRecurrenceProductSubscription();
        $this->downRecurrenceSubscriptionRepetitions();
        $this->downRecurrenceSubProduct();
        $this->downRecurrenceCharge();
    }

    public function runConfigurationMigration()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS pagarme_module_core_configuration (
                      id INTEGER PRIMARY KEY,
                      data TEXT,
                      store_id TEXT)");

        $insert = "INSERT INTO pagarme_module_core_configuration (data, store_id)
                VALUES  (:data, :store_id)";

        $stmt = $this->db->prepare($insert);

        $config = json_encode([
            "enabled" => true
        ]);

        $stmt->bindValue(':data', $config, SQLITE3_TEXT);
        $stmt->bindValue(':store_id', '1', SQLITE3_TEXT);

        $stmt->execute();
    }

    public function downConfigurationMigration()
    {
        $this->db->exec("DROP TABLE IF EXISTS main.pagarme_module_core_configuration");
    }

    public function upRecurrenceProductSubscription()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS pagarme_module_core_recurrence_products_subscription (
                      id INTEGER PRIMARY KEY,
                      product_id INTEGER NULLABLE ,
                      credit_card TEXT NULLABLE,
                      allow_installments TEXT NULLABLE,
                      boleto BOOLEAN NULLABLE,
                      sell_as_normal_product TEXT NULLABLE,
                      billing_type TEXT NULLABLE NULLABLE,
                      created_at TIMESTAMP,
                      updated_at TIMESTAMP,
                      apply_discount_in_all_product_cycles INTEGER NULLABLE)");

    }

    public function downRecurrenceProductSubscription()
    {
        $this->db->exec("DROP TABLE main.pagarme_module_core_recurrence_products_subscription");
    }

    public function upRecurrenceSubscriptionRepetitions()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS pagarme_module_core_recurrence_subscription_repetitions (
                      id INTEGER PRIMARY KEY,
                      subscription_id INTEGER NULLABLE ,
                      `interval` TEXT NULLABLE,
                      interval_count INTEGER NULLABLE,
                      recurrence_price INTEGER NULLABLE,
                      cycles INTEGER NULLABLE,
                      created_at TIMESTAMP,
                      updated_at TIMESTAMP)");
    }

    public function downRecurrenceSubscriptionRepetitions()
    {
        $this->db->exec("DROP TABLE main.pagarme_module_core_recurrence_subscription_repetitions");
    }

    public function upRecurrenceSubProduct()
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS pagarme_module_core_recurrence_sub_products (
                      id INTEGER PRIMARY KEY,
                      product_id INTEGER,
                      product_recurrence_id INTEGER,
                      recurrence_type TEXT,
                      cycles INTEGER NULLABLE,
                      quantity INTEGER NULLABLE,
                      trial_period_days INTEGER NULLABLE,
                      pagarme_id    TEXT,
                      created_at TIMESTAMP,
                      updated_at TIMESTAMP)");
    }

    public function downRecurrenceSubProduct()
    {
        $this->db->exec("DROP TABLE main.pagarme_module_core_recurrence_sub_products");
    }

    public function upRecurrenceCharge()
    {
        $sql = "
        create table if not exists pagarme_module_core_recurrence_charge
        (
            id              INTEGER PRIMARY KEY,
            pagarme_id    TEXT,
            subscription_id TEXT,
            invoice_id      TEXT,
            code            TEXT,
            amount          INTEGER,
            paid_amount     INTEGER,
            canceled_amount INTEGER,
            refunded_amount INTEGER,
            status          TEXT,
            metadata        TEXT,
            payment_method  TEXT,
            boleto_link     TEXT,
            cycle_start     TIMESTAMP,
            cycle_end       TIMESTAMP
        )
            ;
        ";

        $this->db->exec($sql);
    }

    public function downRecurrenceCharge()
    {
        $this->db->exec("DROP TABLE main.pagarme_module_core_recurrence_charge");
    }
}
