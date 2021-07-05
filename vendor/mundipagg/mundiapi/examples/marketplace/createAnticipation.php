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

$request = new \MundiAPILib\Models\CreateAnticipationRequest();
$request->amount = 10000; // this value should be in cents
$request->timeframe = "start";
$request->paymentDate = DateTime::createFromFormat("Y-m-d", "2020-12-12");

$recipientId = "rp_ExAmPlExxxxxxxxx";

$result = $recipientsController->createAnticipation($recipientId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);
