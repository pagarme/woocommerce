<?php
//This example was built using MundiAPI-PHP SDK.
//For more information, please refer to https://github.com/mundipagg/MundiAPI-PHP

$secretKey = 'YOUR SECRET KEY'; //the secret key will be provided by MundiPagg.
$basicAuthPassword = ''; //fill it with an empty string

$apiClient = new \MundiAPILib\MundiAPIClient(
    $secretKey,
    $basicAuthPassword
);

$recipientsController = $apiClient->getRecipients();

$request = new \MundiAPILib\Models\CreateTransferRequest();
$request->amount = 100; // this value should be in cents

$recipientId = "rp_ExAmPlExxxxxxxxx";

$result = $recipientsController->createTransfer($recipientId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);