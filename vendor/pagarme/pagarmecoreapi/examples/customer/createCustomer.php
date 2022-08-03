<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$customerController = $apiClient->getCustomers();

$request = new \PagarmeCoreApiLib\Models\CreateCustomerRequest();
$request->name = "sdk customer test";
$request->email = "tonystark@avengers.com";
$request->type = "individual";
$request->document = "55342561094";
$request->code = "MY_CUSTOMER_001";

$request->address = new \PagarmeCoreApiLib\Models\CreateAddressRequest();
$request->address->line1 = "375, Av. General Justo, Centro";
$request->address->line2 = "8º andar";
$request->address->zipCode = "20021130";
$request->address->city = "Rio de Janeiro";
$request->address->state = "RJ";
$request->address->country = "BR";
$request->address->metadata = new \PagarmeCoreApiLib\Models\UpdateMetadataRequest();
$request->address->metadata->id = "my_address_id";

$request->phones = new \PagarmeCoreApiLib\Models\CreatePhonesRequest();
$request->phones->homePhone = new \PagarmeCoreApiLib\Models\CreatePhoneRequest();
$request->phones->homePhone->areaCode = "21";
$request->phones->homePhone->countryCode = "55";
$request->phones->homePhone->number = "000000000";
$request->phones->mobilePhone = new \PagarmeCoreApiLib\Models\CreatePhoneRequest();
$request->phones->mobilePhone->areaCode = "21";
$request->phones->mobilePhone->countryCode = "55";
$request->phones->mobilePhone->number = "000000000";

$result = $customerController->createCustomer($request);

echo json_encode($result, JSON_PRETTY_PRINT);