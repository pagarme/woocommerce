---
tags: [view-layer, templates, blocks, composition, asset-enqueue]
modules: [src/Block/, templates/]
applies_to: [components, templates]
confidence: inferred
---
# Pattern: Block + Template Rendering

<!-- vibeflow:auto:start -->
## What

A Magento-style view layer. A *block* is a PHP class holding data and view logic; a *template* is a `.phtml` file that `include`s with `$this` bound to the block. Blocks compose by calling `createBlock()` on themselves from inside their own template. Blocks also declare which JS files they need and enqueue them at construction.

This covers the legacy (shortcode) checkout, the admin settings page and order metaboxes, the order/e-mail transaction details, and the thank-you pages. It does **not** cover `src/Block/ReactCheckout/` — see `react-checkout-blocks.md`.

## Where

`src/Block/` (all except `ReactCheckout/`) with views in `templates/`, mirroring the tree: `Block\Checkout\Form\Card` → `templates/checkout/form/card.phtml`, `Block\Order\Transaction\Pix` → `templates/order/transaction/pix.phtml`.

## The Pattern

**1. `AbstractBlock extends DataObject`** — so every block gets `getData()`/`setData()` and the `getFoo()`/`setFoo()` magic accessors. It also owns the template path and script enqueueing.

**2. `Template::_toHtml()` is the renderer.** It resolves the template to a real file, preferring `.php` then `.phtml`, and `include`s it — which is why templates address the block as `$this`:

```php
protected function _toHtml()
{
    $locale = Core::plugin_dir_path() . $this->getTemplate() . '.php';
    if (!file_exists($locale)) {
        $locale = Core::plugin_dir_path() . $this->getTemplate() . '.phtml';
        if (!file_exists($locale)) {
            return;
        }
    }
    include $locale;
}
```

Note it `include`s rather than buffering, so `toHtml()` **echoes** as a side effect and returns nothing useful. Call it for effect: `$block->toHtml();`, or `<?= $block->toHtml() ?>` — both appear and both work only because of the echo.

**3. A block declares its template and scripts as properties:**

```php
class Card extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/card';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/payment/card', 'checkout/model/payment/card/tokenize'];

    /** @var int  */
    protected $sequence = 1;
```

Script paths are relative to `assets/javascripts/{$areaCode}/` (`$areaCode` is `'front'` by default, `'admin'` for admin blocks) and are given without `.js`. `AbstractBlock::enqueue_scripts()` runs from the constructor, derives the handle as `WCMP_JS_HANDLER_BASE_NAME . basename` (→ `pagarme_scripts_card`), versions it with `Core::filemtime()`, and always merges `['jquery', 'jquery.mask']` into the dependencies.

**4. Passing config to JS: override `enqueue_scripts()`, call parent, then localize.**

```php
public function enqueue_scripts($scripts = null, $deps = [])
{
    parent::enqueue_scripts($scripts, $deps);

    wp_localize_script(
        WCMP_JS_HANDLER_BASE_NAME . 'card',
        'PagarmeGlobalVars',
        self::getLocalizeScriptArgs()
    );
}
```

**5. Composition happens inside the template,** via `createBlock(class, layoutName, arguments)`. The `data` key of `$arguments` is merged into the child's `DataObject`; a `template` key overrides the child's default template:

```php
<?= $this->createBlock(
    '\Woocommerce\Pagarme\Block\Checkout\Form\Multicustomers',
    'pagarme.checkout.form.multicustomers',
    [
        'payment_instance' => $this->getPaymentInstance(),
        'parent_element_id' => $this->getElementId('multicustomers'),
        'sequence' => $this->getMulticustomersSequece()
    ]
)->toHtml() ?>
```

The second argument is a dotted layout name (`pagarme.checkout.form.card`, `pagarme.checkout.thank-you`) — Magento vocabulary. It is set on the block but nothing dispatches on it today; keep the convention.

**6. Templates open with the same preamble every time** — team docblock, an `@var` line so editors resolve `$this`, `declare(strict_types=1)`, and the `add_action` guard:

```php
<?php
/**
 * @author      Open Source Team
 * ...
 */

/** @var \Woocommerce\Pagarme\Block\Checkout\Form\Pix $this */

declare( strict_types=1 );

if (!function_exists('add_action')) {
    exit(0);
}
?>
```

**7. View logic lives on the block, not in the template.** Templates call predicates and getters (`showOrderValue()`, `showInstallments()`, `getElementId('pix-value')`, `getMessage(true)`); the block decides. Field names are always built with `getElementId()`, never hand-assembled.

**8. Admin settings fields are a parallel, smaller hierarchy.** `Block\Adminhtml\System\Config\Form\AbstractField` does not extend `AbstractBlock`; it wraps `add_settings_field()` and renders `templates/adminhtml/system/config/form/field/*.phtml`, with `$template` holding a bare filename and `$templatePath` the directory:

```php
class Select extends AbstractField
{
    /** @var string */
    protected $template = 'select.phtml';

    public function elementCallBack()
    {
        if ($value = $this->config->getData($this->getId())) {
            $this->setCurrent($value);
        }
        parent::includeTemplate();
    }
}
```

Its `setData()` maps array keys onto setters (`'field_name'` → `setFieldName()`), calling only setters that exist.

## Rules

- Block namespace path and template path mirror each other. `$_template` is the path from the plugin root, without extension, `kebab-case` filename (`templates/checkout/payment/credit-card`).
- New views use `.phtml`. `.php` is only for the handful of legacy templates already there.
- Every template starts with the docblock, the `/** @var <BlockClass> $this */` line, `declare(strict_types=1);`, and the `add_action` guard.
- Escape on output: `esc_html_e()` / `esc_attr()` / `esc_html__()` for text and attributes, `wp_kses()` when HTML must survive (`Gateway::formatElement()` exists for this). `<?= $this->getX() ?>` is only acceptable where the getter already returns escaped or generated markup.
- Form field names and ids come from `getElementId()` so the `pagarme[<method>][...]` prefix stays consistent with what `PostFormatter` expects.
- Predicates are `showX()` returning bool with a `?? false`/`?? true` default read from `DataObject`; renderers are `getX($htmlFormat = false)`.
- Declare `$scripts` on the block rather than calling `wp_enqueue_script` in the template. Use `wp_localize_script` from an overridden `enqueue_scripts()`.
- `createBlock()` takes a leading-backslash FQCN string as its first argument (that is the existing convention) and a dotted layout name as its second.

## Examples from this codebase

File: `templates/checkout/payment/credit-card.phtml` — a payment template that localizes config, branches on settings, and composes two child blocks.

```php
if ( $this->getConfig()->getEnableGooglepay()  == "yes" && !$this->hasSubscriptionProductInCart() ) : ?>
    <div id="pagarme-googlepay"></div>
    ...
<?php
endif; // Enable or disable googlepay
$this->createBlock(
    '\Woocommerce\Pagarme\Block\Checkout\Form\Card',
    'pagarme.checkout.form.card',
    [
        'payment_instance' => $this->getPaymentInstance(),
        'qty_cards' => $this->getQtyCards()
    ]
)->toHtml();
```

File: `src/Block/Checkout/Gateway.php` — the base block for the whole legacy checkout. Note `getElementId()` (the field-naming contract) and `getPaymentClass()` (block class resolved from the method code).

```php
public function getElementId(string $id)
{
    if (!$this->getPaymentInstance()){
        return null;
    }
    return WCMP_PREFIX . '[' . $this->getPaymentInstance()->getMethodCode() . ']' . $id;
}
```

File: `templates/order/transaction/pix.phtml` — a variation: an order-detail fragment, guarded by a single getter, with no child blocks.

```php
<?php if ($this->getQrCodeUrl()) : ?>
    <tr>
        <th><?= __('QR Code', 'woo-pagarme-payments'); ?>:</th>
        <td>
            <p><img src="<?= $this->getQrCodeUrl() ?>" alt="Pix QRCode" class="pagarme-qr-code-img" /></p>
```
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`toHtml()` echoes instead of returning.** `Template::_toHtml()` `include`s the template, so output escapes immediately while the return value is `null`. Both `<?= $block->toHtml() ?>` and `$block->toHtml();` appear in templates and produce identical output. This makes it impossible to post-process a block's markup and means `AbstractBlock::toHtml()`'s `@return string` docblock is wrong.
- **`templates/order/transaction/pix.phtml` opens with a bare `</thead>`** and emits `<tr>`s. Fragments that only make sense spliced into a parent's table are fragile — the parent's markup and the child's must be read together.
- **`Block\Checkout\Gateway::numeralReplace()`** maps `1`/`2`/`3` to `one`/`two`/`tree` (sic) to turn a method code into a class name (`2-cards` → `TwoCards`). Deriving class names from user-visible codes via string surgery breaks the moment a code contains a digit that isn't 1–3.
- **`Block\Checkout\Form\Card::getCompoenent()`** — typo in a public method name, still referenced. Don't propagate it; don't rename it casually either.
- **`Block\Checkout\Form\Card` holds ~30 hardcoded API error strings** in `getCardErrorsMessagesTranslated()`, keyed by the exact English message the Pagar.me API returns. Any API wording change silently falls through to the untranslated default.
- **`AbstractField` does not extend `AbstractBlock`** even though it renders templates, so it duplicates the template-including and data-setting logic with different property names (`$template` + `$templatePath` vs `$_template`). Two view mechanisms in one directory tree.
