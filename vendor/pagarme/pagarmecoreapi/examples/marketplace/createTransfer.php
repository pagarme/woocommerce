<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$recipientsController = $apiClient->getRecipients();

$request = new \PagarmeCoreApiLib\Models\CreateTransferRequest();
$request->amount = 100; // this value should be in cents

$recipientId = "rp_ExAmPlExxxxxxxxx";

$result = $recipientsController->createTransfer($recipientId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);