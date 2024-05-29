<?php

namespace Woocommerce\Pagarme\Service;

use Exception;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Repositories\ChargeRepository;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Order;

class HandleWebhookService
{
    /**
     * @param $webhook
     *
     * @return void
     * @throws Exception
     */
    public function handle($webhook)
    {
        try {
            $type = explode('.', $webhook->type);
            $handleName = 'handle' . Utils::snakeToCamelCase(end($type));
            $this->$handleName($webhook);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param $webhook
     *
     * @return void
     * @throws InvalidParamException
     */
    private function handlePartialCanceled($webhook)
    {
        $this->handleRefunded($webhook);
    }

    /**
     * @param $webhook
     *
     * @return void
     * @throws InvalidParamException
     */
    private function handleRefunded($webhook)
    {
        $order = new Order($webhook->data->order->code);
        $chargeId = $webhook->data->id;
        $paidAmount = $webhook->data->paid_amount;
        $refundAmount = $webhook->data->last_transaction->amount;
        $totalRefund = $webhook->data->canceled_amount;

        $orderNote = 'PGM - ';
        $noteRefunded = sprintf(
            /* translators: %s is Pagar.me charge ID */
            __('Charge %s was fully refunded.', 'woo-pagarme-payments'),
            $chargeId
        );
        $notePartiallyRefunded = sprintf(
            /* translators: 1 Pagar.me charge ID; 2 Value refunded with currency symbol */
            __(
                'Charge %1$s was partially refunded. Amount: %2$s',
                'woo-pagarme-payments'),
            $chargeId,
            Utils::format_order_price_to_view($refundAmount)
        );
        $orderNote .= $webhook->type === 'charge.refunded' ? $noteRefunded : $notePartiallyRefunded;
        $order->getWcOrder()->add_order_note($orderNote);

        $charges = $order->get_charges();
        $chargeRepository = new ChargeRepository();

        foreach ($charges as $charge) {
            if ($charge->getPagarmeId()->getValue() === $chargeId) {
                if ($totalRefund === $paidAmount) {
                    $charge->setStatus(ChargeStatus::canceled());
                }
                $charge->setCanceledAmount($totalRefund);
                $charge->setRefundedAmount($totalRefund);
                $chargeRepository->save($charge);
            }
        }
    }
}
