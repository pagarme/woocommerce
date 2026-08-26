---
tags: [registry, reflection, autoload, extensibility, plugin-architecture]
modules: [src/Controller/, src/DB/Migration/, src/Model/Payment/, src/Model/]
applies_to: [controllers, models, migrations, configs]
confidence: inferred
---
# Pattern: Runtime Class Discovery

<!-- vibeflow:auto:start -->
## What

Five different extension points in this plugin are populated by scanning a directory at runtime rather than by a registry list. The recipe is always: `glob()` + `include_once` the directory, then filter classes by subclass/interface/attribute. **Dropping a file in the right directory is the registration.** There is no list to update, and conversely, no list to consult when you wonder what is loaded.

Recognizing this pattern is what stops you from hunting for a registry that doesn't exist.

## Where

| What | Discovery site | Filter |
|---|---|---|
| Payment gateways | `Controller\Settings::getGateways()` | `is_subclass_of($class, AbstractGateway::class)` |
| DB migrations | `DB\Migration\Migrator::execute()` | reflection over `Migrations/*.php`, then sorted |
| Card brands | `Model\Payment\CreditCard\Brands::getBrands()` | `implementsInterface(BrandsInterface::class)` |
| Voucher brands / boleto banks | `Model\Payment\Voucher\Brands`, `Model\Payment\Billet\Banks` | same shape |
| Blocks checkout methods | `Model\FeatureCompatibilization::addSupportedBlocks()` | `ClassFinder` over the namespace + abstract filter |

## The Pattern

**1. The `glob` + `include_once` autoloader.** Every site has a private `autoLoad()` that is called first:

```php
public function autoLoad()
{
    foreach(glob( __DIR__ . '/Migrations/*.php') as $file) {
        include_once($file);
    }
}
```

This exists because the filter step relies on `get_declared_classes()`, which only sees classes PHP has already loaded — PSR-4 autoloading alone would not have loaded them yet.

**2. Filter by subclass** — the gateway case:

```php
private function getGateways()
{
    $this->autoLoad();
    $gateways = [];
    $this->config = new Config();
    foreach (get_declared_classes() as $class) {
        if (is_subclass_of($class, Gateways\AbstractGateway::class)) {
            if (strpos($class, "Voucher") !== false && $this->config->getIsVoucherPSP()) {
                continue;
            }
            $gateways[] = $class;
        }
    }
    return $gateways;
}
```

**3. Filter by interface, keyed by filename** — the brands case. Note the `ReflectionClass` and the deliberate swallow, since `get_declared_classes()` includes vendor classes that may not reflect cleanly:

```php
public function getBrands()
{
    $this->autoLoad();
    $banks = [];
    foreach (get_declared_classes() as $class) {
        try {
            $reflect = new ReflectionClass($class);
            if($reflect->implementsInterface(BrandsInterface::class)) {
                $explodedFileName = explode(DIRECTORY_SEPARATOR, $reflect->getFileName());
                $banks[end($explodedFileName)] = $class;
            }
        } catch (\ReflectionException $e) {}
    }
    return $banks;
}
```

**4. Discovery plus lifecycle** — migrations are discovered, sorted, then applied conditionally, with the applied set recorded in `Config`:

```php
public function execute()
{
    $this->autoLoad();
    $migrationsClasses = $this->getMigrations();
    $this->sort($migrationsClasses);
    if (count($migrationsClasses)) {
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
    }
}
```

**5. Namespace-based discovery via `ClassFinder`** — used for the Blocks checkout, where the classes must be found *by namespace* rather than by directory, and where abstracts must be excluded:

```php
ClassFinder::disablePSR4Vendors();

$blockClasses = ClassFinder::getClassesInNamespace(
    'Woocommerce\Pagarme\Block\ReactCheckout',
    ClassFinder::RECURSIVE_MODE
);

$blockClasses = array_filter($blockClasses, [$this, 'filterAbstractClasses']);
$blockClasses = preg_filter('/^/', '\\', $blockClasses);
```

**6. Constant-pair reflection** is the same idea applied to values instead of classes. `Model\Config\Source\AbstractOptions` reflects over its own constants and pairs `X` with `X_VALUE`:

```php
public function toOptionArray(): array
{
    $options = [];
    $reflectionClass = new \ReflectionClass($this);
    $const = $reflectionClass->getConstants();
    foreach ($const as $code => $value) {
        if (strpos($code, '_VALUE') === false) {
            $options[] = ['value' => $const[$code . '_VALUE'], 'label' => __($value)];
        }
    }
    return $options;
}
```

So `Yesno` is just:

```php
class Yesno extends AbstractOptions implements OptionSourceInterface
{
    const NO = 'No';
    const NO_VALUE = 0;
    const YES = 'Yes';
    const YES_VALUE = 1;
}
```

## Rules

- To add a gateway, migration, card brand, voucher brand, boleto bank, or Blocks payment method: **create the file in the right directory with the right base class or interface.** Do not look for a registry — there isn't one.
- Every discovery site pairs `autoLoad()` (glob + `include_once`) with the filter loop. Keep both if you add a new one.
- Filter on `is_subclass_of` or `implementsInterface`, not on the class name. (The `strpos($class, "Voucher")` check in `getGateways()` is the exception and is a wart, not the pattern.)
- Anything discovered must be constructible with **no arguments** — every site does `new $class`.
- Discovered classes must be side-effect-free at include time. `include_once` runs them outside any hook, so top-level code executes at an unpredictable point; that is what the `defined('ABSPATH') || exit;` guard protects.
- Option-value sets go in `Model/Config/Source/` as `X`/`X_VALUE` constant pairs on an `AbstractOptions` subclass. Never hand-write the options array.
- A file placed in one of these directories that does *not* implement the expected contract is silently ignored. When something "isn't showing up", check the base class and interface first.
- `Core::initialize()`'s explicit controller list is the deliberate exception — controllers have ordering and side-effect requirements and are named there on purpose.

## Examples from this codebase

File: `src/DB/Migration/Migrator.php` — discovery + sort + conditional apply, the fullest instance.

File: `src/Model/Payment/CreditCard/Brands.php` with `src/Model/Payment/CreditCard/Brands/Visa.php` — the whole contribution is 12 lines of properties:

```php
class Visa extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'visa';

    /** @var string */
    protected $name = 'Visa';

    /** @var int[] */
    protected $prefixes = [4];
}
```

File: `src/Model/FeatureCompatibilization.php` — the `ClassFinder` variation, plus how the found classes are handed to WooCommerce:

```php
add_action(
    'woocommerce_blocks_payment_method_type_registration',
    ...
```
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`get_declared_classes()` is O(all loaded classes) and is walked repeatedly** — once per `getGateways()`, `getBrands()`, `getMigrations()` call, with a `ReflectionClass` constructed for every one in the brands case. On a store with many plugins this is thousands of reflections per request. Cache if you add another site.
- **Empty `catch (\ReflectionException $e) {}`** in `Brands::getBrands()` means a brand class that fails to reflect vanishes from the checkout with no log line. Same for the empty `catch (\Exception $e) {}` around each migration's `apply()`.
- **`Brands::getBrands()` keys the result by bare filename** (`end($explodedFileName)` → `'Visa.php'`). Two brand classes with the same filename in different directories would collide, and the key is meaningless to callers, which only use the value.
- **The `Brands` docblock in `CreditCard/Brands.php` says `@package ...Payment\Voucher`** and its local variable is `$banks` — copy-paste from the boleto-banks registry. The three registries are near-identical code that was duplicated rather than abstracted, which also makes `.jscpd.json`'s zero threshold unattainable.
- **Discovery ordering is undefined** except for migrations, which sort explicitly. Gateway display order on the WooCommerce settings screen therefore depends on filesystem `glob` order.
