<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$orderController = $apiClient->getOrders();

$customer = new \PagarmeCoreApiLib\Models\CreateCustomerRequest();
$customer->name = "sdk customer test";

$debitCard = new \PagarmeCoreApiLib\Models\CreateDebitCardPaymentRequest();
$debitCard->statementDescriptor = "test descriptor";
$debitCard->card = new \PagarmeCoreApiLib\Models\CreateCardRequest();
$debitCard->card->number = "4000000000000010";
$debitCard->card->holderName = "Tony Stark";
$debitCard->card->expMonth = 1;
$debitCard->card->expYear = 2025;
$debitCard->card->cvv = "123";

$request = new \PagarmeCoreApiLib\Models\CreateOrderRequest();

$request->items = [new \PagarmeCoreApiLib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \PagarmeCoreApiLib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "debit_card";
$request->payments[0]->debitCard = $debitCard;
$request->customer = $customer;

$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);