<?php
//This example was built using pagarme-core-api-php SDK.
//For more information, please refer to https://github.com/pagarme/pagarme-core-api-php
require_once  "../vendor/autoload.php" ;

$basicAuthUserName = 'basicAuthUserName'; // The username to use with basic authentication
$basicAuthPassword = 'basicAuthPassword'; // The password to use with basic authentication

$apiclient = new PagarmeCoreApiLib\PagarmeCoreApiClient($basicAuthUserName, $basicAuthPassword);

$recipientsController = $apiClient->getRecipients();

$request = new \PagarmeCoreApiLib\Models\CreateRecipientRequest();
$request->name = "Tony Stark";
$request->email = "tstark@mundipagg.com";
$request->description = "Recebedor tony stark";
$request->document = "26224451990";
$request->type = "individual";
$request->defaultBankAccount = new \PagarmeCoreApiLib\Models\CreateBankAccountRequest();
$request->defaultBankAccount->holderName = "Tony Stark";
$request->defaultBankAccount->holderType = "individual";
$request->defaultBankAccount->holderDocument = "26224451990";
$request->defaultBankAccount->bank = "341";
$request->defaultBankAccount->branchNumber = "12345";
$request->defaultBankAccount->branchCheckDigit = "6";
$request->defaultBankAccount->accountNumber = "12345";
$request->defaultBankAccount->accountCheckDigit = "6";
$request->defaultBankAccount->type = "checking";

$result = $recipientsController->createRecipient($request);

echo json_encode($result, JSON_PRETTY_PRINT);