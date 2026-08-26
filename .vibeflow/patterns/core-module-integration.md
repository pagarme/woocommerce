---
tags: [core-library, decorators, adapter, api-client, services]
modules: [src/Concrete/, src/Service/, src/Model/]
applies_to: [services, models, controllers]
confidence: inferred
---
# Pattern: Core Module Integration

<!-- vibeflow:auto:start -->
## What

`pagarme/ecommerce-module-core` is a platform-agnostic library, shared with the Magento and other Pagar.me modules, that owns charges, orders, transactions, webhooks, recurrence, and the Pagar.me API client. This plugin is its WooCommerce adapter. Integration happens in exactly two directions:

- **Core lib → WooCommerce**: `WoocommerceCoreSetup` registers a set of *decorator* classes; the core lib instantiates them whenever it needs to read or write a platform order, customer, product, invoice, or table.
- **WooCommerce → Pagar.me API**: thin `Service\*` classes wrap the core lib's `Proxy` objects, authenticated by `Model\CoreAuth`.

Understanding which side owns a behaviour is the first question for any change here.

## Where

`src/Concrete/` — `WoocommerceCoreSetup` plus eight `Woocommerce*Decorator` / `Woocommerce*Service` classes. `src/Service/` — `AccountService`, `CardService`, `CustomerService`, `TdsTokenService`. `src/Model/CoreAuth.php` — the credential provider. `src/Model/Config/PagarmeCoreConfigManagement.php` — settings sync.

## The Pattern

**1. `WoocommerceCoreSetup extends AbstractModuleCoreSetup` is the single registration point.** `setConfig()` maps the core lib's abstract slots to concrete classes; the other `set*` hooks report platform identity and paths:

```php
final class WoocommerceCoreSetup extends AbstractModuleCoreSetup
{
    const MODULE_NAME = 'woo-pagarme-payments';

    protected function setModuleVersion()
    {
        self::$moduleVersion = WCMP_VERSION;
    }

    protected function setPlatformVersion()
    {
        $version = ' Wordpress/' . get_bloginfo('version');

        if (defined('WC_VERSION')) {
            $version .= ' Woocommerce/' . WC_VERSION;
        }

        self::$platformVersion = $version;
    }

    protected function setConfig()
    {
        self::$config = [
            AbstractModuleCoreSetup::CONCRETE_DATABASE_DECORATOR_CLASS =>
            WoocommerceDatabaseDecorator::class,
            AbstractModuleCoreSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS =>
            WoocommercePlatformOrderDecorator::class,
            ...
        ];
    }

    public static function getDatabaseAccessObject()
    {
        global $wpdb;
        return $wpdb;
    }
}
```

A new core-lib abstraction is adopted by adding a `Woocommerce*Decorator` to `src/Concrete/` and one entry to this array — nothing else.

**2. Decorators translate WooCommerce vocabulary into core-lib value objects.** They extend an `Abstract*Decorator` from `Pagarme\Core\Kernel\Abstractions` and hold the WooCommerce object as `$platformOrder`:

```php
class WoocommercePlatformOrderDecorator extends AbstractPlatformOrderDecorator
{
    /** @var WC_Order */
    protected $platformOrder;

    public function __construct($formData = null, $paymentMethod = null)
    {
        $this->i18n          = new LocalizationService();
        $this->formData      = $formData;
        $this->paymentMethod = $this->formatPaymentMethod($paymentMethod);
        $this->orderService  = new OrderService();
        parent::__construct();
    }
```

Where WooCommerce has no equivalent concept, the method is implemented as an explicit no-op with a comment saying so, and returns `null`:

```php
public function setStateAfterLog(OrderState $state)
{
    // Woocommmerce doesnt have the concept of state, only status;
    return null;
}
```

Concept mismatches are bridged with an explicit map, never a computed guess:

```php
$statusToState = [
    'pending'                 => 'stateNew',
    'paid'                    => 'complete',
    'failed'                  => 'closed',
    'authentication_required' => 'processing'
];
```

**3. Table names are declared once**, in `WoocommerceDatabaseDecorator::setTableArray()`, keyed by the core lib's `TABLE_*` constants:

```php
final class WoocommerceDatabaseDecorator extends AbstractDatabaseDecorator
{
    protected function setTableArray()
    {
        $this->tableArray = [
            AbstractDatabaseDecorator::TABLE_MODULE_CONFIGURATION =>
            $this->getTableName('pagarme_module_core_configuration'),
            AbstractDatabaseDecorator::TABLE_CHARGE =>
            $this->getTableName('pagarme_module_core_charge'),
            ...
        ];
    }
```

**4. API calls go through a `Service\*` wrapper around a core-lib `Proxy`.** The service's job is to assemble the core lib's input objects from `Config` and translate the response — never to speak HTTP:

```php
class AccountService
{
    public function __construct(CoreAuth $coreAuth, Config $config, WC_Order $order = null)
    {
        $this->coreAuth = $coreAuth;
        $this->config = $config;
        $this->order = $order;
    }

    public function getAccount($accountId)
    {
        $storeSettings = new StoreSettings();
        $storeSettings->setSandbox($this->config->getIsSandboxMode());
        $storeSettings->setStoreUrls([Utils::get_site_url()]);
        $storeSettings->setEnabledPaymentMethods($this->config->availablePaymentMethods());

        $accountResponse = $this->getAccountOnPagarme($accountId);

        $account = Account::createFromSdk($accountResponse);
        return $account->validate($storeSettings);
    }

    private function getAccountOnPagarme($accountId)
    {
        $accountService = new AccountProxy($this->coreAuth);
        return $accountService->getAccount($accountId);
    }
}
```

**5. `Model\CoreAuth extends Client` supplies the credential** by reading `Config`, so no service handles the secret key:

```php
class CoreAuth extends Client
{
    public function getHubToken()
    {
        $config = new Config;
        return $config->getSecretKey();
    }
}
```

Sandbox/production is selected per call from config, using the core lib's own vocabulary:

```php
$environment = 'live';
if ($this->config->getIsSandboxMode()) {
    $environment = 'test';
}
return $tdsTokenProxy->getTdsToken($environment, $accountId)->tdsToken;
```

**6. Core-lib names are imported with an alias when they collide** with a local class — this is pervasive and load-bearing for readability:

```php
use Pagarme\Core\Kernel\Aggregates\Order as CoreOrder;
use Pagarme\Core\Payment\Repositories\CustomerRepository as CoreCustomerRepository;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Model\Customer as PagarmeCustomer;
```

**7. Success is detected by type, not by a flag.** `Model\Checkout::process()` returns either a `CoreOrder` or an error object; callers check `instanceof`:

```php
$process = $this->checkout->process($wooOrder);

if ($process instanceof CoreOrder) {
    return ['result' => 'success', ...];
}
```

## Rules

- Before implementing charge/order/transaction/webhook/recurrence logic, look in `vendor/pagarme/ecommerce-module-core/src`. If it exists there, call it; do not reimplement.
- New core-lib abstractions are adopted by adding a decorator to `src/Concrete/` and one entry to `WoocommerceCoreSetup::setConfig()`.
- Decorator classes are named `Woocommerce<Concept>Decorator`, are `final` when they hold no subclass (`WoocommerceDatabaseDecorator`, `WoocommerceCoreSetup`), and keep the WooCommerce object in `$platformOrder` / `$platformCustomer`.
- An unsupported concept is an explicit empty method with a comment naming why, returning `null`. Do not throw — the core lib calls these unconditionally.
- WooCommerce↔core-lib enum mismatches are handled by an explicit array map with a fallback, in the decorator.
- API access is always `Service\* → Pagarme\Core\Middle\Proxy\* → CoreAuth`. Never build a request or read a secret key outside that chain.
- Alias core-lib imports (`as CoreOrder`, `as CoreSetup`) whenever the short name would be ambiguous.
- Table names are only ever written in `WoocommerceDatabaseDecorator` and in the activation-time `dbDelta` functions in `woo-pagarme-payments.php`; the two must agree.
- Prefer taking collaborators as constructor parameters (`AccountService` does) over `new`-ing them in a method (`CardService`, `CustomerService`, `TdsTokenService` do) — the former is mockable.

## Examples from this codebase

File: `src/Concrete/WoocommerceCoreSetup.php` — the registration surface and the platform-identity hooks.

File: `src/Service/TdsTokenService.php` — the smallest complete example of the service→proxy→auth chain, including the sandbox branch.

File: `src/Concrete/WoocommercePlatformOrderDecorator.php` — the 1260-line workhorse: status/state mapping, customer and address assembly, payment-method collection, and the split/marketplace filters.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`WoocommercePlatformInvoiceDecorator` is 100% no-op** — every one of its ~11 methods is an empty body with the comment "Not necessary to be implemented on Woocommerce, there is no Invoice concept", including `jsonSerialize()` while the class declares `implements JsonSerializable`. The core lib's abstract contract forces it to exist. It is not a template for a real decorator, and `loadByIncrementId()` even carries a stale `// TODO: Implement`.
- **`WoocommercePlatformOrderDecorator` at 1260 lines** is by far the largest file in the repo and mixes status mapping, customer assembly, address assembly, phone parsing, item mapping, split rules, and payment-method construction. New translation logic should go into a focused collaborator rather than growing this file further.
- **`CardService`, `CustomerService`, and `TdsTokenService` `new CoreAuth()` inside their bodies** instead of accepting it, unlike `AccountService`. Tests for them must use `Mockery::mock('overload:...')` and `@runTestsInSeparateProcesses`.
- **The core lib is patched in place.** `.dev/patch-vendor.sh` rewrites `vendor/pagarme/ecommerce-module-core/src` with a `perl -i` regex to make implicitly-nullable parameters explicit for PHP 8.4. Anything that reinstalls vendor without re-running `make patch-vendor` leaves a plugin that fatals. The real fix is upstream; treat the patch as temporary.
- **`getState()` uses truthiness to test array membership** — `$statusToState[$status] ? ... : 'processing'` on a possibly-absent key emits a notice and relies on the fallback. `??` or `array_key_exists` is the correct form.
