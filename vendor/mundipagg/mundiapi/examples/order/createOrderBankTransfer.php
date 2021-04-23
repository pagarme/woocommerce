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

$bankTransfer = new \MundiAPILib\Models\CreateBankTransferPaymentRequest();
$bankTransfer->bank = "001";

$request = new \MundiAPILib\Models\CreateOrderRequest();

$request->items = [new \MundiAPILib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \MundiAPILib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "bank_transfer";
$request->payments[0]->bankTransfer = $bankTransfer;
$request->customer = $customer;

$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);