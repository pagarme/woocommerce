<?php

namespace Pagarme\Core\Webhook\Services;

use Exception;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Factories\ChargeFactory;
use Pagarme\Core\Kernel\Responses\ServiceResponse;
use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\ChargeService;
use Pagarme\Core\Webhook\Aggregates\Webhook;

final class ChargeHandlerService
{
    const COMPONENT_KERNEL = 'Kernel';
    const COMPONENT_RECURRENCE = 'Recurrence';

    /**
     * @var ChargeRecurrenceService|ChargeOrderService
     */
    private $listChargeHandleService;

    /**
     * @param $component
     * @throws Exception
     */
    public function build($component)
    {
        $listChargeHandleService = [
            self::COMPONENT_KERNEL => new ChargeOrderService(),
            self::COMPONENT_RECURRENCE => new ChargeRecurrenceService()
        ];

        if (empty($listChargeHandleService[$component])) {
            throw new Exception('Não foi encontrado o tipo de charge a ser carregado', 400);
        }

        $this->listChargeHandleService = $listChargeHandleService[$component];
    }

    /**
     * @param Webhook $webhook
     * @return mixed
     * @throws InvalidParamException
     * @throws NotFoundException
     * @throws Exception
     */
    public function handle(Webhook $webhook)
    {
        $this->build($webhook->getComponent());
        $multiMethodCanceled = $this->tryCancelMultiMethods($webhook);

        return array_merge(
            $this->listChargeHandleService->handle($webhook),
            $multiMethodCanceled
        );
    }

    /**
     * @param Webhook $webhook
     * @return array|ServiceResponse[]
     * @throws InvalidParamException
     * @throws Exception
     */
    public function tryCancelMultiMethods(Webhook $webhook)
    {
        /** @var Charge $charge  */
        $charge = $webhook->getEntity();
        $chargeService = new ChargeService();


        /** @var Charge $chargeList */
        $chargeList = $chargeService->findChargeWithOutOrder($charge->getCode());

        if (empty($chargeList)) {
            return [];
        }

        $chargeListPaid = $chargeService->getNotFailedOrCanceledCharges(
            $chargeList
        );

        if (empty($chargeListPaid) && count($chargeList) <= 1) {
            return [];
        }

        $listResponse = [];
        foreach ($chargeListPaid as $charge) {
            $listResponse[] = ($chargeService->cancelJustAtPagarme($charge))->getMessage();
        }

        return $listResponse;
    }
}
