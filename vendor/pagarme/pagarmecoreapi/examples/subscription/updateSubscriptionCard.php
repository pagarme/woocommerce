<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$subscriptionsController = $apiClient->getSubscriptions();

$subscriptionId = "sub_ExAmPlExxxxxxxxx";

$request = new \PagarmeCoreApiLib\Models\UpdateSubscriptionCardRequest();
$request->card = new \PagarmeCoreApiLib\Models\CreateCardRequest();
$request->card->number = "4532912167490007";
$request->card->holderName = "Tony Stark";
$request->card->expMonth = 1;
$request->card->expYear = 2028;
$request->card->cvv = "123";
$request->card->billingAddress = new \PagarmeCoreApiLib\Models\CreateAddressRequest();
$request->card->billingAddress->line1 = "375  Av. General Justo  Centro";
$request->card->billingAddress->line2 = "8º andar";
$request->card->billingAddress->zipCode = "20021130";
$request->card->billingAddress->city = "Rio de Janeiro";
$request->card->billingAddress->state = "RJ";
$request->card->billingAddress->country = "BR";

$result = $subscriptionsController->updateSubscriptionCard($subscriptionId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);