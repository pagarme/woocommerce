---
tags: [jquery, legacy-checkout, tokenization, data-attributes, localize-script]
modules: [assets/javascripts/front/checkout/, assets/javascripts/admin/]
confidence: inferred
applies_to: [components, handlers]
---
# Pattern: Legacy Checkout JavaScript

<!-- vibeflow:auto:start -->
## What

The shortcode (non-Blocks) checkout and the whole admin UI use pre-module JavaScript: one global object literal per file, jQuery for everything, DOM discovery by `data-*` attribute selectors declared as properties, and a `start()` method invoked at the bottom of the file. No build step — these files are enqueued as-is from `assets/javascripts/`.

This is still the default checkout for most stores. It is not deprecated, and it must stay behaviourally in sync with `reactCheckout/`.

## Where

`assets/javascripts/front/checkout/` — `model/payment/{card,pix,billet,voucher,two-cards,googlepay,order-value}.js`, `model/payment/card/{tokenize,tds,tdsToken,initTds,wallet}.js`, `model/multicustomers.js`, `checkoutFields.js`. `assets/javascripts/admin/` — `pagarme_payments.js`, `pagarme_payments_validation.js`, `pagarme_settings.js`, `sales/order/view/cancel-capture.js`.

## The Pattern

**1. One `let pagarmeX = { ... };` object per file, ending in `pagarmeX.start();`.** Selectors are properties at the top, so nothing is hunted for inline:

```js
/*jshint esversion: 6 */
let pagarmePix = {
    qrRawCodeTarget: '#pagarme-qr-code-button',
    start: function () {
        this.addEventListener();
    },
    addEventListener: function () {
        jQuery(this.qrRawCodeTarget).on('click', function (e) {
            pagarmePix.copyRawCode();
        });
    },
    copyRawCode: function () { ... }
};
pagarmePix.start();
```

**2. Every file opens with a jshint pragma,** and with `/* globals ... */` when it depends on another module or on localized data (`.jshintrc` / `.jshintignore` are the config):

```js
/* globals wc_pagarme_checkout */
/* jshint esversion: 11 */
```

```js
/* globals pagarmeCard */
/* jshint esversion: 8 */
```

**3. The DOM contract is `data-*` attributes, declared as selector properties.** Classes and ids are not used for behaviour:

```js
let pagarmeCard = {
    tokenExpirationAttribute: 'data-pagarmecheckout-expiration',
    cardNumberTarget: 'input[data-element="pagarme-card-number"]',
    cardHolderNameTarget: 'input[data-element="card-holder-name"]',
    cardExpiryTarget: 'input[data-element="card-expiry"]',
    cardCvvTarget: 'input[data-element="card-cvv"]',
    brandTarget: 'input[data-pagarme-element="brand-input"]',
    installmentsTarget: '[data-pagarme-component="installments"]',
    fieldsetCardElements: 'fieldset[data-pagarmecheckout="card"]',
```

The templates emit the matching attributes, so a rename must happen in both places. Three prefixes are in use — `data-element`, `data-pagarme-element`, `data-pagarmecheckout*` — and they are not interchangeable.

**4. PHP config arrives via `wp_localize_script`,** under a global the JS declares in its `globals` pragma. The block declares the script; the block localizes the data (see `block-template-rendering.md`):

```php
wp_localize_script(
    WCMP_JS_HANDLER_BASE_NAME . 'card',
    'PagarmeGlobalVars',
    self::getLocalizeScriptArgs()
);
```

Globals in play: `wc_pagarme_checkout` (per-method config from `getConfigDataProvider()`), `PagarmeGlobalVars` (ajax url, locale, spinner, translated API errors), `wc_pagarme_googlepay`.

**5. Re-binding on `updated_checkout` is mandatory.** WooCommerce replaces the payment DOM on every cart/address change, which detaches handlers. Every module that binds to checkout fields pairs `addEventListener()` with a `renewEventListener()` and re-applies masks:

```js
addEventListener: function () {
    jQuery(document.body).on('updated_checkout', function () {
        pagarmeCard.renewEventListener();
        jQuery.applyDataMask(pagarmeCard.maskTargets);
        ...
    });

    jQuery(document).ready(function () {
        jQuery('form.checkout').on('checkout_place_order', function (event) {
            pagarmeCard.fillCardBrandIfEmpty();
            return pagarmeCard.canExecute(event);
        });
        jQuery('form#order_review').on('submit', function (event) {
            pagarmeCard.fillCardBrandIfEmpty();
            return pagarmeCard.canExecute(event);
        });
    });
```

Note both forms are hooked: `form.checkout` (normal checkout) and `form#order_review` (pay-for-order). Miss one and the flow breaks on order-pay URLs.

**6. Submission is gated by returning `false` from `checkout_place_order`,** then re-submitting once tokenization resolves. Tokenization posts card data straight to the Pagar.me tokens API from the browser — card numbers never reach the server:

```js
let pagarmeTokenize = {
    appId: jQuery('[data-pagarmecheckout-app-id]').data('pagarmecheckoutAppId'),
    apiUrl: 'https://api.pagar.me/core/v5/tokens',
    ...
    execute: async function () {
        let el = pagarmeCard.getCheckoutPaymentElement();
        if (pagarmeCard.isPagarmePayment() && pagarmeCard.haveCardForm(el) !== false) {
            pagarmeTokenize.getCardsForm(el).each(await pagarmeTokenize.tokenize);
        }
    },
```

The resulting token is written into a hidden input (`createTokenInput`) whose name comes from the block's `getElementId()`, so it arrives in `$_POST` where `PostFormatter` expects it.

**7. Cross-module calls are guarded by a typeof check,** since enqueueing is conditional per payment method:

```js
if (typeof pagarmeCheckoutWallet == 'object') {
    pagarmeCheckoutWallet.addEventListener();
}
if (typeof pagarmeOrderValue == 'object') {
    pagarmeOrderValue.start();
}
```

**8. Guard clauses over nesting, and normalize the event/element argument** — several modules accept "jQuery event, jQuery object, or DOM node" and coerce:

```js
formatEventToJQuery: function (event) {
    if (event instanceof jQuery.Event) {
        return jQuery(event.currentTarget);
    }
    if (!(event instanceof jQuery)) {
        return jQuery(event);
    }
    return event;
},
```

**9. User-visible feedback is SweetAlert2** (`swal.fire({icon, text})`), enqueued globally by `Core::enqueue_scripts`. Field-level errors go into the `#wcmp-checkout-errors` container.

## Rules

- One object literal per file, named `pagarme<Thing>`, with `start()` last in the object and `pagarme<Thing>.start();` as the final line.
- Declare every selector as a property at the top of the object. Never inline a selector string in a method.
- Target behaviour with `data-*` attributes, not classes or ids; keep the attribute in the template and the selector property in sync.
- Open every file with a `/* jshint esversion: N */` pragma and a `/* globals ... */` line for each external global.
- Any handler bound to checkout DOM must also be re-bound from `renewEventListener()`, called on `document.body`'s `updated_checkout`. Re-apply `jQuery.applyDataMask()` there too.
- Hook both `form.checkout` `checkout_place_order` and `form#order_review` `submit`.
- Reference the module by its global name inside callbacks (`pagarmeCard.foo()`), not `this` — `this` is the DOM node in jQuery callbacks.
- Guard optional module interaction with `typeof pagarmeX == 'object'`.
- Config comes from PHP through `wp_localize_script`; never hardcode a URL, key, or user-facing string that PHP already knows. Card fields must be tokenized client-side — raw PAN/CVV must never be submitted to WordPress.
- Match the file's existing style: `let` at module scope, `function () {}` (not arrows) for jQuery callbacks, `async`/`await` only in the tokenization modules.

## Examples from this codebase

File: `assets/javascripts/front/checkout/model/payment/pix.js` — the minimal complete module: one selector, `start()` → `addEventListener()`, one behaviour, secure-context branch with an `execCommand` fallback.

File: `assets/javascripts/front/checkout/model/payment/card.js` — the reference implementation at 565 lines: selector table, `isPagarmePayment()`/`isPagarmeCard()` guards, brand lookup, token lifecycle (`clearToken`/`isTokenized`/`checkTokenCard`), wallet detection, and the `addEventListener`/`renewEventListener` pair.

File: `assets/javascripts/front/checkout/model/payment/card/tokenize.js` — the tokenization contract: `appId` read from a `data-` attribute, direct POST to `api.pagar.me/core/v5/tokens`, per-fieldset iteration, token written back as a hidden input, `wc_pagarme_checkout.errorTokenize` as the cross-module failure flag.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **Hardcoded pt-BR strings**: `'Código copiado.'` in `pix.js`, `'Não foi possível gerar uma transação. Serviço indisponível.'` in `tokenize.js`. The translated equivalents already exist in `Block\Checkout\Form\Card::getCardErrorsMessagesTranslated()` and are localized into `PagarmeGlobalVars.checkoutErrors` — use those.
- **`PagarmeGlobalVars.checkoutErrors` is keyed by locale with only `pt_BR` populated**, so any other store locale gets no translated API errors at all.
- **Cross-module state via a mutable global** — `wc_pagarme_checkout.errorTokenize` is written by `tokenize.js` and read by `card.js`. There is no ownership and no reset guarantee between attempts.
- **Three competing data-attribute prefixes** (`data-element`, `data-pagarme-element`, `data-pagarmecheckout`) with no rule for which to use. Match the neighbouring field.
- **`isPagarmePayment()` returns `indexOf(...)` rather than a boolean**, so it can return `0` (falsy) for a method whose slug *starts* with `pagarme`. Callers compensate with `!== false` in some places and plain truthiness in others.
- **`document.execCommand('copy')` fallback** is deprecated in every current browser; only the `navigator.clipboard` branch is reliable.
- **`.jshintignore` exempts most of the tree** and there is no ESLint/Prettier for `assets/javascripts/` (only `e2e/` has ESLint), so style drift here is unchecked by CI.
