---
tags: [configuration, settings, data-object, option-source, legacy-migration]
modules: [src/Model/, src/Model/Config/, src/Model/Data/]
applies_to: [models, configs]
confidence: inferred
---
# Pattern: Configuration and Settings

<!-- vibeflow:auto:start -->
## What

All plugin configuration lives in a single WordPress option, loaded into `Model\Config` — a `DataObject` whose `__call` turns `getFooBar()` into `getData('foo_bar')`. Saving writes the option **and** pushes the values into the core lib's configuration aggregate. Enumerable value sets are classes under `Model/Config/Source/`. Settings that existed in the pre-3.0 plugin are read through a declarative legacy map on the gateway.

The rule that follows from this: never call `get_option()`/`update_option()` for plugin settings — go through `Config`, or the core lib and the Hub will read stale data.

## Where

`src/Model/Config.php`, `src/Model/Data/DataObject.php`, `src/Model/Config/PagarmeCoreConfigManagement.php`, `src/Model/Config/Source/` (`Yesno`, `CheckoutTypes`, `EnvironmentsTypes`, `AbstractOptions`), `src/Model/Data/OptionSourceInterface.php`. Gateway-level settings and the legacy maps are in `src/Controller/Gateways/`.

## The Pattern

**1. `DataObject` is the base for anything with dynamic data** — `Config`, `AbstractBlock`, and everything under `Block/`. It is a `\ArrayAccess` wrapper around `protected $_data = []` with Magento's accessor magic:

```php
public function __call($method, $args)
{
    switch (substr($method, 0, 3)) {
        case 'get':
            $key = $this->_underscore(substr($method, 3));
            $index = isset($args[0]) ? $args[0] : null;
            return $this->getData($key, $index);
        case 'set': ...
        case 'uns': ...
        case 'has':
            $key = $this->_underscore(substr($method, 3));
            return isset($this->_data[$key]);
    }
    throw new \Exception(sprintf('Invalid method %1::%2', get_class($this), $method));
}
```

So option keys are `snake_case` and accessors are `camelCase`: `getCcAllowSave()` ↔ `cc_allow_save`. `_underscore()` results are memoized in `static $_underscoreCache`.

`getData()` also supports a path syntax — `getData('a/b/c')` walks nested arrays — and a second `$index` argument that indexes into an array, a `PHP_EOL`-split string, or a nested `DataObject`.

**2. `Config::init()` hydrates from the option and hooks its own updates:**

```php
private function init()
{
    if (is_array($this->getOptions()) || is_object($this->getOptions())) {
        foreach ($this->getOptions() as $key => $value) {
            $this->setData($key, $value);
        }
        add_action(
            'update_option_' . $this->getOptionKey(),
            [$this, 'updateOption'],
            10, 3
        );
    }
}
```

`init()` runs from the constructor, so `new Config` is always a fully loaded object. It is cheap enough that the codebase constructs it freely rather than passing it around.

**3. `save()` writes both destinations.** This is the reason for the "never `update_option` directly" rule:

```php
public function save(Config $config = null)
{
    if (!$config) {
        $config = $this;
    }
    update_option($this->getOptionKey(), $config->getData());
    $this->pagarmeCoreConfigManagement->update($config);
}
```

**4. Explicit getters exist for anything with logic;** magic getters cover the rest. `Config` has ~40 explicit ones (`getIsSandboxMode()`, `getSecretKey()`, `getPagarmeDashUrl()`, `getIsGatewayIntegrationType()`, `availablePaymentMethods()`) — add an explicit getter when a default, a cast, or a derivation is involved:

```php
const ENABLED = 'yes';
const ACCOUNT_ID = 'account_id';
const PAYMENT_PROFILE_ID = 'payment_profile_id';
const POI_TYPE = 'poi_type';
```

Keys that other layers reference are `const`s on `Config`, not literals.

**5. Enumerable option sets are classes of paired constants,** with the option arrays produced by reflection in `AbstractOptions` (`toOptionArray()`, `toArray()`, `toLabelsArray($translate)`):

```php
class Yesno extends AbstractOptions implements OptionSourceInterface
{
    /** @var string */
    const NO = 'No';

    /** @var int */
    const NO_VALUE = 0;

    /** @var string */
    const YES = 'Yes';

    /** @var int */
    const YES_VALUE = 1;
}
```

Consumed in gateway fields as `'options' => $this->yesnoOptions->toLabelsArray(true)`. Adding a value means adding two constants — never editing an array literal.

**6. Gateway settings are WooCommerce settings, mirrored back.** Each gateway's fields are saved by WooCommerce into `woocommerce_<gateway-id>_settings`; `AbstractGateway` hooks `update_option`/`add_option` and calls `saveAdminOptionsInCoreConfig($values)` so `Config` and the core lib stay authoritative. Field defaults read from `Config` so a Hub-provisioned or legacy value wins:

```php
'default' => $this->config->getData('pix_qrcode_expiration_time') ?? 3600,
```

**7. Legacy settings are declared, not branched.** A gateway maps old keys to new and lists the ones needing conversion; `AbstractGateway::getOldConfiguration()` does the rest:

```php
const LEGACY_CONFIG_NAME = "woocommerce_pagarme-credit-card_settings";

const LEGACY_SETTINGS_NAME = [
    "cc_installments_maximum"    => "max_installment",
    "cc_installments_min_amount" => "smallest_installment",
    "cc_installments_interest"   => "interest_rate",
];

const LEGACY_SETTINGS_NEEDS_CONVERSION = ["cc_installments_interest"];
```

```php
protected function getOldConfiguration($fieldName)
{
    if ($this->config->getData($fieldName)) {
        return $this->config->getData($fieldName);
    }
    $oldData = get_option($this::LEGACY_CONFIG_NAME);
    $legacyFieldName = $this->getLegacyFieldsName($fieldName);
    if ($oldData !== false && $legacyFieldName !== false && array_key_exists($legacyFieldName, $oldData)) {
        return $this->getOldData($legacyFieldName, $fieldName, $oldData);
    }

    return null;
}
```

A key listed in `LEGACY_SETTINGS_NEEDS_CONVERSION` is dispatched to `convert<PascalCaseKey>()` on the gateway, resolved dynamically:

```php
$functionHandler = "convert" . Utils::snakeToPascalCase($fieldName);

return $this->$functionHandler($oldData);
```

## Rules

- Read and write plugin settings through `Model\Config` only. `get_option()`/`update_option()` in feature code is a bug — the core lib will not see the change.
- Option keys are `snake_case`; access them as `getSnakeCase()` (magic) or `getData('snake_case')`.
- Add an explicit getter on `Config` when the value needs a default, a cast, an environment branch, or a derivation. Otherwise rely on the magic accessor.
- Any option key referenced from more than one class gets a `const` on `Config` (`Config::ACCOUNT_ID`), never a repeated literal.
- New enumerable value sets go in `Model/Config/Source/` as an `AbstractOptions` subclass implementing `OptionSourceInterface`, defined as `X` / `X_VALUE` constant pairs. Consume via `toLabelsArray()` / `toOptionArray()` / `toArray()`.
- Gateway field defaults read from `Config` with `?? <fallback>` so provisioned and legacy values take precedence over the hardcoded default.
- Renaming a settings key means adding the old name to that gateway's `LEGACY_SETTINGS_NAME`; a semantic change means adding it to `LEGACY_SETTINGS_NEEDS_CONVERSION` plus a `convert<PascalCaseKey>()` method.
- Take `Config` as an optional constructor parameter (`Config $config = null` → `$config ?? new Config`) rather than `new`-ing it mid-method, so tests can inject a mock.
- Sandbox is a config question, never a constant: `$this->config->getIsSandboxMode()`, mapped to the core lib's `'test'`/`'live'` at the call site.

## Examples from this codebase

File: `src/Model/Config.php` — `init()`, `save()`, the key constants, and ~40 explicit getters showing when to prefer one over the magic accessor.

File: `src/Model/Data/DataObject.php` — `__call`, `getDataByPath`, `_underscore` + its cache, and the `ArrayAccess` implementation that every block and `Config` inherit.

File: `src/Controller/Gateways/CreditCard.php` — the fullest legacy map, including a converted key and its `convertCcInstallmentsInterest()` handler.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`new Config` is everywhere.** `Config` is constructed dozens of times per request — in controllers, blocks, models, migrations, `CoreAuth::getHubToken()` — and each construction re-reads the option and re-hydrates. There is no caching and no shared instance. Prefer passing an existing instance where the call site already has one.
- **`Config::init()` registers an `update_option_*` action on every construction,** so the callback is attached once per instance. WordPress de-duplicates identical `[$this, 'method']` callables only per object, and these are distinct objects.
- **`__call` throws a generic `\Exception` with a malformed message** (`'Invalid method %1::%2'` — `sprintf` needs `%1$s`/`%2$s`, so the placeholders render literally). A typo'd accessor produces an unhelpful error.
- **The magic accessor hides the option surface.** There is no schema, so the set of valid keys is only discoverable by grepping `getData(` and the gateway field arrays. New keys should get an explicit getter or a `const` to stay findable.
- **`getOldConfiguration()` calls `get_option()` directly** — legitimately, since it reads a *foreign* legacy option, but it is the one place that looks like a violation of the rule above. Keep such reads inside the legacy path.
- **`Utils::snakeToPascalCase($fieldName)` → `$this->$functionHandler(...)`** is an unchecked dynamic method call. A key listed in `LEGACY_SETTINGS_NEEDS_CONVERSION` without its `convert*` method is a fatal error at settings-render time, not a caught misconfiguration.
