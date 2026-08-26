# Conventions

<!-- vibeflow:auto:start -->

## Files and namespaces

- PSR-4: `Woocommerce\Pagarme\` → `src/`, `Woocommerce\Pagarme\Tests\` → `tests/`. One class per file, `StudlyCase` filename matching the class.
- Vendor prefix is always `Woocommerce` (lowercase `c`). `src/Controller/TdsToken.php` is the single violation — do not copy it.
- Core-lib classes are imported as `Pagarme\Core\...` and aliased when they collide with a local name: `use Pagarme\Core\Kernel\Aggregates\Order as CoreOrder;`, `use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;`.
- `src/*.php` is exempt from the WordPress filename/brace/escaping sniffs (`phpcs.xml`), so `src/` uses PSR-style naming and brace placement. Files outside `src/` (root, `templates/`) follow WordPress style.
- Templates live under `templates/`, mirroring the `src/Block/` tree, and are referenced without extension: `protected $_template = 'templates/checkout/form/card';`. `.phtml` is the norm; a handful of legacy views are `.php`.

## File headers and guards

Every `src/` and `templates/` file opens with the team docblock, then (for most newer files) `declare(strict_types=1);`, then the ABSPATH guard:

```php
<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Model\Payment;

defined('ABSPATH') || exit;
```

Two guard styles coexist and are not interchangeable in practice:

- `defined('ABSPATH') || exit;` — the `src/` default.
- `if (!function_exists('add_action')) { exit(0); }` — used by classes that call WP hook functions at construction time (`Core`, `Model\Order`, `Model\Gateway`, `Helper\Utils`, the gateways) and by every `templates/*.phtml`. Several files use both.

`declare(strict_types=1)` is present in 150 of 202 `src/` files. New code should include it; `Controller/`, `Concrete/`, `Service/`, and `Helper/` mostly predate it.

## Naming

- Classes, interfaces, traits: `StudlyCase`. Abstract classes are prefixed `Abstract...`, interfaces suffixed `...Interface` (`AbstractGateway`, `PaymentInterface`, `MigrationInterface`, `OptionSourceInterface`).
- Constants: `SCREAMING_SNAKE_CASE`, declared with a `/** @var string */` docblock. Payment identity lives in `const PAYMENT_CODE = 'credit_card';` on the model, and gateways reference it rather than repeating the literal.
- Methods: **mixed by layer, deliberately.**
  - WordPress/WooCommerce overrides and anything WP calls back keep `snake_case`: `process_payment()`, `payment_fields()`, `init_form_fields()`, `field_title()`, `generate_pix_additional_data_html()`, `validate_pix_qrcode_expiration_time_field()`.
  - Plugin-internal methods are `camelCase`: `getPaymentMethodTitle()`, `hasCheckoutBlocksSupport()`, `saveAdminOptionsInCoreConfig()`, `assemblePaymentRequest()`.
  - Older files (`Model\Order`, `Model\Meta`, `Helper\Utils`) are `snake_case` throughout. Follow the file you're editing.
- Properties: `camelCase` in newer code, `snake_case` in `Model\Order`/`Meta`. Magento-inherited internals keep a leading underscore: `$_data`, `$_template`.
- Every property carries a `/** @var Type */` docblock; every non-trivial method a `@param`/`@return` docblock. This is consistent enough to treat as required.
- JS: `camelCase` functions, `PascalCase` React components, hooks named `useX` in a `useX.js` file next to the `index.js` that uses them. Legacy JS uses one `pagarmeX` object literal per file (`pagarmeCard`, `pagarmePix`).

## Constructor and dependency style

There is no DI container. Every collaborator is an optional constructor parameter defaulting to `new`:

```php
public function __construct(
    Yesno $yesnoOptions = null,
    Checkout $checkout = null,
    Config $config = null
) {
    $this->config = $config ?? new Config;
    $this->checkout = $checkout ?? new Checkout;
    $this->yesnoOptions = $yesnoOptions ?? new Yesno;
}
```

This is the project's testability seam — it is why `AccountService`, `Config`, and `Checkout` can be mocked at all. Adding a hard `new` inside a method body (as `Service\CardService` and `Service\CustomerService` do with `new CoreAuth()`) closes that seam and forces `Mockery::mock('overload:...')` in tests. Prefer the constructor parameter.

Older code writes `if (!$config) { $config = new Config; }` instead of `??`; both appear, `??` is the newer form. `new Config` without parentheses is the house style.

## Internationalization

- Text domain is **always** `'woo-pagarme-payments'` — never `'woocommerce'`, except when deliberately reusing a WooCommerce core string (`__('Enable/Disable', 'woocommerce')`).
- Source strings are written in **English** and translated to pt-BR in `languages/`. `yarn makepot` regenerates the POT.
- User-facing brand is **"Stone"**: `const PAGARME = 'Stone';` on `AbstractGateway`, and order notes read `'Stone: Payment has already been confirmed.'`. Namespaces, package names, hook prefixes, and DB tables keep `pagarme`/`Pagarme`. Do not rename those; do not write "Pagar.me" in new user-facing copy.
- `__()` is frequently applied to a variable or a constant (`__($this->getPaymentMethodTitle(), 'woo-pagarme-payments')`, `__($instruction, 'woo-pagarme-payments')`). WPCS flags this and the codebase does it anyway — match the surrounding code rather than "fixing" it in passing.

## Prefixes and identifiers

- PHP constants: `WCMP_*`, defined only via `wc_pagarme_define()` in `constants.php`.
- Plugin-global procedural functions (only in `woo-pagarme-payments.php`): `wcmp` prefix — `wcmpRenderAdminNoticeHtml()`, `wcmpLoadInstances()`, `wcmpCreateCoreChargeTable()`.
- Gateway IDs: `'woo-pagarme-payments-' . $this->method`, built in `AbstractGateway::__construct`.
- Checkout form field names: `WCMP_PREFIX . '[' . $methodCode . ']' . $id` via `Block\Checkout\Gateway::getElementId()` → `pagarme[credit_card][cards][1][number]`.
- Order meta keys: `_pagarme_{$name}` for keys listed in `$with_prefix`, `_{$name}` otherwise (`Model\Meta::get_meta_key()`).
- Core-lib DB tables: `{$wpdb->prefix}pagarme_module_core_*`.
- JS script handles: `WCMP_JS_HANDLER_BASE_NAME . $basename` → `pagarme_scripts_card`.
- `wc-api` endpoints: `woocommerce_api_pagarme-<thing>` (`pagarme-tds-token`, `pagarme-account-info`), plus the opaque `Checkout::API_REQUEST` constant.
- Public filters/actions: `pagarme_*` for filters, `on_pagarme_*` for webhook actions.

## Config and settings access

- All settings go through `Model\Config` (a `DataObject`), never `get_option()` directly in feature code.
- Read with the generated getter (`$this->config->getIsSandboxMode()`, `getAccountId()`) or `getData('key')` for keys without one. `DataObject::__call` maps `getFooBar`/`setFooBar`/`hasFooBar`/`unsFooBar` onto `foo_bar`, so option keys are `snake_case`.
- Enumerable option sets are classes under `Model/Config/Source/` extending `AbstractOptions`, which reflects over `X`/`X_VALUE` constant pairs to produce the option arrays. Add a value by adding two constants, not by editing an array.

## Error handling and logging

- Log through the core lib: `$this->config->log()->info(...)` (or `Service\LogService`). No `error_log`.
- Customer-facing checkout failure: `wc_add_notice($message, 'error')` and return `null` from `process_payment` — except on the Blocks checkout, where `Utils::isCheckoutBlock()` forces `wp_die($message, 'error')`.
- Admin settings validation throws `Controller\Gateways\Exceptions\InvalidOptionException`, caught by `AbstractGateway` and surfaced through `WC_Admin_Settings::add_error()`.
- Unauthenticated/invalid webhook: `wp_die($msg, 'Unauthorized', ['response' => 401])` after logging.
- `try { ... } catch (\Exception $e) {}` with an empty body appears in `Migrator`, the migrations, and `Model\Payment\CreditCard\Brands`. It is deliberate there (a failed migration must not white-screen the store) and is not a general licence to swallow exceptions.

## Testing

- One test class per source class, mirroring the path: `src/Service/AccountService.php` → `tests/Service/AccountServiceTest.php`. Class name ends in `Test`; methods are `test` + `<Method><Condition>Should<Outcome>`.
- WordPress is not loaded. Stub WP functions with `Brain\Monkey\Functions\stubs([...])` inside `setUp()`, after `Brain\Monkey\setUp()`; tear down with `Brain\Monkey\tearDown()` and `Mockery::close()`.
- Static/global collaborators are mocked with `Mockery::mock('alias:Fully\Qualified\Class')`; classes instantiated inside the method under test with `Mockery::mock('overload:Fully\Qualified\Class')`. Both leak across tests, so such classes carry `@runTestsInSeparateProcesses` and `@preserveGlobalState disabled`.
- `#[DataProvider]` (attribute form) for table-driven tests. Arrange/Act/Assert comments are the norm.

## Git and release

- Conventional Commits, **in English**: `feat:`, `fix:`, `docs:`, `refactor:`, `perf:` (`.github/contributing.md`).
- PRs target **`develop`**, not `master`.
- A version bump touches five files: `woo-pagarme-payments.php` (header), `constants.php` (`WCMP_VERSION`), `composer.json`, `package.json`, and `readme.txt` (`Stable tag` + a changelog entry).

## Don'ts

- Do **NOT** call `get_option()` / `update_option()` for plugin settings — go through `Model\Config`, or the value won't be mirrored into the core lib's configuration aggregate and the Hub/gateway will read stale data.
- Do **NOT** read `$_POST` / `$_GET` / `$_SERVER` directly in new code — use `Utils::post()`, `Utils::get()`, `Utils::server()`, which run `filter_input_array` + sanitizing. (`PostFormatter` and `Webhooks` touch the superglobals directly; that is existing debt, not the pattern.)
- Do **NOT** re-implement charge, order, webhook, or recurrence logic here — it belongs to `pagarme/ecommerce-module-core`. Check `vendor/pagarme/ecommerce-module-core/src` before writing it.
- Do **NOT** `composer update` `pagarme/ecommerce-module-core` or relax its exact pin. The version is load-bearing and `.dev/patch-vendor.sh` rewrites its sources for PHP 8.4.
- Do **NOT** run a bare `composer install` in the dev container — use `make install`, which chains `make patch-vendor`. Without the patch the plugin fatals on PHP 8.4.
- Do **NOT** add a gateway, migration, card brand, or boleto bank to a hardcoded list — those are discovered at runtime by glob + reflection. Adding the file is the registration. (`Core::initialize()`'s controller list is the one genuine exception.)
- Do **NOT** hardcode user-facing copy — every string goes through `__()`/`_e()`/`esc_html_e()` with the `'woo-pagarme-payments'` text domain.
- Do **NOT** write "Pagar.me" in new user-facing strings — the brand is "Stone". Conversely, do not rename `pagarme` namespaces, hook names, meta keys, or table names.
- Do **NOT** name a test method without a `test` prefix or an explicit `#[Test]` attribute. `tests/Helper/UtilsTests.php` and `DocumentUtilsTests.php` show what happens: 8 methods that silently never run.
- Do **NOT** access order meta with raw `get_post_meta()` / `update_post_meta()` — use `Model\Order`/`Meta`, which branch on HPOS (`FeatureCompatibilization::isHposActivated()`). Raw post-meta calls break on stores with High-Performance Order Storage. (`Controller\Webhooks` still uses `update_post_meta` for its idempotency markers — existing debt.)
- Do **NOT** add a `Block\ReactCheckout\*` class without also adding its `assets/javascripts/front/reactCheckout/payments/<X>/index.js` **and** a matching `webpack.config.js` entry, then re-running `yarn build`. `AbstractPaymentMethodBlock::jsUrl()` resolves `build/<PAYMENT_METHOD_KEY>.js` and fails silently if the bundle is missing.
- Do **NOT** add a checkout field to only one of the two frontends. Legacy (`templates/checkout/` + `assets/javascripts/front/checkout/`) and Blocks (`assets/javascripts/front/reactCheckout/`) both need it, plus the method's `$requirementsData` so `PostFormatter` doesn't drop it.
- Do **NOT** instantiate collaborators inside method bodies when a constructor parameter would do — it removes the only mocking seam this codebase has.
- Do **NOT** add dependencies. The PHP constraints (`monolog <2`, `psr/log 1.1.4`, PHP 7.1 floor) are dictated by the core lib and by the WordPress versions still supported.
<!-- vibeflow:auto:end -->
