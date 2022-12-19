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

$voucher = new \PagarmeCoreApiLib\Models\CreateVoucherPaymentRequest();
$voucher->capture = True;
$voucher->installments = 2;
$voucher->statementDescriptor = "test descriptor";
$voucher->card = new \PagarmeCoreApiLib\Models\CreateCardRequest();
$voucher->card->number = "4000000000000010";
$voucher->card->holderName = "Tony Stark";
$voucher->card->expMonth = 1;
$voucher->card->expYear = 2025;
$voucher->card->cvv = "123";

$request = new \PagarmeCoreApiLib\Models\CreateOrderRequest();

$request->items = [new \PagarmeCoreApiLib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \PagarmeCoreApiLib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "voucher";
$request->payments[0]->voucher = $voucher;
$request->customer = $customer;

$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);