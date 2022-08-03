<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$subscriptionsController = $apiClient->getSubscriptions();

$subscriptionId = "sub_ExAmPlExxxxxxxxx";

$request = new \PagarmeCoreApiLib\Models\CreateCancelSubscriptionRequest();
$request->cancelPendingInvoices = true;

$result = $subscriptionsController->cancelSubscription($subscriptionId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);