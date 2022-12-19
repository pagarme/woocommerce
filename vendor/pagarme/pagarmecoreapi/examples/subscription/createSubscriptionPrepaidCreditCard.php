<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$subscriptionsController = $apiClient->getSubscriptions();

$request = new \PagarmeCoreApiLib\Models\CreateSubscriptionRequest();
$request->paymentMethod = "credit_card";
$request->currency = "BRL";
$request->interval = "month";
$request->intervalCount = 3;
$request->billingType = "prepaid";
$request->installments = 3;
$request->minimumPrice = 10000; // this value should be in cents
$request->boletoDueDays = 5;

$request->customer = new \PagarmeCoreApiLib\Models\CreateCustomerRequest();
$request->customer->name = "Tony Stark";
$request->customer->email = "tonystark@avengers.com";

$request->card = new \PagarmeCoreApiLib\Models\CreateCardRequest();
$request->card->holderName = "Tony Stark";
$request->card->number = "4000000000000010";
$request->card->expMonth = 1;
$request->card->expYear = 26;
$request->card->cvv = "903";
$request->card->billingAddress = new \PagarmeCoreApiLib\Models\CreateAddressRequest();
$request->card->billingAddress->line1 = "375  Av-> General Justo  Centro";
$request->card->billingAddress->line2 = "8º andar";
$request->card->billingAddress->zipCode = "20021130";
$request->card->billingAddress->city = "Rio de Janeiro";
$request->card->billingAddress->state = "RJ";
$request->card->billingAddress->country = "BR";

$request->discounts = [new \PagarmeCoreApiLib\Models\CreateDiscountRequest()];
$request->discounts[0]->cycles = 3;
$request->discounts[0]->value = 10;
$request->discounts[0]->discountType = "percentage";

$request->increments = [new \PagarmeCoreApiLib\Models\CreateIncrementRequest()];
$request->increments[0]->cycles = 2;
$request->increments[0]->value = 20;

$request->items = [
    new \PagarmeCoreApiLib\Models\CreateSubscriptionItemRequest(),
    new \PagarmeCoreApiLib\Models\CreateSubscriptionItemRequest()];

$request->items[0]->description = "Musculação";
$request->items[0]->quantity = 1;
$request->items[0]->pricingScheme = new \PagarmeCoreApiLib\Models\CreatePricingSchemeRequest();
$request->items[0]->pricingScheme->price = 18990; // this value should be in cents

$request->items[1]->description = "Matrícula";
$request->items[1]->quantity = 1;
$request->items[1]->cycles = 1;
$request->items[1]->pricingScheme = new \PagarmeCoreApiLib\Models\CreatePricingSchemeRequest();
$request->items[1]->pricingScheme->price = 5990; // this value should be in cents

$result = $subscriptionsController->createSubscription($request);

echo json_encode($result, JSON_PRETTY_PRINT);