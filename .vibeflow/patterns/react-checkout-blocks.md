---
tags: [woocommerce-blocks, react, wordpress-data, state-management, webpack]
modules: [src/Block/ReactCheckout/, assets/javascripts/front/reactCheckout/]
applies_to: [components, hooks, blocks]
confidence: inferred
---
# Pattern: React Checkout (WooCommerce Blocks)

<!-- vibeflow:auto:start -->
## What

The Cart & Checkout Blocks checkout — WooCommerce's React checkout — is a completely separate frontend from the legacy shortcode checkout. Each payment method needs four coordinated pieces:

1. a PHP class in `src/Block/ReactCheckout/` extending `AbstractPaymentMethodType`, which ships settings to the browser;
2. a JS entry that calls `registerPaymentMethod()` with a component and a label;
3. a `webpack.config.js` entry so the bundle lands at `build/<key>.js`;
4. optionally a `@wordpress/data` redux store for form state.

The PHP class is discovered automatically by namespace (see `runtime-class-discovery.md`); the webpack entry is **not** — forgetting it produces a payment method that renders nothing.

## Where

`src/Block/ReactCheckout/` — `AbstractPaymentMethodBlock`, `AbstractCard`, `AbstractPaymentWithCheckoutInstructionsBlock`, and the concrete `CreditCard`, `Pix`, `Billet`, `GooglePay`. JS in `assets/javascripts/front/reactCheckout/payments/`. Output in `build/` (committed). Registration in `Model\FeatureCompatibilization::addSupportedBlocks()`, on `woocommerce_blocks_loaded`.

## The Pattern

**1. The PHP class is thin: identity plus a payment model.** All three concrete classes look like this:

```php
class Pix extends AbstractPaymentWithCheckoutInstructionsBlock
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-pix';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'pix';

    /** @var string */
    const ARIA_LABEL = 'Pix payment method';

    /** @var PixModel */
    protected $paymentModel;

    public function __construct()
    {
        $paymentModel = new PixModel();
        parent::__construct($paymentModel);
    }
}
```

`$name` must equal `'woo-pagarme-payments-' . PAYMENT_METHOD_KEY` — it is the gateway id WooCommerce matches on. `PAYMENT_METHOD_KEY` doubles as the webpack entry name and the `build/<key>.js` filename.

**2. `AbstractPaymentMethodBlock` handles the rest.** The base payload is fixed; subclasses extend it through one hook:

```php
public function get_payment_method_data()
{
    $paymentData = [
        'name'      => $this->name,
        'key'       => static::PAYMENT_METHOD_KEY,
        'label'     => $this->settings['title'] ?? __($this->paymentModel->getName(), 'woo-pagarme-payments'),
        'ariaLabel' => __( static::ARIA_LABEL, 'woo-pagarme-payments' )
    ];

    $additionalPaymentData = $this->getAdditionalPaymentMethodData();

    return array_merge( $paymentData, $additionalPaymentData );
}

protected function jsUrl()
{
    return Core::plugins_url( 'build/' . static::PAYMENT_METHOD_KEY . '.js' );
}

protected function getScriptDependencies()
{
    return [ 'wp-components', 'react' ];
}
```

`initialize()` pulls the gateway's saved settings: `$this->settings = $this->paymentModel->getSettings();`.

**3. `getAdditionalPaymentMethodData()` is the only place method-specific config is added,** and it is where every user-facing string for the React side is defined — labels and validation messages are translated in PHP, not in JS:

```php
protected function getAdditionalPaymentMethodData() {
    $additionalData = [
        'walletEnabled'    => $this->isWalletEnabled(),
        'installmentsType' => intval( $this->config->getCcInstallmentType() ?? 1 ),
        'appId'            => $this->config->getPublicKey(),
        'installments'     => $this->getInstallments(),
        'fieldsLabels'     => $this->getFieldsLabels(),
        'brands'           => $this->paymentModel->getConfigDataProvider()['brands'],
        'errorMessages'    => CardBlock::getCardErrorsMessagesTranslated(),
        'cards'            => $this->paymentModel->getCards(),
        'fieldErrors'      => $this->getFieldErrors()
    ];
    ...
}
```

For instruction-only methods the whole thing is two keys:

```php
protected function getAdditionalPaymentMethodData()
{
    return [
        'instructions' => $this->paymentModel->getMessage(),
        'logo' => $this->paymentModel->getImage()
    ];
}
```

**4. On the JS side, read the payload from `wc.wcSettings` at module scope,** keyed `<$name>_data`:

```js
const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting("woo-pagarme-payments-pix_data");
```

**5. Register a plain object with `content`, `edit`, `label`, and `supports`.** `content` and `edit` are the same component; `supports.features` lists the WooCommerce Subscriptions capabilities:

```js
const pagarmePixPaymentMethod = {
    name: backendConfig.name,
    label: <PagarmePixLabel />,
    content: <PagarmePixComponent />,
    edit: <PagarmePixComponent />,
    canMakePayment: () => true,
    ariaLabel: backendConfig.ariaLabel,
    supports: {
        features: [
            'products',
            'subscriptions',
            'subscription_cancellation',
            ...
        ],
    }
};

registerPaymentMethod(pagarmePixPaymentMethod);
```

**6. All checkout-submit logic lives in a `useX` hook** that registers an `onPaymentSetup` callback and returns its unsubscribe function from `useEffect`. The return shape — `{type, meta: {paymentMethodData}}` — is the contract with WooCommerce:

```js
const usePaymentWithInstructions = (emitResponse, eventRegistration, backendConfig) => {
    const { onPaymentSetup } = eventRegistration;

    useEffect(() => {
        return onPaymentSetup(() => {
            const paymentMethodData = {
                payment_method: backendConfig.key,
            };

            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: { paymentMethodData },
            };
        });
    }, [onPaymentSetup, backendConfig]);
};
```

For methods with form data, the payload is JSON-stringified under a `pagarme` key — this is what `PostFormatter::formatReactCheckout()` decodes server-side:

```js
return {
    type: emitResponse.responseTypes.SUCCESS,
    meta: {
        paymentMethodData: {
            pagarme: JSON.stringify({
                [backendConfig.key]: { cards: { ...formatedCards } },
            }),
            payment_method: backendConfig.key,
        },
    },
};
```

Errors return `{type: emitResponse.responseTypes.ERROR, message}` — never throw out of the callback.

**7. Form state is a `@wordpress/data` redux store,** one file per concern in `payments/store/`, registered at import time:

```js
import { createReduxStore, register } from "@wordpress/data";

const actions = {
    setNumber(cardIndex, number) {
        return { type: "SET_PROPERTY_VALUE", cardIndex, value: number, propertyName: "number" };
    },
    ...
    reset() { return { type: "RESET" }; }
};

const pagarmeCardsStore = createReduxStore("pagarme-cards", {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case "SET_PROPERTY_VALUE":
                if (action.propertyName?.length === 0) { return state; }
                return {
                    ...state,
                    cards: {
                        ...state.cards,
                        [action.cardIndex]: { ...state.cards[action.cardIndex], [action.propertyName]: action.value },
                    },
                };
            case "RESET":
                return DEFAULT_STATE;
        }
        return state;
    },
    actions,
    selectors: { ... },
});

register(pagarmeCardsStore);

export default pagarmeCardsStore;
```

One generic `SET_PROPERTY_VALUE` action type with a `propertyName` discriminator, plus `RESET`; a named action creator and a named selector per field. Components use `useDispatch(store)` / `useSelect(select => select(store).getX())`.

**8. Add the webpack entry** — this step is manual and easy to miss:

```js
entry: {
    pix: './assets/javascripts/front/reactCheckout/payments/Pix/index.js',
    billet: './assets/javascripts/front/reactCheckout/payments/Billet/index.js',
    credit_card: './assets/javascripts/front/reactCheckout/payments/CreditCard/index.js',
},
```

Then `yarn build`, which emits `build/<key>.js` plus a `build/<key>.asset.php` dependency manifest.

**9. The gateway must opt in.** `hasCheckoutBlocksSupport(): bool` on the `Controller\Gateways\*` class gates whether the method appears at all.

## Rules

- Directory structure is `payments/<Method>/index.js` + `payments/<Method>/use<Method>.js`; shared pieces go in `payments/Card/`, `payments/Common/`, `payments/store/`.
- `PAYMENT_METHOD_KEY` must match the webpack entry name, the `build/<key>.js` filename, and the payment model's `PAYMENT_CODE`. `$name` is `'woo-pagarme-payments-' . PAYMENT_METHOD_KEY`.
- Never add a `ReactCheckout` class without its JS entry **and** the webpack entry **and** a fresh `yarn build`. `jsUrl()` fails silently.
- All user-facing strings for the React checkout are defined in PHP (`getFieldsLabels()`, `getFieldErrors()`, `errorMessages`) and read from `backendConfig`. Do not hardcode copy in JSX.
- Read backend config once at module scope with `wc.wcSettings.getSetting('<name>_data')`; pass it down as the `backendConfig` prop.
- Submit logic goes in a `useX` hook, not in the component body. Return the `onPaymentSetup` unsubscribe function from `useEffect`, and list `onPaymentSetup` plus every value read in the deps array.
- Return errors as `{type: emitResponse.responseTypes.ERROR, message}`. Custom exceptions (`TokenizeException`) are caught in the hook and mapped to a message.
- Form data is sent as `paymentMethodData.pagarme` = a JSON string, plus a plain `payment_method` key.
- Stores live in `payments/store/<name>.js`, use `createReduxStore` + `register` at import time, expose a `reset()` action, and are `export default`ed.
- Every component declares `propTypes`; `@wordpress/element` supplies `useEffect` (not `react` directly).
- `src/Block/ReactCheckout/` is excluded from PHPUnit coverage (`phpunit.xml`) — logic worth testing belongs in the payment model, not here.

## Examples from this codebase

File: `src/Block/ReactCheckout/AbstractCard.php` — the richest `getAdditionalPaymentMethodData()`, plus `assembleCardsInfoToCheckoutBlock()` turning core-lib saved-card value objects into `{value, brand, label}` for the wallet dropdown.

File: `assets/javascripts/front/reactCheckout/payments/CreditCard/useCreditCard.js` — the full submit path: reset on mount, read cards from the store, short-circuit on Google Pay, aggregate per-card validation errors, tokenize, build `paymentMethodData`, map `TokenizeException` to a message.

File: `assets/javascripts/front/reactCheckout/payments/Pix/index.js` — the minimal registration: config at module scope, a component, a label, one `registerPaymentMethod` call.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`'Ou pague com cartão'` is hardcoded in `CreditCard/index.js`** — a raw pt-BR string in JSX, when every other label comes from `backendConfig` and the PHP text domain. The legacy template does the same thing correctly with `__("Or pay with card", 'woo-pagarme-payments')`.
- **`build/` is committed output.** Editing `assets/javascripts/front/reactCheckout/` without running `yarn build` leaves the store running stale code, and reviewers see a diff in generated files. Nothing in CI verifies the two are in sync.
- **`window.wc.wcBlocksRegistry` and `wc.wcSettings` are read at module scope with no guard.** If the bundle is enqueued outside a Blocks checkout, it throws on load rather than degrading.
- **`getAdditionalPaymentMethodData()` in `AbstractCard` reaches into `getConfigDataProvider()['brands']`** — an array index into another layer's payload, with no check that the key exists.
- **`errorMesage`** (single `s`) is the variable name in `useCreditCard.js`. Harmless, but it is copied into other hooks.
- **Google Pay state is a second store (`store/googlepay.js`) whose token short-circuits the card path** inside `useCreditCard`, so the credit-card hook owns Google Pay's submit behaviour too. The coupling is invisible from `GooglePay/index.js`.
