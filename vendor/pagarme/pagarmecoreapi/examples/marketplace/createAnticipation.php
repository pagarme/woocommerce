<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$recipientsController = $apiClient->getRecipients();

$request = new \PagarmeCoreApiLib\Models\CreateAnticipationRequest();
$request->amount = 10000; // this value should be in cents
$request->timeframe = "start";
$request->paymentDate = DateTime::createFromFormat("Y-m-d", "2020-12-12");

$recipientId = "rp_ExAmPlExxxxxxxxx";

$result = $recipientsController->createAnticipation($recipientId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);