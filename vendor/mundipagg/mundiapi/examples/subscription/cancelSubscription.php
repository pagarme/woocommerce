<?php
//This example was built using MundiAPI-PHP SDK.
//For more information, please refer to https://github.com/mundipagg/MundiAPI-PHP

$secretKey = 'YOUR SECRET KEY'; //the secret key will be provided by MundiPagg.
$basicAuthPassword = ''; //fill it with an empty string

$apiClient = new \MundiAPILib\MundiAPIClient(
    $secretKey,
    $basicAuthPassword
);

$subscriptionsController = $apiClient->getSubscriptions();

$subscriptionId = "sub_ExAmPlExxxxxxxxx";

$request = new \MundiAPILib\Models\CreateCancelSubscriptionRequest();
$request->cancelPendingInvoices = true;

$result = $subscriptionsController->cancelSubscription($subscriptionId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);