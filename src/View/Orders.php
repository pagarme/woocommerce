<?php

namespace Woocommerce\Pagarme\View;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Charge;
use Woocommerce\Pagarme\Model\Setting;

class Orders
{
    public static function render_capture_metabox($post)
    {
        $model        = new Order($post->ID);
        $charges      = $model->get_charges(true);
        $charge_model = new Charge();

        if (empty($charges)) {
            echo wp_kses('<p>Nenhum registro encontrado.</p>', array('p' => array()));
            return;
        }

?>
        <style>
            .modal {
                display: none;
            }

            tbody.items {
                font-size: 11px;
            }
        </style>
        <div class="wrapper">
            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Charge ID</th>
                        <th>Tipo</th>
                        <th>Valor Total</th>
                        <th>Parcialmente Capturado</th>
                        <th>Parcialmente Cancelado</th>
                        <th>Parcialmente Estornado</th>
                        <th>Status</th>
                        <th class="action">Ação</th>
                    </tr>
                </thead>
                <tbody class="items">
                    <?php foreach ($charges as $charge) : ?>
                        <tr <?php echo Utils::get_component('capture'); ?>>
                            <?php
                            $transaction = array_shift($charge->getTransactions());
                            $chargeId = $charge->getPagarmeId()->getValue();
                            $chargeStatus = $charge->getStatus()->getStatus();

                            $paid_amount = !empty($charge->getPaidAmount()) ? Utils::format_order_price_to_view($charge->getPaidAmount()) : ' - ';
                            $canceled_amount = !empty($charge->getCanceledAmount()) ? Utils::format_order_price_to_view($charge->getCanceledAmount()) : ' - ';
                            $refunded_amount = !empty($charge->getRefundedAmount()) ? Utils::format_order_price_to_view($charge->getRefundedAmount()) : ' - ';

                            ?>
                            <td><?php echo esc_html($chargeId); ?></td>
                            <td><?php echo esc_html(strtoupper($transaction->getTransactionType()->getType())); ?></td>
                            <td><?php echo wp_kses(Utils::format_order_price_to_view($charge->getAmount()), ['span' => array('class' => true)]); ?></td>
                            <td><?php echo wp_kses($paid_amount, ['span' => array('class' => true)]);
                                ?></td>
                            <td><?php echo wp_kses($canceled_amount, ['span' => array('class' => true)]);
                                ?></td>
                            <td><?php echo wp_kses($refunded_amount, ['span' => array('class' => true)]);
                                ?></td>
                            <td><?php echo esc_html(strtoupper($chargeStatus)); ?></td>
                            <td style="width:150px; padding-top:12px; text-align:center;">
                                <button data-type="cancel" data-ref="<?php echo esc_attr($chargeId); ?>" <?php echo esc_attr(!$charge_model->is_allowed_cancel($charge) ? 'disabled=disabled' : ''); ?> class="button-primary">Cancelar</button>

                                <?php if ($transaction->getTransactionType()->getType() == 'credit_card') : ?>
                                    <button data-type="capture" data-ref="<?php echo esc_attr($chargeId); ?>" <?php echo esc_attr(!$charge_model->is_allowed_capture($charge) ? 'disabled=disabled' : ''); ?> class="button-primary">Capturar</button>
                                <?php endif; ?>
                            </td>
                            <?php self::render_capture_modal($charge, $transaction); ?>
                            <?php self::render_cancel_modal($charge, $transaction); ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php
    }

    private static function render_capture_modal($charge, $transaction)
    {
        $paid_amount = !empty($charge->getPaidAmount()) ? Utils::format_order_price_to_view($charge->getPaidAmount()) : ' - ';
        $chargeId = $charge->getPagarmeId()->getValue();
        $chargeStatus = $charge->getStatus()->getStatus();

    ?>
        <div data-charge-action="<?php echo esc_attr($chargeId); ?>-capture" data-charge="<?php echo esc_attr($chargeId); ?>" class="modal">
            <h2>Pagar.me - Captura</h2>
            <p><b>CHARGE ID: </b><?php echo esc_html($chargeId); ?></p>
            <p><b>TIPO: </b><?php echo esc_html(strtoupper($transaction->getTransactionType()->getType())); ?></p>
            <p><b>VALOR TOTAL: </b><?php echo Utils::format_order_price_to_view($charge->getAmount()); ?></p>
            <p><b>PARCIALMENTE CAPTURADO: </b><?php echo wp_kses($paid_amount, ['span' => array('class' => true)]); ?></p>
            <p><b>STATUS: </b><?php echo esc_html(strtoupper($chargeStatus)); ?></p>
            <p>
                <label>Valor a ser capturado: R$
                    <input data-element="amount" type="text" />
                </label>
            </p>
            <p>
                <button class="button-secondary" data-action="capture">Confirmar</button>
                <button class="button-secondary" data-iziModal-close>Cancelar</button>
            </p>
        </div>
    <?php
    }

    private static function render_cancel_modal($charge, $transaction)
    {
        $canceled_amount = !empty($charge->getCanceledAmount()) ? $charge->getCanceledAmount() : 0;
        $refunded_amount = !empty($charge->getRefundedAmount()) ? $charge->getRefundedAmount() : 0;
        $paid_amount     = !empty($charge->getPaidAmount()) ? $charge->getPaidAmount() : 0;

        $value_to_cancel = $charge->getAmount();
        $chargeId = $charge->getPagarmeId()->getValue();
        $chargeStatus = $charge->getStatus()->getStatus();

        if ($paid_amount) {
            $value_to_cancel = $paid_amount;
        }

        if ($paid_amount && $canceled_amount) {
            $value_to_cancel = $paid_amount - $canceled_amount;
        }

        if ($paid_amount && $refunded_amount) {
            $value_to_cancel = max(0, $paid_amount - $refunded_amount);
        }

        $canceled_amount = !empty($canceled_amount) ? Utils::format_order_price_to_view($canceled_amount) : '-';
    ?>
        <div data-charge-action="<?php echo esc_attr($chargeId); ?>-cancel" data-charge="<?php echo esc_attr($chargeId); ?>" class="modal">
            <h2>Pagar.me - Cancelamento</h2>
            <p><b>CHARGE ID: </b><?php echo esc_html($chargeId); ?></p>
            <p><b>TIPO: </b><?php echo esc_html(strtoupper($transaction->getTransactionType()->getType())); ?></p>
            <p><b>VALOR TOTAL: </b><?php echo Utils::format_order_price_to_view($charge->getAmount()); ?></p>
            <p><b>PARCIALMENTE CANCELADO: </b><?php echo wp_kses($canceled_amount, ['span' => array('class' => true)]); ?></p>
            <p><b>STATUS: </b><?php echo esc_html(strtoupper($chargeStatus)); ?></p>
            <p>
                <label>Valor a ser cancelado: R$
                    <input data-element="amount" type="text" value="<?php echo esc_attr($value_to_cancel); ?>" <?php echo esc_attr($chargeStatus == 'pending' ? 'disabled=disabled' : ''); ?> />
                </label>
            </p>
            <p>
                <button class="button-secondary" data-action="cancel">Confirmar</button>
                <button class="button-secondary" data-iziModal-close>Cancelar</button>
            </p>
        </div>
<?php
    }
}
