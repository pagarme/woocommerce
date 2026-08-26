# Project: woo-pagarme-payments (Stone for WooCommerce)
> Analyzed: 2026-08-26
> Stack: PHP 7.1+ (dev container runs 8.4), WordPress plugin, WooCommerce 3.9+, PSR-4 `Woocommerce\Pagarme\` → `src/`; React 18 / `@wordpress/*` for the Blocks checkout, jQuery for the legacy checkout; MariaDB via `wpdb`; PHPUnit 10.5 + Brain Monkey + Mockery; Playwright for E2E
> Type: WordPress/WooCommerce plugin (payment gateway) — hybrid: server-rendered admin + two distinct checkout frontends
> Suggested budget: ≤ 8 files per task

## Structure

A single WordPress plugin, not a monorepo. `woo-pagarme-payments.php` is the WP entrypoint (plugin header, admin notices, activation-time table creation); `constants.php` defines the `WCMP_*` constants; `src/` holds all PSR-4 classes; `templates/` holds the `.phtml`/`.php` view files that `src/Block/` renders; `assets/javascripts/` holds both checkout frontends; `build/` holds the webpack output for the Blocks checkout (committed).

The critical thing to understand: **most payment business logic is not in this repo**. `pagarme/ecommerce-module-core` (pinned to an exact version in `composer.json`) is a library shared across all Pagar.me platform modules — it owns charges, orders, webhooks, recurrence, and the API client. This repo is the WooCommerce adapter: `src/Concrete/` supplies the platform decorators the core lib calls back into, and `src/Controller/`, `src/Block/`, `src/Model/` wire it into WordPress.

Layer naming borrows from Magento (`Block`, `Adminhtml`, `Concrete`, `Model/Config/Source`, `DataObject`) because the core lib and its sibling Magento module share that vocabulary. Read it as Magento-flavored, not WordPress-idiomatic.

## Structural Units

- **`src/Controller/`** — WordPress entrypoints. Each class registers its own hooks in its constructor; `Core::initialize()` instantiates the list.
- **`src/Controller/Gateways/`** — one `WC_Payment_Gateway` subclass per payment method; auto-discovered, not registered in a list.
- **`src/Model/`** — domain layer: `Config` (settings), `Order`/`Meta` (order meta access), `Checkout` (order placement), `Gateway` (installments/hub), `Subscription`.
- **`src/Model/Payment/`** — one class per payment method describing its POST contract, plus `Data/` request DTOs and `Brands`/`Banks` registries.
- **`src/Block/`** — Magento-style view layer over `templates/`. `Checkout/` = legacy checkout, `Adminhtml/` = settings + order metaboxes, `Order/` = transaction & e-mail details.
- **`src/Block/ReactCheckout/`** — WooCommerce Blocks (Cart & Checkout) payment method types; the only part of `Block/` that does not render `.phtml`.
- **`src/Concrete/`** — the `pagarme/ecommerce-module-core` bridge: `WoocommerceCoreSetup` plus the platform decorators it registers.
- **`src/Service/`** — thin wrappers over core-lib API proxies (`AccountService`, `CardService`, `CustomerService`, `TdsTokenService`).
- **`src/DB/Migration/`** — plugin-level schema migrations, discovered by glob + reflection.
- **`src/Action/`** — `ActionsRunner` + hook bundles that don't warrant a controller.
- **`src/Helper/`** — `Utils` (static superglobal sanitizing, formatting) and `DocumentUtils` (CPF/CNPJ).
- **`templates/`** — view files, mirroring the `src/Block/` tree.
- **`assets/javascripts/front/checkout/`** — legacy (shortcode) checkout: jQuery object-literal modules.
- **`assets/javascripts/front/reactCheckout/`** — Blocks checkout: React + `@wordpress/data`.
- **`assets/javascripts/admin/`** — settings-page validation and order-screen actions.
- **`tests/`** — PHPUnit, mirroring `src/`. WordPress is never loaded.
- **`e2e/`** — standalone Playwright package, run against a deployed site.

## Pattern Registry

<!-- vibeflow:patterns:start -->
patterns:
  - file: patterns/payment-gateway-registration.md
    tags: [payment-gateway, woocommerce, settings, form-fields, validation]
    modules: [src/Controller/Gateways/, src/Controller/]
  - file: patterns/payment-method-model.md
    tags: [payment-methods, post-contract, dto, domain-model]
    modules: [src/Model/Payment/, src/Model/Payment/Data/]
  - file: patterns/block-template-rendering.md
    tags: [view-layer, templates, blocks, composition, asset-enqueue]
    modules: [src/Block/, templates/]
  - file: patterns/core-module-integration.md
    tags: [core-library, decorators, adapter, api-client, services]
    modules: [src/Concrete/, src/Service/, src/Model/]
  - file: patterns/runtime-class-discovery.md
    tags: [registry, reflection, autoload, extensibility]
    modules: [src/Controller/, src/DB/Migration/, src/Model/Payment/, src/Model/]
  - file: patterns/wp-hook-registration.md
    tags: [wordpress-hooks, controllers, bootstrap, endpoints, webhooks]
    modules: [src/Controller/, src/Action/, src/]
  - file: patterns/react-checkout-blocks.md
    tags: [woocommerce-blocks, react, wordpress-data, state-management, webpack]
    modules: [src/Block/ReactCheckout/, assets/javascripts/front/reactCheckout/]
  - file: patterns/legacy-checkout-js.md
    tags: [jquery, legacy-checkout, tokenization, data-attributes, localize-script]
    modules: [assets/javascripts/front/checkout/, assets/javascripts/admin/]
  - file: patterns/config-and-settings.md
    tags: [configuration, settings, data-object, option-source, legacy-migration]
    modules: [src/Model/, src/Model/Config/, src/Model/Data/]
  - file: patterns/order-meta-access.md
    tags: [order-meta, hpos, wordpress-meta, order-status]
    modules: [src/Model/]
  - file: patterns/db-migrations.md
    tags: [migrations, database, wpdb, schema]
    modules: [src/DB/Migration/, src/DB/Migration/Migrations/]
  - file: patterns/unit-testing.md
    tags: [testing, phpunit, mockery, brain-monkey, mocking]
    modules: [tests/]
  - file: patterns/extension-points.md
    tags: [filters, actions, extensibility, split, marketplace]
    modules: [src/Concrete/, src/Controller/, docs/filters-actions/]
<!-- vibeflow:patterns:end -->

## Pattern Docs Available

- [payment-gateway-registration.md](patterns/payment-gateway-registration.md) — how a payment method becomes a `WC_Payment_Gateway`, its settings fields, and its validators.
- [payment-method-model.md](patterns/payment-method-model.md) — the `Model\Payment\*` classes that declare each method's POST contract and build its API payload.
- [block-template-rendering.md](patterns/block-template-rendering.md) — the Magento-style `Block` + `templates/*.phtml` view layer and how blocks compose.
- [core-module-integration.md](patterns/core-module-integration.md) — the `ecommerce-module-core` bridge: `WoocommerceCoreSetup`, platform decorators, and API proxy services.
- [runtime-class-discovery.md](patterns/runtime-class-discovery.md) — the recurring glob + reflection registry used for gateways, migrations, brands, banks, and blocks.
- [wp-hook-registration.md](patterns/wp-hook-registration.md) — controllers as hook bundles, the bootstrap chain, and `wc-api` endpoints.
- [react-checkout-blocks.md](patterns/react-checkout-blocks.md) — the WooCommerce Blocks checkout: PHP payment method type, React component, `@wordpress/data` store, webpack entry.
- [legacy-checkout-js.md](patterns/legacy-checkout-js.md) — the jQuery object-literal modules behind the shortcode checkout and the admin screens.
- [config-and-settings.md](patterns/config-and-settings.md) — `DataObject`, `Config`, option sources, and the legacy-settings conversion path.
- [order-meta-access.md](patterns/order-meta-access.md) — `Meta`/`Order` magic-property meta access and the HPOS branch.
- [db-migrations.md](patterns/db-migrations.md) — plugin-level schema migrations and how they are recorded.
- [unit-testing.md](patterns/unit-testing.md) — the PHPUnit + Brain Monkey + Mockery setup and its aliasing/overloading tricks.
- [extension-points.md](patterns/extension-points.md) — the filters and actions this plugin exposes to store owners.

## Key Files

- `woo-pagarme-payments.php` — plugin header, admin notices, activation-time `dbDelta` table creation, `plugins_loaded` bootstrap.
- `constants.php` — every `WCMP_*` constant, defined through `wc_pagarme_define()`.
- `src/Core.php` — singleton that instantiates the controller list and enqueues global assets.
- `src/Controller/Settings.php` — settings page + gateway auto-discovery (`getGateways()`).
- `src/Controller/Gateways/AbstractGateway.php` — base `WC_Payment_Gateway`: `process_payment`, form fields, legacy settings conversion.
- `src/Model/Checkout.php` — turns the formatted POST into a core-lib order.
- `src/Model/Payment/PostFormatter.php` — normalizes `$_POST` (both checkouts) into the shape payment models expect.
- `src/Model/Config.php` — all plugin settings, backed by WP options and mirrored into the core lib.
- `src/Concrete/WoocommerceCoreSetup.php` — registers the platform decorators with the core lib.
- `src/Concrete/WoocommercePlatformOrderDecorator.php` — largest file in the repo; the WooCommerce↔core-lib order translation.
- `src/Block/AbstractBlock.php` / `src/Block/Template.php` — the view layer's base classes.
- `src/Model/Meta.php` — order meta access with the HPOS branch.
- `src/Block/ReactCheckout/AbstractPaymentMethodBlock.php` — Blocks checkout PHP side.
- `assets/javascripts/front/checkout/model/payment/card.js` — the legacy card flow (largest JS file).
- `webpack.config.js` — the three Blocks-checkout bundle entries.
- `tests/bootstrap.php` — defines `WCMP_*` and a fake `WC_Logger`; WordPress is not loaded.
- `Makefile` / `.dev/` — the Docker dev environment.

## Dependencies (critical only)

- **`pagarme/ecommerce-module-core`** (exact pin) — owns charges, orders, webhooks, recurrence, and the Pagar.me API client. Never `composer update` it casually: the pin is load-bearing and `.dev/patch-vendor.sh` patches it for PHP 8.4.
- **`haydenpierce/class-finder`** — discovers `Block\ReactCheckout\*` classes for Blocks registration.
- **`mashape/unirest-php`**, **`monolog/monolog` (<2)**, **`psr/log` 1.1.4** — transitive constraints from the core lib; the low pins are why PHP 8.4 needs patching.
- **`@wordpress/scripts` + `@woocommerce/dependency-extraction-webpack-plugin`** — build the Blocks checkout against WP's externalized runtime.
- **`brain/monkey` + `mockery/mockery`** — the only way the test suite can touch WP/WC functions.

## Known Issues / Tech Debt

- **Dead tests.** `tests/Helper/UtilsTests.php` and `tests/Helper/DocumentUtilsTests.php` name their methods `only_numbers_WithVariousInputs_ShouldReturnOnlyDigits`-style, with `#[DataProvider]` but no `test` prefix and no `#[Test]` attribute. Under PHPUnit 10 those 8 methods never execute. The class names also end in `Tests`, not `Test`.
- **`$_POST` mutation as a transport.** `PostFormatter::format()` and `assemblePaymentRequest()` rewrite `$_POST` in place, and payment models read it back. Nothing downstream can be tested without faking the superglobal.
- **Namespace casing.** `src/Controller/TdsToken.php` declares `namespace WooCommerce\Pagarme\Controller` (capital C) while all 201 other files use `Woocommerce`. PHP tolerates it; PSR-4 autoloading on a case-sensitive filesystem plus `ClassFinder` may not.
- **`declare(strict_types=1)` is inconsistent** — 150 of 202 `src/` files have it. Newer `Model/`, `Block/`, and `Gateways/` code does; `Controller/`, `Concrete/`, `Service/`, `Helper/` largely do not.
- **`WoocommercePlatformInvoiceDecorator` is entirely no-op** — every method is an empty body with a "no Invoice concept in Woocommerce" comment. Required by the core lib's abstract contract, so it can't be deleted, but it is not a model to copy.
- **Two parallel checkout frontends** must be kept in sync by hand. A new payment field needs work in `templates/checkout/`, `assets/javascripts/front/checkout/`, `assets/javascripts/front/reactCheckout/`, and the `Model\Payment\*` requirements list.
- **`build/` is committed** but is webpack output. It goes stale silently if someone edits `assets/javascripts/front/reactCheckout/` without running `yarn build`.
- **Vendor patching is a manual step.** A bare `composer install` (instead of `make install`) leaves the core lib un-patched and the plugin fatals on PHP 8.4.
- **jscpd threshold is 0** in `.jscpd.json`, yet the per-payment-method blocks, templates, and thank-you pages are near-duplicates by design. Expect MegaLinter copy-paste noise.
- **Documentation language is mixed** — `docs/README.md` and `.github/contributing.md` are Portuguese; code, comments, and commit messages are English.

## Known Gaps

Sampled ~45 of ~313 source files. Not covered in depth: `src/Model/Subscription.php` and the WooCommerce Subscriptions integration; `src/Model/Payment/Voucher/*` and `Billet/Banks/*` beyond confirming they follow the brands-registry pattern; `src/Block/Adminhtml/Sales/Order/MetaBox/*`; `src/Controller/Charges.php`, `Orders.php`, `Accounts.php`, `Hub.php`; most of `src/Concrete/WoocommercePlatformOrderDecorator.php` (1260 lines, read the first ~120 plus the split filter); the `e2e/` Playwright suite; `assets/stylesheets/`.
