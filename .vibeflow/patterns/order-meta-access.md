---
tags: [order-meta, hpos, wordpress-meta, order-status, magic-properties]
modules: [src/Model/]
applies_to: [models]
confidence: inferred
---
# Pattern: Order Meta Access

<!-- vibeflow:auto:start -->
## What

Pagar.me data attached to a WooCommerce order (payment method, charge ids, transaction response, installment fee, retry count) is stored as order meta and reached through `Model\Meta` / `Model\Order` rather than WordPress meta functions. `Meta` provides magic property access, a two-tier key-prefixing scheme, sanitizing on both read and write, and — critically — the branch between classic post meta and HPOS (High-Performance Order Storage).

Using raw `get_post_meta()`/`update_post_meta()` instead is a correctness bug on HPOS stores, which this plugin declares itself compatible with.

## Where

`src/Model/Meta.php` (abstract base), `src/Model/Order.php` (the concrete order model), `src/Model/SubscriptionMeta.php`. HPOS detection in `Model\FeatureCompatibilization::isHposActivated()`. Consumers: gateways, blocks, `Action\OrderActions`, the `Concrete/` decorators.

## The Pattern

**1. `Meta` is an abstract base holding only an id and a prefix map:**

```php
abstract class Meta
{
    public $ID;
    protected $type        = 'post';
    protected $with_prefix = array();
```

**2. Magic properties read and write meta.** Reading caches into the real property; writing goes straight to meta:

```php
public function __get($prop_name)
{
    if (isset($this->{$prop_name})) {
        return $this->{$prop_name};
    }

    return $this->get_property($prop_name);
}

public function __set($prop_name, $value)
{
    $this->update_meta($this->get_meta_key($prop_name), $value);
}
```

So `$order->pagarme_id` reads meta `_pagarme_pagarme_id`, and `$order->pagarme_id = 'or_x'` writes it. Declaring the property on the subclass is what makes it addressable and cacheable.

**3. Keys are prefixed by membership in `$with_prefix`** — two tiers, one map:

```php
private function get_meta_key($prop_name)
{
    return isset($this->with_prefix[$prop_name]) ? "_pagarme_{$prop_name}" : "_{$prop_name}";
}
```

`Order` declares which keys are ours:

```php
public $with_prefix = array(
    'payment_method'    => 1,
    'response_data'     => 1,
    'pagarme_status'    => 1,
    'pagarme_id'        => 1,
    'attempts'          => 1
);
```

Everything else — the mirrored WooCommerce billing/shipping fields declared as protected properties on `Order` — resolves to `_billing_cpf`, `_shipping_number`, and so on.

**4. Every read and write goes through the HPOS branch and is sanitized:**

```php
public function get_meta($meta_key, $sanitize = 'rm_tags')
{
    $value = get_metadata($this->type, $this->ID, $this->get_meta_key($meta_key), true);
    if (FeatureCompatibilization::isHposActivated()) {
        $value = $this->wc_order->get_meta($this->get_meta_key($meta_key), true);
    }
    return Utils::sanitize($value, $sanitize);
}

public function update_meta($key, $value)
{
    $key = $this->get_meta_key($key);
    if (FeatureCompatibilization::isHposActivated()) {
        $this->wc_order->update_meta_data($key, Utils::rm_tags($value));
        return;
    }
    update_metadata($this->type, $this->ID, $key, Utils::rm_tags($value));
}
```

The sanitizer is selectable per call (`get_meta('response_data', 'none')` when the value is serialized JSON that must survive intact).

**5. `Order` wraps the `WC_Order` and exposes intention-named status transitions,** each of which guards on the current status, adds a translated order note, saves, and logs:

```php
public function payment_paid()
{
    $current_status = $this->wc_order->get_status();

    if (!in_array($current_status, ['completed', 'processing'])) {
        $this->wc_order->add_order_note(__('Stone: Payment has already been confirmed.', 'woo-pagarme-payments'));
        $this->wc_order->payment_complete();
    }

    if (!$this->needs_processing()) {
        $this->wc_order->set_status('completed');
    }

    $statusArray = [
        'previous_status' => $current_status,
        'new_status' => $this->wc_order->get_status()
    ];
    $this->wc_order->save();
    $this->log($statusArray);
}
```

The guard list, the `{previous_status, new_status}` log payload, and the `Stone: ` note prefix are the same in `payment_on_hold()`, `payment_canceled()`, `paymentFailed()`. Copy the shape.

**6. Callers ask the model, not the meta table:**

```php
$order = new Order($orderId);
if ($order->isPagarmePaymentMethod() && $order->get_meta('pagarme_card_tax') > 0) {
    $total = $order->get_meta('pagarme_card_tax');
```

## Rules

- Never call `get_post_meta()`, `update_post_meta()`, `get_metadata()`, or `update_metadata()` on an order directly. Instantiate `Model\Order` and use `get_meta()` / `update_meta()` or the magic properties.
- A new Pagar.me meta key must be added to `Order::$with_prefix` so it gets the `_pagarme_` prefix; a mirrored WooCommerce field is just a protected property.
- Declare every accessible property on the subclass — `__get` only caches into declared properties, and an undeclared name silently round-trips to meta on every access.
- Pass an explicit `$sanitize` argument when the default `rm_tags` would corrupt the value (serialized or JSON payloads).
- Status changes go through the intention-named methods (`payment_paid`, `payment_on_hold`, `payment_canceled`, `paymentFailed`), never `update_status()` at the call site. Each guards the current status, adds a `Stone: `-prefixed translated note, saves, and logs the before/after pair.
- `Order` is constructed with the WooCommerce order id: `new Order($orderId)`. `WooOrderRepository::getById()` is the way to get the underlying `WC_Order`.
- Anything touching meta must work under HPOS. If you add a code path that bypasses `Meta`, test it with `woocommerce_custom_orders_table_enabled` on — the plugin declares compatibility in `FeatureCompatibilization::getFeatures()`.

## Examples from this codebase

File: `src/Model/Meta.php` — the whole mechanism in ~75 lines: magic accessors, `get_meta_key()`, the HPOS branch, sanitizing.

File: `src/Model/Order.php` — `$with_prefix`, the mirrored WooCommerce field block delimited by `// == BEGIN WC ORDER ==` / `// == END WC ORDER ==`, and the four status-transition methods.

File: `src/Action/OrderActions.php` — a consumer: `new Order($order->get_id())`, `isPagarmePaymentMethod()`, `get_meta('pagarme_card_tax')`, with a fallback that recomputes from charges when the meta is empty.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`Meta::get_meta()` reads twice on HPOS stores.** It calls `get_metadata(...)` unconditionally, then overwrites the result with `$this->wc_order->get_meta(...)` if HPOS is active. Every read on an HPOS store pays for a wasted legacy-table query. The branch should be an `if/else`.
- **`Meta` references `$this->wc_order`, which it does not declare.** The property lives on the `Order` subclass, so `Meta` is only usable by subclasses that happen to define it — an implicit contract that a new subclass will violate silently.
- **`__isset()` returns `$this->__get(...)`**, i.e. the value, not a boolean, and triggers a meta read. `isset($order->foo)` therefore has a side effect and returns the wrong type.
- **`Controller\Webhooks` bypasses this layer** entirely, using `update_post_meta`/`get_post_meta` for its `webhook_{$event}_{$id}` idempotency markers. On an HPOS store those markers go to the legacy table — functional today, but inconsistent with the rest of the plugin.
- **`Order` declares ~25 protected properties mirroring WooCommerce billing/shipping fields** purely so `__get` can cache them. It duplicates `WC_Order`'s own accessors and drifts when WooCommerce adds or renames a field.
- **`/** phpcs:disable */` wraps both constructors** (`Meta::__construct`, `Order::__construct`) to silence the sniff on the `$ID` parameter name. Two suppressions to keep one non-conforming variable name.
- **Status methods are inconsistently named** — `payment_paid()`, `payment_on_hold()`, `payment_canceled()` are `snake_case` while `paymentFailed()` is `camelCase`, in the same class.
