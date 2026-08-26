---
tags: [payment-methods, post-contract, dto, domain-model, brands]
modules: [src/Model/Payment/, src/Model/Payment/Data/]
applies_to: [models, services]
confidence: inferred
---
# Pattern: Payment Method Model

<!-- vibeflow:auto:start -->
## What

For each payment method there is a `Model\Payment\*` class that owns the method's *identity and data contract*: its code and display name, which `$_POST` keys belong to it, how those keys are renamed before the core lib sees them, and what configuration the checkout JS needs. `PostFormatter` and `Model\Checkout` are generic — they ask the payment model what to keep and how to rename it. The gateway (`Controller\Gateways\*`) is the WooCommerce-facing half; this is the domain half.

## Where

`src/Model/Payment/` — `CreditCard`, `Pix`, `Billet`, `BilletCard`, `TwoCards`, `Voucher`, `GooglePay`, `Card`, over `AbstractPayment` / `AbstractPaymentWithCheckoutInstructions`. Request DTOs in `src/Model/Payment/Data/`. Enumerable sub-registries in `CreditCard/Brands/`, `Voucher/Brands/`, `Billet/Banks/`.

## The Pattern

**1. Identity is four protected properties plus a `PAYMENT_CODE` constant.**

```php
class Pix extends AbstractPaymentWithCheckoutInstructions implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'pix';

    /** @var string */
    const IMAGE_FILE_NAME = 'pix.svg';

    /** @var int */
    protected $suffix = 7;

    /** @var string */
    protected $name = 'Pix';

    /** @var string */
    protected $code = self::PAYMENT_CODE;
```

`$suffix` is a stable numeric id used for ordering/legacy compatibility — pick an unused one. `AbstractPayment`'s getters throw if a property was left null (`return $this->suffix ?? $this->error($this->suffix);`), so all four must be set.

**2. `$requirementsData` is the POST allow-list.** `PostFormatter` intersects `$_POST` with it, so a checkout field that is not listed is silently discarded before it reaches the gateway:

```php
/** @var string[] */
protected $requirementsData = [
    'brand1',
    'pagarmetoken1',
    'installments_card',
    'multicustomer_card',
    'payment_method',
    'enable_multicustomers_card',
    'save_credit_card1',
    'card_id'
];
```

**3. `$dictionary` renames those keys** into what the core lib expects. Keys absent from the dictionary pass through unchanged:

```php
/** @var array */
protected $dictionary = [
    'installments_card' => 'installments',
    'brand1' => 'brand',
    'save_credit_card1' => 'save_credit_card'
];
```

Methods with no renaming declare `protected $dictionary = [];` explicitly rather than omitting it.

**4. `getConfigDataProvider()` is the PHP→JS bridge.** Whatever it returns is localized into the checkout scripts (both frontends). Override it to add method-specific config, always calling `parent::` first:

```php
public function getConfigDataProvider()
{
    global $wp;
    $jsConfigProvider = parent::getConfigDataProvider();
    $brands = new Brands;
    foreach ($brands->getBrands() as $class) {
        /** @var BrandsInterface $bank */
        $brand = new $class;
        $jsConfigProvider['brands'][$brand->getBrandCode()] = $brand->getConfigDataProvider();
    }
    $jsConfigProvider['tdsEnabled'] = Subscription::hasSubscriptionProductInCart()
        || Subscription::isChangePaymentSubscription()
        || isset($wp->query_vars['order-pay'])
        ? false
        : $this->getConfig()->isTdsEnabled();
    if ($jsConfigProvider['tdsEnabled']) {
        $jsConfigProvider['tdsMinAmount'] = $this->getConfig()->getTdsMinAmount();
    }
    return $jsConfigProvider;
}
```

**5. `getPayRequest()` builds the API payload,** delegating to the parent and then adjusting. Amounts are always integers in cents — run them through `Utils::format_order_price()`:

```php
public function getPayRequest(WC_Order $wc_order, array $form_fields, $customer = null)
{
    $request = [];
    $content = current(parent::getPayRequest($wc_order, $form_fields, $customer));
    $amount = Utils::str_to_float($this->getAmount($wc_order, $form_fields));
    $content['amount'] = Utils::format_order_price(
        $this->getPriceWithInterest(
            $amount,
            Utils::get_value_by($form_fields, 'installments'),
            Utils::get_value_by($form_fields, 'brand')
        )
    );
    if (!isset($content['customer']) && isset($customer->email)) {
        $content['customer'] = $customer;
    }
    $request[] = $content;
    return $request;
}
```

**6. Methods that only show static instructions extend `AbstractPaymentWithCheckoutInstructions`** and supply one static method; the settings field, checkout copy, and Blocks payload all read from it:

```php
public static function getDefaultCheckoutInstructions()
{
    return __(
        'The QR Code for your payment with PIX will be generated after confirming the purchase. '
        . 'Point your phone at the screen to capture the code or copy and paste the code into your '
        . 'payments app.',
        'woo-pagarme-payments'
    );
}
```

**7. Sub-registries (card brands, boleto banks, voucher brands) are one class per value** with only protected properties, discovered by reflection — see `runtime-class-discovery.md`:

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

## Rules

- `PAYMENT_CODE` is the single source of truth for the method's string id. Gateways, blocks, and React `PAYMENT_METHOD_KEY`s all reference it; never repeat the literal.
- Set all four of `$suffix`, `$name`, `$code`, `$requirementsData`. `$code` is always `self::PAYMENT_CODE`.
- Implement `PaymentInterface`, even when extending an abstract that already satisfies it — every concrete model declares it explicitly.
- A new checkout field is not wired until its POST key is in `$requirementsData`. If the frontend name differs from the API name, add the mapping to `$dictionary` rather than renaming in the template.
- `getConfigDataProvider()` overrides must call `parent::getConfigDataProvider()` and merge, never replace.
- Money is integer cents at the boundary: `Utils::format_order_price()` on the way out, `Utils::str_to_float()` on the way in.
- Read `$_POST`-derived values through `Utils::get_value_by($form_fields, 'key')`, not direct array access — the shape varies between the two checkouts.
- New brands/banks are new files in the `Brands/` or `Banks/` directory implementing the matching `*Interface`. Nothing else changes.

## Examples from this codebase

File: `src/Model/Payment/CreditCard.php` — the fullest example: allow-list, dictionary, JS config, payload override.

```php
class CreditCard extends Card implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'credit_card';

    /** @var int */
    protected $suffix = 1;

    /** @var string */
    protected $name = 'Credit Card';

    /** @var string */
    protected $code = self::PAYMENT_CODE;
```

File: `src/Model/Payment/PostFormatter.php` — the generic consumer. It asks the model for both the allow-list and the renaming, which is why the model is the only place either is declared.

```php
private function dataToFilterFromPost($paymentMethod)
{
    if ($paymentMethod) {
        return $this->gateway->getPaymentInstance($paymentMethod)->getRequirementsData();
    }
    return $_POST;
}

private function renameFieldsFromFormattedPost($formattedPost, $paymentMethod)
{
    foreach ($formattedPost['fields'] as $arrayFieldKey => $field) {
        $formattedPost = $this->applyForAllFields($field, $formattedPost, $arrayFieldKey);
        if ($paymentMethod) {
            $formattedPost = $this->gateway->getPaymentInstance($paymentMethod)
                ->renameFieldsPost($field, $formattedPost, $arrayFieldKey);
        }
    }
    return $formattedPost;
}
```

File: `src/Model/Payment/Pix.php` — the minimal shape for an instruction-only method: identity, an empty dictionary, one static string.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`PostFormatter` communicates by overwriting `$_POST`.** `format()` ends with `$_POST = $result;` and `assemblePaymentRequest()` writes a `PaymentRequest` object into `$_POST[PaymentRequest::PAGARME_PAYMENT_REQUEST_KEY]`. Payment models then read it back out. Nothing in this chain can be unit-tested without faking the superglobal, and ordering between `formatReactCheckout()`, `format()`, and `assemblePaymentRequest()` is implicit. Do not extend this mechanism; if you need to pass data, add a parameter.
- **`$requirementsData` keys carry the frontend's card index in the name** (`brand1`, `pagarmetoken1`, `save_credit_card1`) and are then renamed by `$dictionary`. `TwoCards` needs the `2` variants. The indexing convention is undocumented and easy to get wrong — copy an existing method's list rather than inventing keys.
- **`getBrands()` swallows every `ReflectionException`** with an empty `catch` while walking *all* declared classes. A brand that fails to load disappears from the checkout with no log line.
