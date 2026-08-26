---
tags: [testing, phpunit, mockery, brain-monkey, mocking]
modules: [tests/]
applies_to: [tests]
confidence: inferred
---
# Pattern: Unit Testing

<!-- vibeflow:auto:start -->
## What

PHPUnit 10.5 with **no WordPress loaded**. `tests/bootstrap.php` defines the `WCMP_*` constants and a fake `WC_Logger`, and that is the entire environment. Every WordPress function, every WooCommerce class, and every global the code under test touches must be faked — Brain Monkey for functions, Mockery for classes, plain `global` assignment for superglobal-ish state.

The tests mirror `src/` one-to-one, and are the only place the "optional constructor parameter defaulting to `new`" convention pays off.

## Where

`tests/` mirroring `src/`: `tests/Block/Checkout/GatewayTest.php`, `tests/Concrete/WoocommerceCoreSetupTest.php`, `tests/Controller/HubCommandTest.php`, `tests/Model/Payment/CreditCardTest.php`, `tests/Service/AccountServiceTest.php`, and so on. Config in `phpunit.xml`; bootstrap in `tests/bootstrap.php`.

## The Pattern

**1. Namespace and naming mirror the source.** `src/Service/AccountService.php` → `tests/Service/AccountServiceTest.php`, namespace `Woocommerce\Pagarme\Tests\Service`, class `AccountServiceTest`, methods `test<Method><Condition>Should<Outcome>`:

```php
public function testGetAccountShouldCallAccountServiceAndValidateAccountSettings()
public function testSaveIdentifiersFromWebhookWithEmptyBodyShouldReturn()
public function testGetConfigDataProviderWithOrderPayShouldDisableJavascriptTdsEnabledConfig()
```

**2. `setUp`/`tearDown` are symmetric.** Brain Monkey up, Mockery down:

```php
public function setUp(): void
{
    parent::setUp();
    Brain\Monkey\setUp();

    Brain\Monkey\Functions\stubs([
        'add_action' => null,
        'get_option' => false,
    ]);
    ...
}

public function tearDown(): void
{
    parent::tearDown();
    Mockery::close();
    Brain\Monkey\tearDown();
}
```

`use Brain;` and then the fully-qualified `Brain\Monkey\...` calls is the house style — not `use Brain\Monkey\Functions;`.

**3. Stub WP functions with `Functions\stubs([...])`** — a map of name to return value. Return an object or a mock when the code expects one:

```php
$orderMock = Mockery::mock(WC_Order::class);
$orderMock->shouldReceive('get_total')->andReturn(10);

Brain\Monkey\Functions\stubs([
    'wc_get_order' => $orderMock
]);
```

For a function whose behaviour matters, use `Functions\when(...)->alias(...)`:

```php
Brain\Monkey\Functions\when('apply_filters')
    ->alias(function($filter, $value, ...$args) {
        if ($filter === 'pagarme_marketplace_config' && $value !== null) {
            $value->mainRecipientId = "re_xxxxxxxxx0x00000xxxx000xx";
        }
        return $value;
    });
```

**4. Globals are set directly.** The code reads `global $wp`, `global $wp_filter`, `WC()->cart` — so the test builds them:

```php
global $wp;
$wp = new stdClass;
$wp->query_vars = ['order-pay' => 1];
```

```php
$wcCheckoutMock = Mockery::mock(WC_Cart::class);
$wcCheckoutMock->total = 20;
$woocommerce = new stdClass();
$woocommerce->cart = $wcCheckoutMock;
Brain\Monkey\Functions\stubs(['WC' => $woocommerce]);
```

```php
global $wp_filter;
$wp_filter['pagarme_marketplace_config'] = true;
... 
unset($wp_filter['pagarme_marketplace_config']);   // always clean up
```

**5. Three Mockery modes, each for a different coupling:**

| Mode | When | Example |
|---|---|---|
| `Mockery::mock(Class::class)` | the collaborator is injected | `Mockery::mock(Config::class)` passed into the constructor |
| `Mockery::mock('alias:Fully\Qualified')` | static calls on a class not otherwise loaded | `Mockery::mock('alias:' . Utils::class)` then `shouldReceive('isCurrentUserAdmin')` |
| `Mockery::mock('overload:Fully\Qualified')` | the class is `new`-ed inside the method under test | `Mockery::mock('overload:Pagarme\Core\Middle\Proxy\AccountProxy')` |

Because `alias:` and `overload:` install a class definition process-wide, those classes carry:

```php
/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
```

**6. Assert on arguments with `withArgs(fn)`** when the interesting part is the shape of what was passed, not the return:

```php
$accountMock->shouldReceive('validate')
    ->withArgs(function ($storeConfig) use ($availablePayments, $siteUrl) {
        return $storeConfig->isSandbox()
            && current($storeConfig->getStoreUrls()) === $siteUrl
            && $storeConfig->getEnabledPaymentMethods() === $availablePayments;
    })
    ->andReturnSelf();
```

Combine with `->once()` when the call count is the assertion:

```php
$this->settingsMock
    ->shouldReceive('addData')
    ->once()
    ->withArgs(function (array $keys) {
        return array_key_exists(Config::PAYMENT_PROFILE_ID, $keys)
            && $keys[Config::PAYMENT_PROFILE_ID] === null;
    })
    ->andReturnSelf();
$this->settingsMock->shouldReceive('save')->once();
```

**7. Reach past encapsulation with reflection** when a controller builds its own dependency and there is no seam:

```php
$this->hubCommand = new HubCommand();

$reflection = new ReflectionClass($this->hubCommand);
$settingsProperty = $reflection->getProperty('settings');
$settingsProperty->setAccessible(true);
$settingsProperty->setValue($this->hubCommand, $this->settingsMock);
```

**8. Table-driven tests use the `#[DataProvider]` attribute** (PHPUnit 10 form, not the annotation), with Arrange/Act/Assert comments and a message on the assertion:

```php
#[DataProvider('onlyNumbersProvider')]
public function only_numbers_WithVariousInputs_ShouldReturnOnlyDigits(string $input, string $expected)
{
    // Arrange & Act
    $result = Utils::only_numbers($input);

    // Assert
    $this->assertEquals($expected, $result, "Input '{$input}' should return '{$expected}'");
}
```

## Rules

- Test path, namespace, and class name mirror the source; the class name ends in **`Test`** (singular).
- **Test methods must start with `test`** or carry an explicit `#[Test]` attribute. Anything else silently never runs — see the anti-patterns.
- Always pair `Brain\Monkey\setUp()` in `setUp()` with `Brain\Monkey\tearDown()` and `Mockery::close()` in `tearDown()`, and call `parent::` in both.
- Stub every WP/WC function the code path touches. There is no WordPress: an unstubbed function is a fatal, not a skipped assertion.
- Prefer injecting a `Mockery::mock(Class::class)` through the constructor. Reach for `alias:`/`overload:` only when the source gives you no seam — and then add `@runTestsInSeparateProcesses` + `@preserveGlobalState disabled`.
- Set and **unset** globals you touch (`$wp`, `$wp_filter`) inside the test.
- Assert on argument shape with `withArgs(fn)` and on interaction counts with `->once()`. `andReturnSelf()` for fluent builders.
- Use `#[DataProvider]`, not `@dataProvider`. Provider methods are `public static`.
- Add a message to assertions in data-driven tests so a failure names the input.
- Do not test `src/Block/ReactCheckout/` — it is excluded from coverage in `phpunit.xml`. Put testable logic in the payment model instead.
- Run with `make test` (inside the container) or `vendor/bin/phpunit --filter <TestName>` for a single test.

## Examples from this codebase

File: `tests/Block/Checkout/GatewayTest.php` — the clearest illustration of faking the WP/WC environment: `global $wp` as a `stdClass`, `WC()` stubbed to an object carrying a mocked `WC_Cart`, `wc_get_order` stubbed to a mocked `WC_Order`, and the block constructed with mocks injected through its `$data` array.

File: `tests/Service/AccountServiceTest.php` — all three Mockery modes in one test: injected `Config`/`CoreAuth` mocks, `alias:` for the static `Utils` and `Account`, `overload:` for the `AccountProxy` that the service `new`s internally, plus a `withArgs` assertion on the assembled `StoreSettings`.

File: `tests/Concrete/WoocommerceCoreSetupTest.php` — testing a filter-driven code path: `$wp_filter` populated so the production `has_filter` check passes, `apply_filters` aliased to a closure that mutates the value, then a full `Config` `overload:` mock enumerating every getter the core setup reads.

File: `tests/Controller/HubCommandTest.php` — the reflection escape hatch for a controller that constructs its own `Config`, plus `->once()` + `withArgs` interaction assertions.
<!-- vibeflow:auto:end -->

## Anti-patterns

- **`tests/Helper/UtilsTests.php` and `tests/Helper/DocumentUtilsTests.php` contain 8 methods that never execute.** They are named `only_numbers_WithVariousInputs_ShouldReturnOnlyDigits`-style, carry `#[DataProvider]` but **no** `test` prefix and **no** `#[Test]` attribute, so PHPUnit 10 does not collect them. Both class names also end in `Tests` rather than `Test`. The suite passes and reports nothing wrong. Adding `#[Test]` (or a `test` prefix) is the fix; until then treat `Utils` and `DocumentUtils` as untested.
- **`ReflectionClass` + `setAccessible()` to swap a private property** (`HubCommandTest`) is a symptom, not a technique: `HubCommand` `new`s its own `Config`. The right fix is an optional constructor parameter, which is the convention everywhere else.
- **`overload:` and `alias:` mocks are process-global**, so any test file using them needs `@runTestsInSeparateProcesses`. Forgetting it produces failures in *unrelated* tests that depend on execution order — the most expensive failure mode in this suite.
- **`WoocommerceCoreSetupTest::getMockForConfiguration()` enumerates ~35 `Config` getters.** Adding a new config read to `WoocommerceCoreSetup` breaks this test with an obscure Mockery "received unexpected call" rather than a clear message.
- **Coverage is thin and uneven** — 14 test files against 202 source files. `Controller/Gateways/`, `Model/Checkout`, `Model/Payment/PostFormatter`, and most of `Concrete/WoocommercePlatformOrderDecorator` (the largest file in the repo) have no tests. The `$_POST`-mutation design is the main reason.
- **No JS tests at all.** Neither `assets/javascripts/front/checkout/` nor `assets/javascripts/front/reactCheckout/` has a test runner; the only automated frontend coverage is the `e2e/` Playwright suite, which needs a deployed site.
