<?php
//This example was built using MundiAPI-PHP SDK.
//For more information, please refer to https://github.com/mundipagg/MundiAPI-PHP

$secretKey = 'YOUR SECRET KEY'; //the secret key will be provided by MundiPagg.
$basicAuthPassword = ''; //fill it with an empty string

$apiClient = new \MundiAPILib\MundiAPIClient(
    $secretKey,
    $basicAuthPassword
);

$orderController = $apiClient->getOrders();

$customer = new \MundiAPILib\Models\CreateCustomerRequest();
$customer->name = "sdk customer test";
$customer->email = "tonystark@avengers.com";
$customer->address = new \MundiAPILib\Models\CreateAddressRequest();
$customer->address->street = "Malibu Point";
$customer->address->number = "10880";
$customer->address->zipCode = "90265";
$customer->address->neighborhood = "Central Malibu";
$customer->address->city = "Malibu";
$customer->address->state = "CA";
$customer->address->country = "US";

$boleto = new \MundiAPILib\Models\CreateBoletoPaymentRequest();
$boleto->bank = "033";
$boleto->instructions = "Pagar até o vencimento";
$boleto->dueAt = new \DateTime('2019-12-31T00:00:00Z');

$request = new \MundiAPILib\Models\CreateOrderRequest();

$request->items = [new \MundiAPILib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \MundiAPILib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "boleto";
$request->payments[0]->boleto = $boleto;
$request->customer = $customer;

$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);