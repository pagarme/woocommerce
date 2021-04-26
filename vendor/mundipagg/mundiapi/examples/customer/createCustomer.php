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

$request = new \MundiAPILib\Models\CreateCustomerRequest();
$request->name = "sdk customer test";
$request->email = "tonystark@avengers.com";
$request->type = "individual";
$request->document = "55342561094";
$request->code = "MY_CUSTOMER_001";

$request->address = new \MundiAPILib\Models\CreateAddressRequest();
$request->address->line1 = "375, Av. General Justo, Centro";
$request->address->line2 = "8º andar";
$request->address->zipCode = "20021130";
$request->address->city = "Rio de Janeiro";
$request->address->state = "RJ";
$request->address->country = "BR";
$request->address->metadata = new \MundiAPILib\Models\UpdateMetadataRequest();
$request->address->metadata->id = "my_address_id";

$request->phones = new \MundiAPILib\Models\CreatePhonesRequest();
$request->phones->homePhone = new \MundiAPILib\Models\CreatePhoneRequest();
$request->phones->homePhone->areaCode = "21";
$request->phones->homePhone->countryCode = "55";
$request->phones->homePhone->number = "000000000";
$request->phones->mobilePhone = new \MundiAPILib\Models\CreatePhoneRequest();
$request->phones->mobilePhone->areaCode = "21";
$request->phones->mobilePhone->countryCode = "55";
$request->phones->mobilePhone->number = "000000000";

$result = $customerController->createCustomer($request);

echo json_encode($result, JSON_PRETTY_PRINT);