---
tags: [wordpress-hooks, controllers, bootstrap, endpoints, webhooks]
modules: [src/Controller/, src/Action/, src/]
applies_to: [controllers, handlers, middleware]
confidence: inferred
---
# Pattern: WordPress Hook Registration

<!-- vibeflow:auto:start -->
## What

There is no router and no event bus. A *controller* is a class whose constructor registers WordPress hooks and whose public methods are the callbacks. Instantiating the controller **is** wiring it up. `Core::initialize()` names the controllers; `ActionsRunner` covers hook bundles too small to justify one. HTTP endpoints are WooCommerce `wc-api` actions.

## Where

`src/Controller/` (10 controllers), `src/Action/` (`ActionsRunner` + `OrderActions` + `CustomerFieldsActions`), `src/Core.php` (the controller list and global asset enqueueing), `woo-pagarme-payments.php` (the outer bootstrap).

## The Pattern

**1. The bootstrap chain is fixed.** `plugins_loaded` at priority 0 → guard on WooCommerce being present → `wcmpLoadInstances()`:

```php
function wcmpLoadInstances()
{
    require_once __DIR__ . '/vendor/autoload.php';

    Woocommerce\Pagarme\Core::instance();
    (new Woocommerce\Pagarme\DB\Migration\Migrator)->execute();
    do_action('wcmp_init');
}

add_action('plugins_loaded', 'wcmpPluginsLoadedCheck', 0);
add_action('before_woocommerce_init', 'checkCompatibilityWithFeatures', 0);
add_action('woocommerce_blocks_loaded', 'addWoocommerceSupportedBlocks');
```

The three separate entry hooks are not interchangeable: HPOS/feature declarations must run on `before_woocommerce_init`, and Blocks registration on `woocommerce_blocks_loaded`.

**2. `Core` is a singleton that instantiates the controller list.** This is the one place with an explicit registry (see `runtime-class-discovery.md` for why everything else is auto-discovered):

```php
public static function initialize()
{
    $controllers = array(
        'Settings',
        'Checkout',
        'Webhooks',
        'Hub',
        'HubCommand',
        'Orders',
        'Charges',
        'Accounts',
        'HubAccounts',
        'TdsToken',
    );

    self::load_controllers($controllers);
}

public static function load_controllers($controllers)
{
    foreach ($controllers as $controller) {
        $class = sprintf(__NAMESPACE__ . '\Controller\%s', $controller);
        new $class();
    }
}
```

Because they are constructed in order and each registers hooks immediately, ordering in this array is meaningful.

**3. A controller registers in its constructor and does the work in a public method:**

```php
class TdsToken
{
    /** @var Config */
    protected $config;

    public function __construct()
    {
        $this->config = new Config;
        add_action('woocommerce_api_pagarme-tds-token', [$this, 'getTdsToken']);
    }

    public function getTdsToken()
    {
        $accountId = $this->config->getAccountId();
        $tdsTokenService = new TdsTokenService($this->config);
        wp_send_json_success([
            'token' => $tdsTokenService->getTdsToken($accountId)
        ]);
        wp_die();
    }
}
```

**4. HTTP endpoints are `woocommerce_api_*` actions,** reachable at `/wc-api/<name>`. Names are `pagarme-<thing>`; some come from a `Core::get*Name()` helper so the PHP and JS sides agree:

```php
add_action('woocommerce_api_' . Core::getWebhookName(), array($this, 'handle_requests'));
```

JSON responses use `wp_send_json_success()` / `wp_send_json_error()` followed by `wp_die()`.

**5. Webhook handlers validate first, then log, then dispatch a plugin action.** The order — signature, payload presence, signature validity, order ownership — is deliberate, and every rejection is logged before `wp_die`:

```php
public function handle_requests()
{
    $webHookSignature = $_SERVER[self::WEBHOOK_SIGNATURE_HEADER] ?? null;
    if (!$webHookSignature) {
        $this->config->log()->info('Unauthorized Webhook Received: no signature header found!');
        wp_die('Unauthorized Webhook Received: no signature header found!', 'Unauthorized', array('response' => 401));
    }
    $payload = file_get_contents('php://input');
    ...
    if (!WebhookValidatorService::validateSignature($payload, $webHookSignature)) { ... }
```

Idempotency is a per-event meta marker checked before dispatch:

```php
if ($this->was_sent($event, $body->id, $body->data->code)) {
    return;
}

if (strpos($event, 'charge') !== false) {
    update_post_meta($body->data->code, "webhook_{$event}_{$body->id}", true);
    do_action("on_pagarme_{$event}", $body);
    do_action("on_pagarme_notes_{$event}", $body);
    return;
}
```

**6. Hook bundles that don't warrant a controller go in `src/Action/`,** implementing `RunnerInterface` with a single `run()`, and are named in `ActionsRunner`:

```php
class ActionsRunner implements RunnerInterface
{
    private $actionClasses = [
        "OrderActions",
        "CustomerFieldsActions"
    ];

    public function run()
    {
        foreach ($this->actionClasses as $actionClass) {
            $class = sprintf(__NAMESPACE__ . '\%s', $actionClass);
            $action = new $class();
            $action->run();
        }
    }
}
```

```php
class OrderActions implements RunnerInterface
{
    public function run()
    {
        add_filter('woocommerce_get_order_item_totals', array($this, 'showInstallmentFeesToCustomer'), 10, 3);
        add_action('woocommerce_admin_order_totals_after_tax', array($this, 'showInstallmentFeesAdmin'));
        add_action('woocommerce_available_payment_gateways', array($this, 'removeGooglepayOnlyWhenNotProcessPaymentAction'));
    }
```

**7. Assets are enqueued behind a context guard,** so the checkout bundle never loads on unrelated pages:

```php
public static function scripts_front()
{
    if (is_checkout() || is_account_page()) {
        self::enqueue_styles('front');
        self::enqueue_scripts('front');
    }
}
```

## Rules

- A controller's constructor registers hooks and initializes collaborators. It must not perform work, echo, or query — it runs on every request.
- Callbacks are **public** methods on the controller (WordPress needs to call them); helpers are private.
- Callback naming follows the file: `snake_case` in the older controllers (`handle_requests`, `was_sent`), `camelCase` in newer ones (`getTdsToken`, `saveIdentifiersFromWebhook`).
- Register with the array-callable form `array($this, 'method')` or `[$this, 'method']`; both appear, prefer matching the file. Always pass the accepted-args count when a filter needs more than one (`add_filter(..., 10, 3)`).
- New controllers must be added to `Core::initialize()`'s list — this is the one registry that is not auto-discovered.
- New endpoints are `woocommerce_api_pagarme-<thing>`. If JS needs the name, expose it through a `Core::get*Name()` static rather than duplicating the literal.
- Endpoint handlers must terminate: `wp_send_json_*()` then `wp_die()`, or `wp_die()` directly on rejection.
- Anything reached from a webhook or endpoint gets an authorization/validity check *before* any state change, and every rejection path logs via `$this->config->log()->info(...)`.
- WooCommerce feature declarations (HPOS, Blocks) belong on `before_woocommerce_init`; Blocks payment-method registration on `woocommerce_blocks_loaded`. Do not move them to `plugins_loaded`.
- Guard front-end asset enqueueing with a context check (`is_checkout()`, `is_account_page()`, `is_admin()`).

## Examples from this codebase

File: `src/Controller/TdsToken.php` — the minimal controller: constructor registers one `wc-api` action, one public handler, `wp_send_json_success` + `wp_die`.

File: `src/Controller/Webhooks.php` — the full validate → log → idempotency-check → `do_action` sequence, including the `HTTP_X_WEBHOOK_ASYMMETRIC_SIGNATURE` header constant.

File: `src/Action/OrderActions.php` — a hook bundle: three registrations in `run()`, each with its callback in the same class.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`Controller\Settings` registers 20+ hooks in one constructor** across admin columns, gateway loading, plugin action links, and settings pages, and also owns gateway discovery. It is the de-facto god object of the controller layer; new admin behaviour should get its own controller or `Action` runner.
- **`Webhooks::handle_requests` reads `$_SERVER` directly** and uses `update_post_meta`/`get_post_meta` for its idempotency markers, bypassing both `Utils::server()` and the HPOS-aware `Model\Meta`. On an HPOS store those markers land in the legacy post-meta table.
- **`src/Controller/TdsToken.php` declares `namespace WooCommerce\Pagarme\Controller`** (capital `C`) while every other file uses `Woocommerce`. PHP resolves it, but PSR-4 on a case-sensitive filesystem and `ClassFinder` may not.
- **Controllers instantiate their own dependencies** (`new Config()`, `new TdsTokenService(...)`) rather than accepting them, so tests must alias/overload them — see `tests/Controller/HubCommandTest.php`, which additionally reaches in with `ReflectionClass` to swap a private property.
- **`Core::__construct` does the work of five methods** — text domain, activation redirect, controller loading, script enqueueing, a `script_loader_tag` filter, and action runners — as a side effect of `instance()`. There is no way to load controllers without also enqueueing assets.
- **`add_action('init', [$this, 'set_method_description'])` runs once per gateway**, so ~8 identical `init` callbacks are registered on every request just to set a description string.
