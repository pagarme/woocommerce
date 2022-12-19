<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$customerController = $apiClient->getCustomers();

$customer = new \PagarmeCoreApiLib\Models\CreateCustomerRequest();
$customer->name = "sdk customer test";
$customer->email = "tonystark@avengers.com";

$request = new \PagarmeCoreApiLib\Models\CreateCardRequest();

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
$request->billingAddress = new \PagarmeCoreApiLib\Models\CreateAddressRequest();
$request->billingAddress->line1 = "10880, Malibu Point, Malibu Central";
$request->billingAddress->line2 = "7º floor";
$request->billingAddress->zipCode = "90265";
$request->billingAddress->city = "Malibu";
$request->billingAddress->state = "CA";
$request->billingAddress->country = "US";
// Card Options: Verify OneDollarAuth;
$request->options = new \PagarmeCoreApiLib\Models\CreateCardOptionsRequest();
$request->options->verifyCard = true;

$createdCustomer = $customerController->createCustomer($customer);
$result = $customerController->createCard($createdCustomer->id, $request);

echo json_encode($result, JSON_PRETTY_PRINT);
    