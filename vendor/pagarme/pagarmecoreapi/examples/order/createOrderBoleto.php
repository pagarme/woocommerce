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
$customer->email = "tonystark@avengers.com";
$customer->address = new \PagarmeCoreApiLib\Models\CreateAddressRequest();
$customer->address->street = "Malibu Point";
$customer->address->number = "10880";
$customer->address->zipCode = "90265";
$customer->address->neighborhood = "Central Malibu";
$customer->address->city = "Malibu";
$customer->address->state = "CA";
$customer->address->country = "US";

$boleto = new \PagarmeCoreApiLib\Models\CreateBoletoPaymentRequest();
$boleto->bank = "033";
$boleto->instructions = "Pagar até o vencimento";
$boleto->dueAt = new \DateTime('2019-12-31T00:00:00Z');

$request = new \PagarmeCoreApiLib\Models\CreateOrderRequest();

$request->items = [new \PagarmeCoreApiLib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \PagarmeCoreApiLib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "boleto";
$request->payments[0]->boleto = $boleto;
$request->customer = $customer;

$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);