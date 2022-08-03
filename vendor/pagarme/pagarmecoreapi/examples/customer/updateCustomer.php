<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$customerController = $apiClient->getCustomers();

$request = new \PagarmeCoreApiLib\Models\UpdateCustomerRequest();
$request->name = "Peter Parker";
$request->email = "parker@avengers.com";

$customerId = "cus_ExAmPlExxxxxxxxx";

$result = $customerController->updateCustomer($customerId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);