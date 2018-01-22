<?php
namespace Woocommerce\Mundipagg\View;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Order;
use Woocommerce\Mundipagg\Model\Charge;
use Woocommerce\Mundipagg\Model\Setting;

class Orders
{
    public static function render_capture_metabox( $post )
    {
        $model        = new Order( $post->ID );
        $charges      = $model->get_charges( true );
        $charge_model = new Charge();

        if ( empty( $charges ) ) {
            echo '<p>Nenhum registro encontrado.</p>';
            return;
        }

        ?>
        <style>
            .modal { display:none; }
        </style>
        <div class="wrapper">
            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Charge ID</th>
                        <th>Tipo</th>
                        <th>Valor Total</th>
                        <th>Parcialmente Pago</th>
                        <th>Status</th>
                        <th class="action">Ação</th>
                    </tr>
                </thead>
                <tbody class="items">
                <?php foreach ( $charges as $item ) : ?>    
                    <tr <?php echo Utils::get_component( 'capture' ); ?>>
                        <?php 
                            $charge = maybe_unserialize( $item->charge_data );
                            $paid_amount = isset( $charge->paid_amount ) ? Utils::format_order_price_to_view( $charge->paid_amount ) : '-';
                        ?>
                        <td><?php echo $item->charge_id; ?></td>
                        <td><?php echo strtoupper( $charge->payment_method ); ?></td>
                        <td><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></td>
                        <td><?php echo $paid_amount; ?></td>
                        <td><?php echo strtoupper( $item->charge_status ); ?></td>
                        <td style="width:150px; text-align:center;">
                            <?php if ( $charge_model->is_allowed_cancel( $item ) ) : ?>
                            <button data-action="cancel" class="button-primary">Cancelar</button>
                            <?php endif; ?>

                            <?php if ( $charge_model->is_allowed_capture( $item ) ) : ?>
                            <button data-ref="<?php echo $item->charge_id; ?>" class="button-primary">Capturar</button>
                            <?php endif; ?>
                        </td>
                       <?php self::_render_capture_modal( $item, $charge ); ?>
                    </tr>
                <?php endforeach; ?>    
                </tbody>
            </table>
        </div>
        <?php
    }

    private static function _render_capture_modal( $item, $charge )
    {
        $paid_amount = isset( $charge->paid_amount ) ? Utils::format_order_price_to_view( $charge->paid_amount ) : '-';

        ?>
        <div data-charge="<?php echo $item->charge_id; ?>" class="modal">
            <p><b>CHARGE ID: </b><?php echo $item->charge_id; ?></p>
            <p><b>TIPO: </b><?php echo strtoupper( $charge->payment_method ); ?></p>
            <p><b>VALOR TOTAL: </b><?php echo Utils::format_order_price_to_view( $charge->amount ); ?></p>
            <p><b>PAGO PARCIALMENTE: </b><?php echo $paid_amount; ?></p>
            <p><b>STATUS: </b><?php echo strtoupper( $charge->status ); ?></p>
            <p>
                <label>Valor a ser capturado:
                    <input data-element="amount" type="text">
                </label>
            </p>
            <p>
                <button class="button-secondary" data-action="capture">Confirmar</button>
                <button class="button-secondary" data-iziModal-close>Cancelar</button>
            </p>  
        </div>
        <?php
    }
}