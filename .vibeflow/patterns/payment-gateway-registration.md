---
tags: [payment-gateway, woocommerce, settings, form-fields, validation]
modules: [src/Controller/Gateways/, src/Controller/]
applies_to: [controllers, gateways, configs]
confidence: inferred
---
# Pattern: Payment Gateway Registration

<!-- vibeflow:auto:start -->
## What

Every payment method the store can offer is a `WC_Payment_Gateway` subclass in `src/Controller/Gateways/`, extending `AbstractGateway`. The subclass contributes three things: which payment model it is (`$method`), what admin settings it exposes (`append_form_fields()` + `field_*()` methods), and which capabilities it supports (`hasSubscriptionSupport()`, `hasCheckoutBlocksSupport()`, `addRefundSupport()`). Everything else — the checkout form, `process_payment`, thank-you page, refunds, e-mail details, legacy settings conversion — is inherited.

Gateways are never listed anywhere. `Controller\Settings::getGateways()` includes the directory and filters `get_declared_classes()`, so creating the file registers the method.

## Where

`src/Controller/Gateways/` — `Billet`, `BilletCreditCard`, `CreditCard`, `GooglePay`, `Pix`, `TwoCreditCard`, `Voucher`, all over `AbstractGateway`. Discovery and the `woocommerce_payment_gateways` filter live in `src/Controller/Settings.php`.

## The Pattern

**1. Declare the method from the payment model's constant — never a literal.**

```php
class Pix extends AbstractGateway
{
    /** @var string */
    protected $method = PixModel::PAYMENT_CODE;
```

`AbstractGateway::__construct` derives everything else from it: `$this->id = 'woo-pagarme-payments-' . $this->method;`, the title, the `woocommerce_thankyou_<id>` hook, the settings option name.

**2. Contribute settings via `append_form_fields()`, one `field_*()` method per field.**

`AbstractGateway::init_form_fields()` always supplies `enabled` and `title`, then merges your fields:

```php
public function init_form_fields()
{
    $this->form_fields['enabled'] = $this->field_enabled();
    $this->form_fields['title'] = $this->field_title();
    $this->form_fields = array_merge(
        $this->form_fields,
        $this->append_form_fields(),
        $this->append_gateway_form_fields()
    );
}
```

Each field returns a WooCommerce settings-field array whose `default` reads back through `Config`, so a value already saved by the Hub or a previous version wins over the hardcoded default:

```php
public function field_pix_qrcode_expiration_time()
{
    return [
        'title' => __(self::QR_CODE_EXPIRATION_TIME_FIELD_NAME, 'woo-pagarme-payments'),
        'type' => 'text',
        'description' => __('Expiration time in seconds of the generated pix QR code.', 'woo-pagarme-payments'),
        'desc_tip' => true,
        'placeholder' => 3600,
        'default' => $this->config->getData('pix_qrcode_expiration_time') ?? 3600,
        'custom_attributes' => [
            'data-mask' => '##0',
            'data-field-validate' => 'required|min',
            'data-min' => self::$minimumValueQrCodeExpirationTime,
            'data-error-message-required' => __('This field is required.', 'woo-pagarme-payments'),
        ]
    ];
}
```

`custom_attributes` starting with `data-field-validate` / `data-mask` are consumed by `assets/javascripts/admin/pagarme_payments_validation.js` for client-side validation and masking. Server-side validation is a separate `validate_<key>_field()` method (WooCommerce naming convention), which throws:

```php
/**
 * @throws InvalidOptionException
 */
public function validate_pix_qrcode_expiration_time_field($key, $value)
{
    $this->validateRequired($value, self::QR_CODE_EXPIRATION_TIME_FIELD_NAME);
    $this->validateMinValue($value, self::QR_CODE_EXPIRATION_TIME_FIELD_NAME, self::$minimumValueQrCodeExpirationTime);
    return $value;
}
```

A field with a custom widget adds `'type' => 'pix_additional_data'` plus a `generate_pix_additional_data_html($key, $data)` method that `ob_start()`s a `<tr>` — again, WooCommerce's own `generate_<type>_html` convention.

**3. Some options differ between PSP and Gateway integration types.** Fields returned from `gateway_form_fields()` are only merged when `isGatewayType()` is true for this method — that is what `append_gateway_form_fields()` gates.

**4. Declare capabilities by overriding, not by configuring.**

```php
public function addRefundSupport()          { $this->supports[] = 'refunds'; }
public function hasSubscriptionSupport(): bool  { return true; }
public function isSubscriptionActive(): bool    { return wc_string_to_bool($this->config->getData('pix_allowed_in_subscription') ?? true); }
public function hasCheckoutBlocksSupport(): bool { return true; }
```

The base class returns `false` from all three and `false` from `addRefundSupport()`.

**5. Legacy settings migration is declarative.** When a method existed in the pre-3.0 plugin, map the old option keys so `getOldConfiguration()` can fall back to them:

```php
const LEGACY_CONFIG_NAME = "woocommerce_pagarme-credit-card_settings";

const LEGACY_SETTINGS_NAME = [
    "cc_installments_maximum"     => "max_installment",
    "cc_installments_min_amount"  => "smallest_installment",
    "cc_installments_interest"    => "interest_rate",
];

const LEGACY_SETTINGS_NEEDS_CONVERSION = ["cc_installments_interest"];
```

A key listed in `LEGACY_SETTINGS_NEEDS_CONVERSION` is routed to a `convert<PascalCaseKey>()` method on the gateway (`convertCcInstallmentsInterest`).

**6. Saving mirrors into the core lib.** `AbstractGateway` hooks `update_option`/`add_option` and calls `saveAdminOptionsInCoreConfig($values)` so the core lib's configuration aggregate stays in sync. Never bypass `process_admin_options`.

## Rules

- `$method` is always a `PAYMENT_CODE` constant from the matching `Model\Payment\*` class.
- One `field_*()` method per settings field, returning an array. Private if the method is only reachable from `append_form_fields()`, public otherwise.
- Field keys are `snake_case` and are the same string used with `$this->config->getData(...)`.
- WordPress/WooCommerce-facing methods stay `snake_case` (`append_form_fields`, `validate_*_field`, `generate_*_html`, `process_payment`); plugin-internal helpers are `camelCase` (`hasCheckoutBlocksSupport`, `getOldConfiguration`).
- Server-side validation throws `InvalidOptionException` via the `validateRequired` / `validateMinValue` helpers on `AbstractGateway`. Do not `wp_die` or `add_error` yourself.
- Every field title, description, and error message goes through `__(..., 'woo-pagarme-payments')`. Reuse a `const <FIELD>_FIELD_NAME` when the same label is needed by both the field and its validator.
- Do not override `process_payment()`, `payment_fields()`, or `thank_you_page()` — the base class routes them into `PostFormatter` → `Model\Checkout` and into the block layer. Contribute behaviour through the payment model and blocks instead.
- Do not add the class to any list. Do not add an `if` in `Settings` for it.

## Examples from this codebase

File: `src/Controller/Gateways/Pix.php`

```php
class Pix extends AbstractGateway
{
    /** @var string */
    protected $method = PixModel::PAYMENT_CODE;

    public function append_form_fields()
    {
        $fields = [
            PixModel::getCheckoutInstructionsKey() => $this->field_pix_checkout_instructions(),
            'pix_qrcode_expiration_time' => $this->field_pix_qrcode_expiration_time(),
            'pix_additional_data' => $this->field_pix_additional_data(),
        ];
        if (Subscription::hasSubscriptionPlugin()) {
            $fields['pix_allowed_in_subscription'] = $this->field_pix_allowed_for_subscription();
        }
        return $fields;
    }

    public function hasCheckoutBlocksSupport(): bool
    {
        return true;
    }
}
```

File: `src/Controller/Settings.php` — the discovery side; note there is no registry to update, and that the only per-method branch is a config-driven exclusion.

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
```

File: `src/Controller/Gateways/AbstractGateway.php` — `process_payment` is the shared path every gateway inherits; the Blocks-vs-legacy error branch is here, not in subclasses.

```php
$process = $this->checkout->process($wooOrder);

if ($process instanceof CoreOrder) {
    return ['result' => 'success', 'redirect' => $this->get_return_url($wooOrder)];
}
$errorMessage = $this->getErrorMessage($process);

if (Utils::isCheckoutBlock()) {
    wp_die($errorMessage, 'error');
}

wc_add_notice($errorMessage, 'error');

return null;
```
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`Voucher` is excluded by a string match on the class name** in `Settings::getGateways()` (`strpos($class, "Voucher")`). A future gateway whose name happens to contain "Voucher" would be silently dropped when `getIsVoucherPSP()` is true. Capability checks belong on the gateway, not in the discovery loop.
- **`GooglePay` is removed from the gateway list by inspecting `$_POST`** in `Action\OrderActions::removeGooglepayOnlyWhenNotProcessPaymentAction()` via `woocommerce_available_payment_gateways`. Availability logic split between the gateway class and an action bundle is hard to find.
- **`checkout_transparent()` in `AbstractGateway` `require_once`s `templates/checkout/{$this->method}-item.php`** — files that no longer exist in `templates/checkout/`. `receipt_page()` therefore fatals if a gateway ever needs the receipt flow. Dead path; do not build on it.
- **`$this->has_fields` is set to `false`, then to `true`** a few lines later in the constructor, around `init_form_fields()`/`init_settings()`. The intermediate `false` matters only as a side-effect guard; it reads like a mistake.
