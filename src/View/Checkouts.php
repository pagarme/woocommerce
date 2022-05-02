<?php

namespace Woocommerce\Pagarme\View;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Charge;

class Checkouts
{
    protected static function message_before()
    {
        echo wp_kses(
            '<p class="title">' . __('Your transaction has been processed.', 'woo-pagarme-payments') . '</p>',
            array('p' => array('class' => true))
        );
    }

    protected static function message_after()
    {
        echo wp_kses(
            '<p>' . __('If you have any questions regarding the transaction, please contact us.', 'woo-pagarme-payments') . '</p>',
            array('p' => array())
        );
    }

    public static function handle_messages(Order $order)
    {
        switch ($order->payment_method) {
            case 'billet':
                return self::billet_message($order);

            case 'credit_card':
                return self::credit_card_message($order);

            case 'pix':
                return self::pix_message($order);

            case 'billet_and_card':
                return self::billet_and_card_message($order);

            case '2_cards':
                return self::two_credit_card_message($order);
            case 'voucher':
                return self::credit_card_message($order);
        }
    }

    public static function billet_message($order)
    {
        $response_data = $order->response_data;

        if (!$response_data) :
            return self::render_failed_message();
        endif;

        if (is_string($response_data)) {
            $response_data = json_decode($response_data);
        }

        $charges     = $response_data->charges;
        $charge      = array_shift($charges);
        $transaction = array_shift($charge->transactions);

        ob_start();

        self::message_before();

?>
        <p>
            <?php _e('If you have not yet received the boleto, please click the button below to print.', 'woo-pagarme-payments'); ?>
        </p>

        <a href="<?php echo esc_url($transaction->boletoUrl); ?>" target="_blank" class="payment-link">
            <?php _e('Print', 'woo-pagarme-payments'); ?>
        </a>

    <?php

        echo self::message_after();

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    public static function credit_card_message($order)
    {
        $response_data = $order->response_data;

        if (is_string($response_data)) {
            $response_data = json_decode($response_data);
        }

        $charge = null;
        if (!empty($response_data)) {
            $charges = $response_data->charges;
            $charge  = array_shift($charges);
        }

        ob_start();

        self::message_before();

    ?>
        <p>
            <?php
            /** phpcs:disable */
            printf(
                __('The status of your transaction is %s.', 'woo-pagarme-payments'),
                '<strong>' . strtoupper(
                    __(
                        $charge ? ucfirst($charge->status) : 'Failed',
                        'woo-pagarme-payments'
                    )
                ) . '</strong>'
            );
            /** phpcs:enable */
            ?>
        </p>
    <?php

        self::message_after();

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    public static function two_credit_card_message($order)
    {
        $response_data = $order->response_data;

        if (is_string($response_data)) {
            $response_data = json_decode($response_data);
        }

        $first_charge = null;
        $second_charge = null;
        if (!empty($response_data)) {
            $charges = $response_data->charges;
            $first_charge = array_shift($charges);
            $second_charge = array_shift($charges);
        }

        ob_start();

        self::message_before();

    ?>
        <p>
            <?php
            /** phpcs:disable */
            printf(
                __('The status of your credit cards transactions are %s and %s', 'woo-pagarme-payments'),
                '<strong>' . strtoupper(
                    __(
                        $first_charge ? ucfirst($first_charge->status) : 'Failed',
                        'woo-pagarme-payments'
                    )
                ) . '</strong>',
                '<strong>' . strtoupper(
                    __(
                        $second_charge ? ucfirst($second_charge->status) : 'Failed',
                        'woo-pagarme-payments'
                    )
                ) . '</strong>'
            );
            /** phpcs:enable */
            ?>
        </p>
    <?php

        self::message_after();

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    public static function pix_message($order)
    {
        $response_data = $order->response_data;

        if (!$response_data) :
            return self::render_failed_message();
        endif;

        if (is_string($response_data)) {
            $response_data = json_decode($response_data);
        }

        $charges     = $response_data->charges;
        $charge      = array_shift($charges);
        $transaction = array_shift($charge->transactions);
        $qrCodeUrl = $transaction->postData->qr_code_url;
        $rawQrCode = $transaction->postData->qr_code;
        ob_start();

        self::message_before();

    ?>
        <p>
            <img style="margin: auto;" src="<?php echo esc_url($qrCodeUrl); ?>" title="Link to QRCode" />
        </p>

        <a id="pagarme-qr-code" rawCode="<?php echo esc_attr($rawQrCode); ?>" onclick="pagarmeQrCodeCopy()" class="payment-link">
            <?php _e('Copy Code', 'woo-pagarme-payments'); ?>
        </a>

        <div class="pix-qr-code-instruction">
            <?php _e('1. Point your phone at this screen to capture the code.', 'woo-pagarme-payments'); ?>
        </div>
        <div class="pix-qr-code-instruction">
            <?php _e('2. Open your payments app.', 'woo-pagarme-payments'); ?>
        </div>
        <div class="pix-qr-code-instruction">
            <?php _e('3. Confirm the information and complete the payment on the app.', 'woo-pagarme-payments'); ?>
        </div>
        <div class="pix-qr-code-instruction">
            <?php _e('4. We will send you a purchase confirmation.', 'woo-pagarme-payments'); ?>
        </div>


        <div id="pix-image-attention-container">
            <?php
            printf(
                '<img id="pix-image-attention" class="logo" src="%1$s" alt="%2$s" title="%2$s" />',
                esc_url(Core::plugins_url('assets/images/pix-checkout-attention.svg')),
                esc_html__('pix attention icon', 'woo-pagarme-payments')
            );
            ?>
        </div>

        <div class="pix-qr-code-instruction pix-attention-instruction">
            <?php _e('You can also complete the payment by copying and pasting the code into the app.', 'woo-pagarme-payments'); ?>
        </div>



        <?php

        echo self::message_after();

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    public static function billet_and_card_message($order)
    {
        $response = json_decode($order->response_data);

        if (!$response) :
            return self::render_failed_message();
        endif;

        $charges = $response->charges;

        ob_start();

        self::message_before();

        foreach ($charges as $charge) :

            $transaction = array_shift($charge->transactions);
            $transactionType = $transaction->type;
            if ($transactionType == 'credit_card') :
                echo wp_kses('<p>', array('p' => array()));
                /** phpcs:disable */
                printf(
                    __('CREDIT CARD: The status of your transaction is %s.', 'woo-pagarme-payments'),
                    '<strong>' .  strtoupper(
                        __(
                            ucfirst($charge->status),
                            'woo-pagarme-payments'
                        )
                    )  . '</strong>'
                );
                /** phpcs:enable */
                echo wp_kses('</p>', array('p' => array()));;
            endif;

            if ($transactionType == 'boleto') :
        ?>
                <p>
                    <?php _e('BOLETO: If you have not yet received the boleto, please click the button below to print.', 'woo-pagarme-payments'); ?>
                </p>

                <a href="<?php echo esc_url($transaction->boletoUrl); ?>" target="_blank" class="payment-link">
                    <?php _e('Print', 'woo-pagarme-payments'); ?>
                </a>
        <?php
            endif;

        endforeach;

        echo self::message_after();

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    private static function render_failed_message()
    {
        ob_start();

        self::message_before();
        ?>
        <p>
            <?php
            printf(
                __('The status of your transaction is %s.', 'woo-pagarme-payments'),
                '<strong>' . strtoupper(
                    __(
                        'Failed',
                        'woo-pagarme-payments'
                    )
                ) . '</strong>'
            );
            ?>
        </p>

    <?php
        echo self::message_after();

        $message = ob_get_contents();

        ob_end_clean();

        return $message;
    }

    public static function render_payment_details($order_id)
    {
        $order   = new Order($order_id);
        $charges = $order->get_charges();

        if (!$charges) {
            $charges = isset($order->response_data->charges) ? $order->response_data->charges : false;
        }

        if (empty($charges)) {
            return;
        }

        $model_charge = new Charge();

    ?>
        <section>
            <h2><?php _e('Payment Data', 'woo-pagarme-payments'); ?></h2>
            <table class="woocommerce-table">
                <?php
                foreach ($charges as $charge) {
                    echo self::get_payment_detail($charge, $model_charge);
                }
                ?>
            </table>
        </section>
        <?php
    }

    public static function render_installments($total)
    {
        $gateway = new Gateway();

        echo esc_html($gateway->get_installments_by_type($total));
    }

    private static function get_payment_detail($charge, Charge $model_charge)
    {
        if ($charge->payment_method == 'boleto') {

            $due_at = new \DateTime($charge->last_transaction->due_at);

            ob_start()

        ?>
            <tr>
                <th><?php _e('Payment Type', 'woo-pagarme-payments'); ?>:</th>
                <td><?php _e('Boleto', 'woo-pagarme-payments'); ?></td>
            </tr>
            <tr>
                <th>Link:</th>
                <td>
                    <a href="<?php echo esc_url($charge->last_transaction->pdf); ?>">
                        <?php echo esc_url($charge->last_transaction->pdf); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th><?php _e('Line Code', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($charge->last_transaction->line); ?></td>
            </tr>
            <tr>
                <th><?php _e('Due at', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($due_at->format('d/m/Y')); ?></td>
            </tr>
            <tr>
                <th><?php _e('Paid value', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html(Utils::format_order_price_to_view($charge->amount)); ?></td>
            </tr>
            <tr>
                <th><?php _e('Status', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($model_charge->get_i18n_status($charge->status)); ?></td>
            </tr>
            <tr>
                <td></td>
            </tr>
        <?php

            $html = ob_get_contents();

            ob_end_clean();
        }

        if ($charge->payment_method == 'credit_card') {

            ob_start()

        ?>
            <tr>
                <th><?php _e('Payment Type', 'woo-pagarme-payments'); ?>:</th>
                <td><?php _e('Credit Card', 'woo-pagarme-payments'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Card Holder Name', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($charge->last_transaction->card->holder_name); ?></td>
            </tr>
            <tr>
                <th><?php _e('Card Brand', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($charge->last_transaction->card->brand); ?></td>
            </tr>
            <tr>
                <th><?php _e('Card number', 'woo-pagarme-payments'); ?>:</th>
                <td>
                    **** **** **** <?php echo esc_html($charge->last_transaction->card->last_four_digits); ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Installments', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($charge->last_transaction->installments); ?></td>
            </tr>
            <tr>
                <th><?php _e('Paid value', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html(Utils::format_order_price_to_view($charge->amount)); ?></td>
            </tr>
            <tr>
                <th><?php _e('Status', 'woo-pagarme-payments'); ?>:</th>
                <td><?php echo esc_html($model_charge->get_i18n_status($charge->status)); ?></td>
            </tr>
            <tr>
                <td></td>
            </tr>
<?php

            $html = ob_get_contents();

            ob_end_clean();
        }

        return $html;
    }
}
