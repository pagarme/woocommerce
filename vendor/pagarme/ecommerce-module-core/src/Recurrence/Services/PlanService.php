<?php

namespace Pagarme\Core\Recurrence\Services;

use PagarmeCoreApiLib\Models\GetPlanItemResponse;
use PagarmeCoreApiLib\PagarmeCoreApiClient;
use PagarmeCoreApiLib\Models\CreatePlanRequest;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Factories\PlanFactory;
use Pagarme\Core\Recurrence\Repositories\PlanRepository;
use Pagarme\Core\Recurrence\ValueObjects\PlanId;
use Pagarme\Core\Recurrence\ValueObjects\PlanItemId;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;

class PlanService
{
    /**
     * @var PagarmeCoreApiClient
     */
    private $pagarmeCoreApiClient;

    public function __construct()
    {
        AbstractModuleCoreSetup::bootstrap();

        $config = AbstractModuleCoreSetup::getModuleConfiguration();

        $secretKey = null;
        if ($config->getSecretKey() != null) {
            $secretKey = $config->getSecretKey()->getValue();
        }

        $password = '';

        \PagarmeCoreApiLib\Configuration::$basicAuthPassword = '';

        $this->pagarmeCoreApiClient = new PagarmeCoreApiClient($secretKey, $password);
    }

    /**
     * @param Plan $plan
     * @throws \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function save(Plan $plan)
    {
        $methodName = "createPlanAtPagarme";
        if ($plan->getPagarmeId() !== null) {
            $methodName = "updatePlanAtPagarme";
        }

        $result = $this->{$methodName}($plan);

        $planId = new PlanId($result->id);
        $plan->setPagarmeId($planId);

        $planRepository = new PlanRepository();
        $planRepository->save($plan);
    }

    public function createPlanAtPagarme(Plan $plan)
    {
        $createPlanRequest = $plan->convertToSdkRequest();
        $planController = $this->pagarmeCoreApiClient->getPlans();

        try {
            $logService = $this->getLogService();
            $logService->info(
                'Create plan request: ' .
                json_encode($createPlanRequest, JSON_PRETTY_PRINT)
            );

            $result = $planController->createPlan($createPlanRequest);

            $logService->info(
                'Create plan response: ' .
                json_encode($result, JSON_PRETTY_PRINT)
            );

            $this->setItemsId($plan, $result);

            return $result;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }


    }

    public function updatePlanAtPagarme(Plan $plan)
    {
        $updatePlanRequest = $plan->convertToSdkRequest(true);
        $planController = $this->pagarmeCoreApiClient->getPlans();

        $this->updateItemsAtPagarme($plan, $planController);
        $result = $planController->updatePlan($plan->getPagarmeId(), $updatePlanRequest);

        return $result;
    }

    protected function setItemsId(Plan $plan, $result)
    {
        $resultItems = $result->items;
        foreach ($resultItems as $resultItem) {
            $this->updateItems($plan, $resultItem);
        }
    }

    protected function updateItems(Plan $plan, GetPlanItemResponse $resultItem)
    {
        $planItems = $plan->getItems();
        foreach ($planItems as $planItem) {
            if ($this->isItemEqual($planItem, $resultItem)) {
                $planItem->setPagarmeId(
                  new PlanItemId($resultItem->id)
                );
            }
        }
    }

    protected function isItemEqual($planItem, $resultItem)
    {
        return $planItem->getName() == $resultItem->name;
    }

    protected function updateItemsAtPagarme(Plan $plan, $planController)
    {
        foreach ($plan->getItems() as $item) {
            $planController->updatePlanItem(
                $plan->getPagarmeId(),
                $item->getPagarmeId(),
                $item->convertToSdkRequest()
            );
        }
    }

    public function findById($id)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->find($id);
    }

    public function findByPagarmeId(AbstractValidString $pagarmeId)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->findByPagarmeId($pagarmeId);
    }

    public function findAll()
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->listEntities(0, false);
    }

    public function findByProductId($id)
    {
        $planRepository = $this->getPlanRepository();

        return $planRepository->findByProductId($id);
    }

    public function delete($id)
    {
        $planRepository = $this->getPlanRepository();
        $plan = $planRepository->find($id);

        if (empty($plan)) {
            throw new \Exception("Plan not found - ID : {$id} ");
        }

        try {
            $planController = $this->pagarmeCoreApiClient->getPlans();
            $planController->deletePlan($plan->getPagarmeId());
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return $planRepository->delete($plan);
    }

    public function getPlanRepository()
    {
        return new PlanRepository();
    }

    public function getPagarmeCoreAPIClient($secretKey, $password)
    {
        return new PagarmeCoreAPIClient($secretKey, $password);
    }

    public function getLogService()
    {
        return new LogService(
            'PlanService',
            true
        );
    }
}
