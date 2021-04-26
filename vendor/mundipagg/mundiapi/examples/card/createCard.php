<?php
//This example was built using MundiAPI-PHP SDK.
//For more information, please refer to https://github.com/mundipagg/MundiAPI-PHP

$secretKey = 'YOUR SECRET KEY'; //the secret key will be provided by MundiPagg.
$basicAuthPassword = ''; //fill it with an empty string

$apiClient = new \MundiAPILib\MundiAPIClient(
    $secretKey,
    $basicAuthPassword
);

$customerController = $apiClient->getCustomers();

$customer = new \MundiAPILib\Models\CreateCustomerRequest();
$customer->name = "sdk customer test";
$customer->email = "tonystark@avengers.com";

$request = new \MundiAPILib\Models\CreateCardRequest();

$request->number = "4000000000000010";
$request->holderName = "Tony Stark";
$request->holderDocument = "93095135270";
$request->expMonth = 1;
$request->expYear = 25;
$request->cvv = "351";
// Brand is Optional field and autodetected;
$request->brand = "Mastercard";
$request->privateLabel = false;
// Billing Address;
$request->billingAddress = new \MundiAPILib\Models\CreateAddressRequest();
$request->billingAddress->line1 = "10880, Malibu Point, Malibu Central";
$request->billingAddress->line2 = "7º floor";
$request->billingAddress->zipCode = "90265";
$request->billingAddress->city = "Malibu";
$request->billingAddress->state = "CA";
$request->billingAddress->country = "US";
// Card Options: Verify OneDollarAuth;
$request->options = new \MundiAPILib\Models\CreateCardOptionsRequest();
$request->options->verifyCard = true;

$createdCustomer = $customerController->createCustomer($customer);
$result = $customerController->createCard($createdCustomer->id, $request);

echo json_encode($result, JSON_PRETTY_PRINT);
    
    