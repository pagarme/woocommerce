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

$voucher = new \MundiAPILib\Models\CreateVoucherPaymentRequest();
$voucher->capture = True;
$voucher->installments = 2;
$voucher->statementDescriptor = "test descriptor";
$voucher->card = new \MundiAPILib\Models\CreateCardRequest();
$voucher->card->number = "4000000000000010";
$voucher->card->holderName = "Tony Stark";
$voucher->card->expMonth = 1;
$voucher->card->expYear = 2025;
$voucher->card->cvv = "123";

$request = new \MundiAPILib\Models\CreateOrderRequest();

$request->items = [new \MundiAPILib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \MundiAPILib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "voucher";
$request->payments[0]->voucher = $voucher;
$request->customer = $customer;

$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);