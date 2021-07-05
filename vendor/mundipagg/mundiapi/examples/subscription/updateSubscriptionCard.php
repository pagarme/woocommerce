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

$request = new \MundiAPILib\Models\UpdateSubscriptionCardRequest();
$request->card = new \MundiAPILib\Models\CreateCardRequest();
$request->card->number = "4532912167490007";
$request->card->holderName = "Tony Stark";
$request->card->expMonth = 1;
$request->card->expYear = 2028;
$request->card->cvv = "123";
$request->card->billingAddress = new \MundiAPILib\Models\CreateAddressRequest();
$request->card->billingAddress->line1 = "375  Av. General Justo  Centro";
$request->card->billingAddress->line2 = "8º andar";
$request->card->billingAddress->zipCode = "20021130";
$request->card->billingAddress->city = "Rio de Janeiro";
$request->card->billingAddress->state = "RJ";
$request->card->billingAddress->country = "BR";

$result = $subscriptionsController->updateSubscriptionCard($subscriptionId, $request);

echo json_encode($result, JSON_PRETTY_PRINT);