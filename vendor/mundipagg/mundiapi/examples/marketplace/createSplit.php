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
$customer->name = "sdk customer order";
$customer->email = "tonystark@avengers.com";

$creditCard = new \MundiAPILib\Models\CreateCreditCardPaymentRequest();
$creditCard->capture = True;
$creditCard->installments = 2;
$creditCard->statementDescriptor = "descriptor";
$creditCard->card = new \MundiAPILib\Models\CreateCardRequest();
$creditCard->card->number = "4000000000000010";
$creditCard->card->holderName = "Tony Stark";
$creditCard->card->expMonth = 1;
$creditCard->card->expYear = 2025;
$creditCard->card->cvv = "123";

$request = new \MundiAPILib\Models\CreateOrderRequest();

$request->items = [new \MundiAPILib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 1;
$request->items[0]->amount = 200000; // this value should be in cents

$request->payments = [new \MundiAPILib\Models\CreatePaymentRequest()];
$request->payments[0]->paymentMethod = "credit_card";
$request->payments[0]->creditCard = $creditCard;
$request->customer = $customer;

$request->payments[0]->split = [
    new \MundiAPILib\Models\CreateSplitRequest(),
    new \MundiAPILib\Models\CreateSplitRequest()
];

$request->payments[0]->split[0]->recipientId = "rp_ExAmPlExxxxxxxxx";
$request->payments[0]->split[0]->amount = 100000; // this value should be in cents
$request->payments[0]->split[0]->type = "flat";

$request->payments[0]->split[1]->recipientId = "rp_xxxxxxxxxExAmPlE";
$request->payments[0]->split[1]->amount = 100000; // this value should be in cents
$request->payments[0]->split[1]->type = "flat";


$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);