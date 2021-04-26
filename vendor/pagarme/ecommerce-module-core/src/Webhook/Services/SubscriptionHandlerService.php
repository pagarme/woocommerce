<?php

namespace Pagarme\Core\Webhook\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Exceptions\NotFoundException;
use Pagarme\Core\Kernel\Factories\OrderFactory;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\OrderService;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Webhook\Aggregates\Webhook;
use Pagarme\Core\Recurrence\Aggregates\Subscription;
use Pagarme\Core\Recurrence\Repositories\SubscriptionRepository;

class SubscriptionHandlerService extends AbstractHandlerService
{
    protected function handleCreated(Webhook $webhook)
    {
        throw new NotFoundException('Webhook Not implemented');
    }

    protected function handleCanceled(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $orderService = new OrderService();
        $i18n = new LocalizationService();
        $orderFactory = new OrderFactory();

        /**
         * @var Subscription
         */
        $subscription = $webhook->getEntity();

        $this->order->setStatus($subscription->getStatus());

        $subscriptionRepository->save($this->order);

        $history = $i18n->getDashboard('Subscription canceled');
        $this->order->getPlatformOrder()->addHistoryComment($history);

        $platformOrderStatus = ucfirst(
            $this->order->getPlatformOrder()
                ->getPlatformOrder()
                ->getStatus()
        );

        $realOrder = $orderFactory->createFromSubscriptionData(
            $this->order,
            $platformOrderStatus
        );

        $orderService->syncPlatformWith($realOrder);

        $result = [
            "message" => 'Subscription cancel registered',
            "code" => 200
        ];

        return $result;
    }

    public function loadOrder(Webhook $webhook)
    {
        $subscriptionRepository = new SubscriptionRepository();
        $apiService = new ApiService();

        $subscriptionId = $webhook->getEntity()->getSubscriptionId()->getValue();
        $subscriptionObject = $apiService->getSubscription(new SubscriptionId($subscriptionId));

        if (!$subscriptionObject) {
            throw new Exception('Code not found.', 400);
        }

        $subscription = $subscriptionRepository->findByCode($subscriptionObject->getCode());
        if ($subscription === null) {
            $code = $subscriptionObject->getCode();
            throw new NotFoundException("Subscription #{$code} not found.");
        }

        $this->order = $subscription;
    }
}
