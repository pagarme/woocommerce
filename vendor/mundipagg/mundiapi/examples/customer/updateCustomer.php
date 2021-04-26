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

$request = new \MundiAPILib\Models\UpdateCustomerRequest();
$request->name = "Peter Parker";
$request->email = "parker@avengers.com";

$customerId = "cus_ExAmPlExxxxxxxxx";

$result = $customerController->updateCustomer($customerId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);