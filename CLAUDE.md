# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

WordPress/WooCommerce payment plugin for Pagar.me / Stone (`woo-pagarme-payments`). PHP 7.1+ (dev env runs PHP 8.4), PSR-4 autoload `Woocommerce\Pagarme\` → `src/`, tests `Woocommerce\Pagarme\Tests\` → `tests/`. Supports credit card, 2 cards, boleto, boleto+card, Pix, voucher, Google Pay.

Most payment/charge/order business logic does **not** live here — it lives in `pagarme/ecommerce-module-core` (pinned to an exact version in `composer.json`), a library shared by all Pagar.me platform modules (Magento, WooCommerce, etc.). This repo is the WooCommerce adapter around it.

## Commands

Dev environment is Docker + Makefile (`.dev/docker-compose.yml`). `make` / `make help` lists everything.

```bash
make build && make up && make install   # first-time setup (WP + MariaDB + phpMyAdmin, installs/activates WooCommerce + plugin)
make seed                               # re-run WP init (idempotent)
make test                               # phpunit inside the container
make phpcs                              # PHPCS with WordPress-Extra standard
make shell                              # bash in the WordPress container
make patch-vendor                       # REQUIRED after any composer install (see below)
```

Single test / filtered run (inside the container, or locally if vendor is installed):

```bash
vendor/bin/phpunit --filter testGetConfigDataProvider
vendor/bin/phpunit tests/Model/Payment/CreditCardTest.php
```

React checkout bundles (WooCommerce Blocks):

```bash
yarn build    # wp-scripts build → build/{pix,billet,credit_card}.js
yarn start    # watch mode
yarn makepot  # regenerate languages/ POT
```

Legacy (non-React) assets in `assets/` are built with Grunt: `grunt` (dev/watch) or `grunt deploy` (dist + uglify).

E2E (Playwright, separate package in `e2e/`, runs against a deployed site — not part of the unit suite):

```bash
cd e2e && npm install
URL=http://site.under.test PRODUCT=some_product npm run test:e2e
```

Local dev URLs after `make up`: WordPress at `http://woo.localhost`, wp-admin `admin`/`admin`, phpMyAdmin `localhost:8081` (`root`/`root`). XDebug is in `trigger` mode on port 9003 — see the "Desenvolvimento local" section of `docs/README.md` for the full VS Code setup and troubleshooting table.

## Architecture

### Bootstrap chain

`woo-pagarme-payments.php` (plugin header, admin notices, activation) → `plugins_loaded` priority 0 → `wcmpPluginsLoadedCheck()` → `wcmpLoadInstances()`:

1. `Core::instance()` — instantiates every controller listed in `Core::initialize()` (`Settings`, `Checkout`, `Webhooks`, `Hub`, `HubCommand`, `Orders`, `Charges`, `Accounts`, `HubAccounts`, `TdsToken`). Each controller registers its own WP hooks in its constructor. Also enqueues admin/front assets and runs `ActionsRunner`.
2. `Migrator::execute()` — plugin-level DB migrations.
3. `do_action('wcmp_init')`.

Separately: `before_woocommerce_init` → `FeatureCompatibilization::callCompatibilization()` (HPOS / blocks feature flags), and `woocommerce_blocks_loaded` → `addSupportedBlocks()`.

The `wp_pagarme_module_core_*` tables (configuration, customer, charge, order, transaction, saved_card, hub_install_token) are created by raw `dbDelta` calls in the activation hook at the bottom of `woo-pagarme-payments.php` — not by the migration system.

### Core library integration (`src/Concrete/`)

`WoocommerceCoreSetup extends AbstractModuleCoreSetup` is the bridge: `setConfig()` maps the core's abstract decorator slots to the `Woocommerce*Decorator` classes in this directory (order, invoice, creditmemo, payment method, product, customer, database, data service). When the core lib needs to read a WooCommerce order/customer/product, it goes through these decorators. `src/Service/*` (`AccountService`, `CardService`, `CustomerService`, `TdsTokenService`) call Pagar.me APIs via `Model\CoreAuth`.

When behavior looks wrong, check whether the logic is here or in `vendor/pagarme/ecommerce-module-core/`.

### Gateways (`src/Controller/Gateways/`)

Each payment method is a `WC_Payment_Gateway` subclass extending `AbstractGateway`. They are **auto-discovered**: `Settings::getGateways()` includes every file in the directory then filters `get_declared_classes()` for subclasses of `AbstractGateway`. Adding a new file in that namespace registers it — no list to update. `$method` on each gateway is the payment code taken from the matching `Model\Payment\*` class. `LEGACY_SETTINGS_NAME` / `LEGACY_CONFIG_NAME` constants handle migration of settings from the old `woocommerce_pagarme-*_settings` options.

### Payment models (`src/Model/Payment/`)

`AbstractPayment` subclasses build the API request payload for each method and expose `getConfigDataProvider()` for the checkout JS. `Model/Payment/Data/*` are the request DTOs (`PaymentRequest`, `Card`, `BillingAddress`, `Multicustomers`, …). Card brands and boleto banks are registry classes under `CreditCard/Brands/`, `Voucher/Brands/`, `Billet/Banks/`.

### Blocks and templates

`src/Block/` implements a small Magento-style layout system: `AbstractBlock` + `Template::createBlock()` instantiate a block, assign `setTemplate('templates/...')`, and `_toHtml()` includes the matching `.php`/`.phtml` under `templates/`. Used for the legacy checkout (`Block/Checkout/`), admin config forms and order metaboxes (`Block/Adminhtml/`), order/email transaction details (`Block/Order/`), and thank-you pages.

`src/Block/ReactCheckout/` is different — it's the WooCommerce Blocks (Cart & Checkout) integration, extending `AbstractPaymentMethodType`. These classes are discovered at runtime by `FeatureCompatibilization::addSupportedBlocks()` via `ClassFinder` over the namespace (abstract classes filtered out). The JS counterpart lives in `assets/javascripts/front/reactCheckout/payments/<Method>/index.js` and must be added as a `webpack.config.js` entry so `AbstractPaymentMethodBlock::jsUrl()` can find `build/<key>.js`.

### Plugin migrations (`src/DB/Migration/`)

`Migrator` globs `Migrations/*.php`, sorts by reflection, and applies each `MigrationInterface` whose `canApply()` passes, registering applied ones so they don't re-run. Migration files are named `YYYY-MM-DD-NNNN-Description.php`.

### Config

`Model\Config` (a `DataObject`) reads/writes WP options; `Model\Config\PagarmeCoreConfigManagement` syncs them into the core lib's `Configuration` aggregate. Option-source enums live in `Model/Config/Source/`.

## Conventions

- Text domain is always `woo-pagarme-payments`. User-facing brand string is **Stone** (`AbstractGateway::PAGARME`), not "Pagar.me" — the namespace/package names stay `Pagarme`.
- Every PHP file entrypoint guards with `defined('ABSPATH') || exit;` (and often `if (!function_exists('add_action')) exit(0);`).
- Plugin constants are `WCMP_*`, defined via `wc_pagarme_define()` in `constants.php`.
- PHPCS uses `WordPress-Extra` (see `phpcs.xml`); `src/*.php` is exempted from the WP filename/brace/escaping rules, so new `src/` code follows PSR-style class naming and brace placement.
- Commits: conventional commits, in English (`feat:`, `fix:`, `docs:`, `refactor:`, `perf:`). PRs target **`develop`**, not `master`.
- Version bumps must touch all five: `woo-pagarme-payments.php` header, `constants.php` (`WCMP_VERSION`), `composer.json`, `package.json`, `readme.txt` (`Stable tag` + changelog entry).

## Testing

PHPUnit 10.5 with Brain Monkey + Mockery for WP function stubbing. `tests/bootstrap.php` defines the `WCMP_*` constants and a fake `WC_Logger` — it does **not** load WordPress, so tests must mock any WP/WC function they touch. Tests that rely on globals commonly use `@runTestsInSeparateProcesses` / `@preserveGlobalState disabled`. `src/Block/ReactCheckout/` is excluded from coverage (`phpunit.xml`).

## Gotchas

- **`make patch-vendor` after every `composer install`.** `.dev/patch-vendor.sh` rewrites implicitly-nullable parameters (`Type $x = null` → `?Type $x = null`) in `vendor/pagarme/ecommerce-module-core/src` so the pinned core version runs on PHP 8.4. `make install` chains it automatically; a bare `composer install` does not.
- The root `Dockerfile` / `docker-compose.yml` are the old PHP 7.2 image used by CI images; local development uses `.dev/` via the Makefile.
- CI (`.github/workflows/pr.yml`) runs a PHP 7.1 syntax lint, PHPUnit with coverage, and MegaLinter (`JAVASCRIPT`, `TYPESCRIPT`, `PHP`, `HTML`, `COPYPASTE`, `CSS`) with a jscpd copy-paste threshold of 0.
