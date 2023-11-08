<?php

namespace Woocommerce\Pagarme\Action;

use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Helper\PaymentHelper;

class OrderActions implements RunnerInterface
{

    public function run()
    {
        add_filter('woocommerce_get_order_item_totals', array($this, 'filter_woocommerce_get_order_item_totals'), 10, 3);
        add_action('woocommerce_admin_order_totals_after_tax', array($this, 'showInstallmentFeesAdmin'));
    }
    public function showInstallmentFeesAdmin($orderId)
    {
        $order = new Order($orderId);
        if (PaymentHelper::isPagarmePaymentMethod($orderId) && !empty($order->get_meta('pagarme_card_tax'))) {
            $total = $order->get_meta('pagarme_card_tax');
            echo "  <tr>
                        <td class='label'>" . __('Installment Fee', 'woo-pagarme-payments') . ":</td>
                        <td width='1%'></td>
                        <td class='total'> " . wc_price($total) . "</td>
                    </tr>";
        }
    }

    public function filter_woocommerce_get_order_item_totals($total_rows, $order, $tax_display)
    {
        $orderPagarme = new Order($order->get_id());
        $total = $order->get_total();
        $installmentsValue = $orderPagarme->get_meta('pagarme_card_tax');
        if (empty($orderPagarme->get_meta('pagarme_card_tax'))) {
            $installmentsValue = $orderPagarme->calculeInstallmentFee(
                $orderPagarme->getTotalAmountByCharges(),
                $order->get_total()
            );
            $total = $orderPagarme->getTotalAmountByCharges();
        }
        if (PaymentHelper::isPagarmePaymentMethod($order->get_id()) && $installmentsValue > 0) {
            array_pop($total_rows);
            $total_rows['pagarme_installment_fee']['label'] = __('Installment Fee', 'woo-pagarme-payments');
            $total_rows['pagarme_installment_fee']['value'] = wc_price($installmentsValue);
            $total_rows['order_total']['label'] = __('Total', 'woocommerce');
            $total_rows['order_total']['value'] = wc_price($total);
            return $total_rows;
        }
        return $total_rows;
    }
}
