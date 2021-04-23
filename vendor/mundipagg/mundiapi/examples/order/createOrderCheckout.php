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

$checkout = new \MundiAPILib\Models\CreateCheckoutPaymentRequest();
$checkout->customerEditable = false;
$checkout->skipCheckoutSuccessPage = true;
$checkout->acceptedPaymentMethods = ["credit_card", "boleto", "bank_transfer", "debit_card"];
$checkout->acceptedMultiPaymentMethods = [["credit_card", "credit_card"], ["credit_card", "boleto"]];
$checkout->successUrl = "https://www.mundipagg.com";

//Bank transfer payment Setup;
$checkout->bankTransfer = new \MundiAPILib\Models\CreateCheckoutBankTransferRequest();
$checkout->bankTransfer->bank = ["237", "001", "341"];

//Boleto Payment Setup;
$checkout->boleto = new \MundiAPILib\Models\CreateCheckoutBoletoPaymentRequest();
$checkout->boleto->bank = "033";
$checkout->boleto->instructions ="Pagar ate o vencimento";
$checkout->boleto->dueAt = new \DateTime("2021-07-25T00:00:00Z");

//Credit Card Payment Setup;
$checkout->creditCard = new \MundiAPILib\Models\CreateCheckoutCreditCardPaymentRequest();
$checkout->creditCard->capture = true;
$checkout->creditCard->statement_descriptor = "Descriptor example";
$checkout->creditCard->installments = [ //Credit card installments Setup
    new \MundiAPILib\Models\CreateCheckoutCardInstallmentOptionRequest(),
    new \MundiAPILib\Models\CreateCheckoutCardInstallmentOptionRequest()
];
// installment 1;
$checkout->creditCard->installments[0]->number = 1;
$checkout->creditCard->installments[0]->total = 2000;
// installment 2 with extra tax of 500;
$checkout->creditCard->installments[1]->number = 1;
$checkout->creditCard->installments[1]->total = 2500;

// Debit Card Payment Setup;
$checkout->debitCard = new \MundiAPILib\Models\CreateCheckoutDebitCardPaymentRequest();
// Debit card Authentication Setup;
$checkout->debitCard->authentication = new \MundiAPILib\Models\CreatePaymentAuthenticationRequest();
$checkout->debitCard->authentication->type = 'threed_secure';
$checkout->debitCard->authentication->threedSecure = new \MundiAPILib\Models\CreateThreeDSecureRequest();
$checkout->debitCard->authentication->threedSecure->mpi = "acquirer";
$checkout->debitCard->authentication->threedSecure->successUrl = "https://www.mundipagg.com";

$request = new \MundiAPILib\Models\CreateOrderRequest();

$request->items = [new \MundiAPILib\Models\CreateOrderItemRequest()];
$request->items[0]->description = "Tesseract Bracelet";
$request->items[0]->quantity = 3;
$request->items[0]->amount = 1490; // this value should be in cents

$request->payments = [new \MundiAPILib\Models\CreatePaymentRequest()];
$request->payments[0]->amount = 2000; // this value should be in cents
$request->payments[0]->paymentMethod = "checkout";
$request->payments[0]->checkout = $checkout;
$request->customer = $customer;

$result = new \MundiAPILib\Models\GetOrderResponse();
$result->checkouts = [new \MundiAPILib\Models\GetCheckoutPaymentResponse()];
$result = $orderController->createOrder($request);

echo json_encode($result, JSON_PRETTY_PRINT);