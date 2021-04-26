<?php
//This example was built using MundiAPI-PHP SDK.
//For more information, please refer to https://github.com/mundipagg/MundiAPI-PHP

$secretKey = 'YOUR SECRET KEY'; //the secret key will be provided by MundiPagg.
$basicAuthPassword = ''; //fill it with an empty string

$apiClient = new \MundiAPILib\MundiAPIClient(
    $secretKey,
    $basicAuthPassword
);

$plansController = $apiClient->getPlans();

$request = new \MundiAPILib\Models\CreatePlanRequest();
$request->name = "Plano Gold";
$request->currency = "BRL";
$request->interval = "month";
$request->intervalCount = 3;
$request->billingType = "prepaid";
$request->minimumPrice = 10000; // this value should be in cents
$request->installments = [3];
$request->paymentMethods = ["credit_card", "boleto"];
$request->items = [
    new \MundiAPILib\Models\CreatePlanItemRequest(),
    new \MundiAPILib\Models\CreatePlanItemRequest()
];
// Plan Item 1
$request->items[0]->name = "Musculação";
$request->items[0]->quantity = 1;
$request->items[0]->pricingScheme = new \MundiAPILib\Models\CreatePricingSchemeRequest();
$request->items[0]->pricingScheme->price = 18990; // this value should be in cents
$request->items[0]->price = 18990; // this value should be in cents
// Plan Item 2
$request->items[1]->name = "Matrícula";
// Matrícula ira cobrar apenas 1 vez-> Após a primeira cobrança, nao será mais cobrado
$request->items[1]->cycles = 1;
$request->items[1]->quantity = 1;
$request->items[1]->pricingScheme = new \MundiAPILib\Models\CreatePricingSchemeRequest();
$request->items[1]->pricingScheme->price = 5990; // this value should be in cents
$request->items[1]->price = 18990; // this value should be in cents

$result = $plansController->createPlan($request);

echo json_encode($result, JSON_PRETTY_PRINT);