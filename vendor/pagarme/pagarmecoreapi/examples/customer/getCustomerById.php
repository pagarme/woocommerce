<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$customersController = $apiClient->getCustomers();

$customerId = "cus_ExAmPlExxxxxxxxx";

$result = $customersController->getCustomer($customerId);

echo json_encode($result, JSON_PRETTY_PRINT);