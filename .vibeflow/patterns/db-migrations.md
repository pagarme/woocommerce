---
tags: [migrations, database, wpdb, schema]
modules: [src/DB/Migration/, src/DB/Migration/Migrations/]
applies_to: [migrations]
confidence: inferred
---
# Pattern: Database Migrations

<!-- vibeflow:auto:start -->
## What

Schema changes after the initial install are one class per change in `src/DB/Migration/Migrations/`, discovered by glob, sorted, and applied on every request through `Migrator::execute()`. Each migration answers two questions itself: *has this already been applied?* (`validate()`) and *what does it do?* (`apply()`). The applied set is recorded in `Config`, not in a schema table.

This is **not** where initial tables come from. The seven `pagarme_module_core_*` tables are created by raw `dbDelta()` calls in `wcmpOnActivation()` in `woo-pagarme-payments.php`, which only runs on plugin activation.

## Where

`src/DB/Migration/` — `Migrator`, `AbstractMigration`, `MigrationInterface`, and `Migrations/`. Invoked from `wcmpLoadInstances()` on `plugins_loaded`, immediately after `Core::instance()`.

## The Pattern

**1. Filename encodes the order: `YYYY-MM-DD-NNNN-Description.php`,** and the class name is the `Description` part only:

```
src/DB/Migration/Migrations/2021-10-28-0000-TypeInSavedCardTable.php
  → class TypeInSavedCardTable
```

`Migrator::sort()` orders by that filename, which is why the date prefix exists — the class name alone carries no ordering.

**2. A migration extends `AbstractMigration` and implements `MigrationInterface`,** declaring the table and column it touches as constants:

```php
class TypeInSavedCardTable extends AbstractMigration implements MigrationInterface
{
    /** @var string */
    const COLUMN_TYPE = 'type';

    /** @var string */
    const TABLE = 'pagarme_module_core_saved_card';
```

`AbstractMigration::__construct` supplies `$this->wpdb` (from the global) and `$this->settings` (a `Config`), so migrations are constructed with no arguments — required by the discovery loop.

**3. `validate()` inspects the live schema and returns `true` when the migration still needs to run.** It must be safe to call on every request:

```php
public function validate(): bool
{
    $table_name = $this->wpdb->prefix . self::TABLE;
    $column_name = self::COLUMN_TYPE;
    $row = $this->wpdb->get_results(  "SHOW COLUMNS FROM $table_name LIKE '$column_name'");
    if (empty($row)) {
        return true;
    }
    return false;
}
```

**4. `apply()` re-checks `validate()`, then mutates, swallowing failures** so a broken migration cannot white-screen the store:

```php
public function apply(): void
{
    $table_name = $this->wpdb->prefix . self::TABLE;
    $column_name = self::COLUMN_TYPE;
    if ($this->validate()) {
        try {
            $query = "ALTER TABLE {$table_name} ADD {$column_name} varchar(30) not null comment 'card type' AFTER brand";
            $this->wpdb->query($query);
            $query = "UPDATE {$table_name} SET type = 'credit_card'";
            $this->wpdb->query($query);
        } catch (\Exception $e) {}
    }
}
```

Table names are always `$this->wpdb->prefix . self::TABLE` — never hardcoded with a prefix.

**5. Bookkeeping lives in `AbstractMigration` and writes to `Config`,** under the `migrations` key, as a list of fully-qualified class names:

```php
const MIGRATION_SETTINGS = 'migrations';

public function canApply(MigrationInterface $migration): bool
{
    if ( $this->settings->getData(self::MIGRATION_SETTINGS) &&
        is_array($this->settings->getData(self::MIGRATION_SETTINGS)) &&
        in_array(get_class($migration), $this->settings->getData(self::MIGRATION_SETTINGS)) ) {
        return false;
    }
    return true;
}

public function registerMigration(MigrationInterface $migration)
{
    $migrationSetting = $this->settings->getData(self::MIGRATION_SETTINGS);
    if (!is_array($migrationSetting)) {
        $migrationSetting = [];
    }
    $migrationSetting[] = get_class($migration);
    $this->settings->setData(self::MIGRATION_SETTINGS, $migrationSetting);
    $this->settings->save();
}
```

**6. `Migrator::execute()` combines the two signals.** `validate()` returning true means "not applied", which also *un*-registers a migration wrongly recorded as done — a self-healing step:

```php
foreach ($migrationsClasses as $class) {
    /** @var MigrationInterface $migration */
    $migration = new $class;
    if ($migration->validate()) {
        $migration->unregisterMigration($migration);
    }
    if ($migration->canApply($migration)) {
        $migration->apply();
        $migration->registerMigration($migration);
    }
}
```

**7. Ordering dependencies are declarative** via `getDependencies(): array` (returns `[]` by default) — override it when a migration must follow another regardless of filename order.

**8. Initial table creation is separate.** One `wcmpCreate<Thing>Table($upgradePath)` function per table in the main plugin file, each `require_once`-ing `wp-admin/includes/upgrade.php` and calling `dbDelta` with `CREATE TABLE IF NOT EXISTS` and `$wpdb->get_charset_collate()`:

```php
function wcmpCreateCoreChargeTable($upgradePath)
{
    global $wpdb;

    require_once $upgradePath;

    $charset = $wpdb->get_charset_collate();
    $tableName = $wpdb->prefix . 'pagarme_module_core_charge';

    $query = "CREATE TABLE IF NOT EXISTS {$tableName}
    (
        id              int unsigned auto_increment comment 'ID' primary key,
        pagarme_id      varchar(19)  not null comment 'format: ch_xxxxxxxxxxxxxxxx',
        ...
    ) comment 'Charge Table' {$charset};";

    dbDelta($query);
}
```

## Rules

- New migration = new file `YYYY-MM-DD-NNNN-DescriptiveName.php` in `Migrations/`, class `DescriptiveName`, extending `AbstractMigration` and implementing `MigrationInterface`. Nothing to register.
- The class must be constructible with no arguments. Do not add required constructor parameters.
- `validate()` must be idempotent, cheap, and inspect the actual schema (`SHOW COLUMNS`, `SHOW TABLES`) — it runs on every request. It returns `true` when the change is **still needed**.
- `apply()` re-checks `validate()` before mutating and wraps the mutation in `try/catch`. A failing migration must not break page loads.
- Table and column names are constants on the migration; table names are always built as `$this->wpdb->prefix . self::TABLE`.
- Use `$this->wpdb->query()` for DDL; use `$this->wpdb->prepare()` for any value that isn't a compile-time constant.
- Override `getDependencies()` when filename order is not sufficient.
- Creating a **new** table belongs in `wcmpOnActivation()` via a `wcmpCreate*Table()` function **and** in `WoocommerceDatabaseDecorator::setTableArray()` if the core lib will use it — plus a migration, since activation does not re-run on update.

## Examples from this codebase

File: `src/DB/Migration/Migrations/2021-10-28-0000-TypeInSavedCardTable.php` — the only migration in the repo and the reference: two constants, a `SHOW COLUMNS` guard, an `ALTER` plus a data backfill.

File: `src/DB/Migration/AbstractMigration.php` — `canApply`/`registerMigration`/`unregisterMigration` against `Config['migrations']`, and the `wpdb` + `Config` constructor.

File: `src/DB/Migration/Migrator.php` — discovery, sort, and the `validate` → `unregister` → `canApply` → `apply` → `register` sequence.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **Migrations run on every front-end request.** `Migrator::execute()` is called from `plugins_loaded`, so `autoLoad()` globs the directory and every migration's `validate()` issues a `SHOW COLUMNS`/`SHOW TABLES` query on every page load. With one migration this is one extra query per request; it scales linearly and there is no short-circuit.
- **State is duplicated and can disagree.** `Config['migrations']` says what ran; `validate()` says what the schema looks like. `Migrator` reconciles them by trusting `validate()`, so a migration whose `validate()` is wrong will re-run forever or never run at all.
- **`validate()` interpolates table and column names straight into SQL** (`"SHOW COLUMNS FROM $table_name LIKE '$column_name'"`). They are class constants today, so not injectable, but the habit is unsafe and `$wpdb->prepare()` is right there.
- **`catch (\Exception $e) {}` with an empty body** discards the reason a migration failed. There is no log line, so a partially-applied schema is invisible until something else breaks. At minimum log via `$this->settings->log()`.
- **`registerMigration()` calls `$this->settings->save()`**, which writes the whole plugin option *and* pushes it into the core lib — a heavy write for a bookkeeping flag, executed inside the request that happens to trigger the migration.
- **`unregisterMigration()` calls `save()` even when it changed nothing** (early `return` only covers the non-array case).
- **The DDL for the core tables exists in two unlinked places** — `wcmpCreate*Table()` in `woo-pagarme-payments.php` and `WoocommerceDatabaseDecorator::setTableArray()` — and `setTableArray()` lists recurrence tables (`pagarme_module_core_recurrence_*`) that no activation function creates. Those come from the core lib's own installer; the split makes it genuinely hard to tell who owns a given table.
