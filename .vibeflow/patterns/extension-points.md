---
tags: [filters, actions, extensibility, split, marketplace, webhooks]
modules: [src/Concrete/, src/Controller/, docs/filters-actions/]
applies_to: [handlers, models, configs]
confidence: inferred
---
# Pattern: Extension Points (Filters and Actions)

<!-- vibeflow:auto:start -->
## What

The plugin exposes a deliberately small public hook surface so store owners can customize behaviour from their theme or a companion plugin without forking. There are three filters and one family of actions, and they are the only supported extension mechanism — everything else is internal.

Two properties make this a *pattern* rather than a list: an opt-in filter is gated by `isset($wp_filter[...])` so the whole feature stays dormant until someone hooks it, and filtered input is validated immediately after `apply_filters` with a thrown exception on a bad contract.

## Where

`src/Concrete/WoocommerceCoreSetup.php` (`pagarme_hub_app_key`, `pagarme_marketplace_config`), `src/Concrete/WoocommercePlatformOrderDecorator.php` (`pagarme_split_order`), `src/Controller/Webhooks.php` (`on_pagarme_*`), `src/Controller/Orders.php` (`on_pagarme_response`). User-facing documentation in `docs/filters-actions/split.md`, linked from `docs/README.md`.

## The Pattern

**1. Feature-gate on `$wp_filter` before doing any work.** Split/marketplace support is off unless the store actually registered the filter, so no store pays for a feature it does not use:

```php
private static function fillWithMarketplaceConfig($configData)
{
    global $wp_filter;
    if (
        !isset($wp_filter['pagarme_marketplace_config'])
    ) {
        return $configData;
    }
    $configSplit = new \stdClass();
    $configSplit->enabled = true;
    $configSplit->responsibilityForProcessingFees = "marketplace_sellers";
    $configSplit->responsibilityForChargebacks = "marketplace_sellers";
    $configSplit->responsibilityForReceivingSplitRemainder = "marketplace_sellers";
    $configSplit->responsibilityForReceivingExtrasAndDiscounts = "marketplace_sellers";
    $configSplit->mainRecipientId = null;

    $configSplit = apply_filters( "pagarme_marketplace_config", $configSplit);
    $configData->marketplaceConfig = $configSplit;

    return $configData;
}
```

**2. Pass a fully-populated default object, not an empty one.** The integrator overrides only what they care about (`mainRecipientId` is the sole required field) and every other responsibility field already has a sane value.

**3. Validate the filtered result immediately and throw on a broken contract:**

```php
public function handleSplitOrder()
{
    global $wp_filter;
    if ( !isset($wp_filter['pagarme_split_order'])) {
        return null;
    }

    $order = $this->getPlatformOrder();
    $paymentMethod = $this->getPaymentMethodPlatform();

    $splitDataFromOrder = apply_filters('pagarme_split_order', $order, $paymentMethod);
    $this->validateSellerArray($splitDataFromOrder);
    $splitData = new Split();
    $splitData->setSellersData($splitDataFromOrder['sellers']);
    $splitData->setMarketplaceData($splitDataFromOrder['marketplace']);
    return $splitData;
}

private function validateSellerArray($splitDataFromOrder)
{
    foreach ($splitDataFromOrder['sellers'] as $data) {
        $requiredFields = ['marketplaceCommission', 'commission', 'pagarmeId'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new \InvalidArgumentException("The field '$field' is required for each seller.");
            }
        }
    }
}
```

**4. A simple value filter keeps its default as a local and falls back if the filter returns empty:**

```php
protected static function getPlatformHubAppPublicAppKey()
{
    $defaultKey = '1e9c3c13-f8ea-4fdd-b2a0-8795b5593397';
    $key = apply_filters('pagarme_hub_app_key', $defaultKey);
    if (!empty($key)) {
        return $key;
    }
    return $defaultKey;
}
```

**5. Webhook events are re-broadcast as plugin actions,** named from the sanitized event type so integrators can react to any Pagar.me event. Charge events get a second `notes` variant, and the payload differs between the charge and order branches:

```php
$event = $this->sanitize_event_name($body->type);   // 'charge.paid' → 'charge_paid'

if (strpos($event, 'charge') !== false) {
    update_post_meta($body->data->code, "webhook_{$event}_{$body->id}", true);
    do_action("on_pagarme_{$event}", $body);
    do_action("on_pagarme_notes_{$event}", $body);
    return;
}

...
do_action("on_pagarme_{$event}", $order, $body);
```

**6. Documented extension points get a page under `docs/filters-actions/`,** written in Portuguese for store owners, with a complete runnable snippet and the money-in-cents rule spelled out. `docs/README.md` links each one. Adding a filter without adding its page means it does not exist as far as integrators are concerned.

## Rules

- Filters are prefixed `pagarme_`; webhook actions are `on_pagarme_<event>` (plus `on_pagarme_notes_<event>` for charge events). Do not invent a third prefix.
- Gate any expensive or opt-in feature with `isset($wp_filter['<name>'])` before building its input, and return the unmodified value when absent.
- Pass a fully-defaulted object or array into `apply_filters`, so an integrator only overrides what they mean to.
- Validate the filter's return value at the call site. A missing required key is an `\InvalidArgumentException` with the field name in the message — do not silently drop it.
- Fall back to the default when a value filter returns something empty.
- Filter names are literal strings at the call site (there is no constants registry for them) — they are public API, so treat a rename as a breaking change.
- Every new public filter or action needs: a page in `docs/filters-actions/`, a link from `docs/README.md`, and a test that exercises both the filtered and unfiltered paths (see `tests/Concrete/WoocommerceCoreSetupTest.php` and `WoocommercePlatformOrderDecoratorTest.php`, which cover with-filter, without-filter, and malformed-filter).
- Monetary values crossing this boundary are **integer cents** — document it, as `docs/filters-actions/split.md` does.

## Examples from this codebase

File: `src/Concrete/WoocommerceCoreSetup.php` — both filter shapes side by side: the gated config-object filter (`pagarme_marketplace_config`) and the simple value filter with fallback (`pagarme_hub_app_key`).

File: `src/Concrete/WoocommercePlatformOrderDecorator.php` — `handleSplitOrder()` + `validateSellerArray()`: gate, filter with two arguments (`$order`, `$paymentMethod`), validate, then map into the core lib's `Split` aggregate.

File: `src/Controller/Webhooks.php` — the action-broadcast path, including the idempotency marker written before dispatch so a redelivered webhook does not re-fire integrator code.

File: `docs/filters-actions/split.md` — the documentation shape: numbered steps, a full `add_filter` example per hook, inline comments naming allowed values, and an explicit note that amounts are integers in cents.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`isset($wp_filter[...])` reads a WordPress internal.** `has_filter('pagarme_split_order')` is the public API for exactly this check and does not depend on the global's shape. Both split gates use the global.
- **`apply_filters('pagarme_split_order', $order, $paymentMethod)` passes the order as the *filtered value*** and expects an unrelated array back. Integrators must write `function alimentarSplit(\WC_Order $order, $paymentMethod)` and return a `['sellers' => ..., 'marketplace' => ...]` array — the filter's input type and output type differ, which is not what `apply_filters` semantics suggest. `apply_filters('pagarme_split_order', $defaultSplitArray, $order, $paymentMethod)` would have been the conventional signature.
- **`validateSellerArray()` assumes `$splitDataFromOrder['sellers']` exists** and is iterable. A filter that returns `null`, a string, or an array without a `sellers` key produces a PHP warning or a `foreach` on non-iterable rather than the intended `InvalidArgumentException`. `marketplace` is never validated at all.
- **`InvalidArgumentException` thrown mid-checkout** from a decorator the core lib calls has no handler in this plugin — a store owner's typo in a split filter surfaces as a fatal during payment, not as a checkout notice.
- **The default hub app key is a hardcoded UUID in source.** It is a public app key, not a secret, but it means the "default" is invisible to anyone reading the docs.
- **Only `split.md` exists** under `docs/filters-actions/`, so `pagarme_hub_app_key` and the entire `on_pagarme_*` action family are undocumented public API — discoverable only by grepping `do_action` in `src/`.
- **`on_pagarme_{$event}` payload arity varies by branch**: charge events receive `($body)` while order events receive `($order, $body)`. An integrator hooking a name they saw in a log cannot know the signature without reading `Webhooks::handle_requests`.
