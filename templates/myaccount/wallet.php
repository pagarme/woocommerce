<?php
if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Customer;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Account;

$customer = new Customer(get_current_user_id());

do_action('woocommerce_before_account_wallet');

if ($customer->cards) :
    $api_route = get_home_url(null, '/wc-api/' . Account::WALLET_ENDPOINT);
    $swal_data = apply_filters(Core::tag_name('account_wallet_swal_data'), array(
        'title'          => __('Waiting...', 'woo-pagarme-payments'),
        'text'           => __('We are processing your request.', 'woo-pagarme-payments'),
        'confirm_title'  => __('Are you sure?', 'woo-pagarme-payments'),
        'confirm_text'   => __('You won\'t be able to revert this!', 'woo-pagarme-payments'),
        'confirm_button' => __('Yes, delete it!', 'woo-pagarme-payments'),
        'cancel_button'  => __('No, cancel!', 'woo-pagarme-payments'),
        'confirm_color'  => '#3085d6',
        'cancel_color'   => '#d33',
    ));
?>

    <table class="woocommerce-wallet-table shop_table shop_table_responsive" data-swal='<?php echo wp_json_encode($swal_data, JSON_HEX_APOS); ?>' data-api-request="<?php echo esc_url($api_route); ?>" <?php echo
                                                                                                                                                                                                        /** phpcs:ignore */
                                                                                                                                                                                                        Utils::get_component('wallet'); ?>>
        <thead>
            <tr>
                <th class="woocommerce-wallet-name">
                    <?php esc_html_e('Name', 'woo-pagarme-payments'); ?>
                </th>
                <th class="woocommerce-wallet-brand">
                    <?php esc_html_e('Type', 'woo-pagarme-payments'); ?>
                </th>
                <th class="woocommerce-wallet-last-digits">
                    <?php esc_html_e('Card', 'woo-pagarme-payments'); ?>
                </th>
                <th class="woocommerce-wallet-created-at">
                    <?php esc_html_e('Created At', 'woo-pagarme-payments'); ?>
                </th>
                <th class="woocommerce-wallet-brand">
                    <?php esc_html_e('Brand', 'woo-pagarme-payments'); ?>
                </th>
                <th class="woocommerce-wallet-brand">
                    <?php esc_html_e('Action', 'woo-pagarme-payments'); ?>
                </th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($customer->cards as $card_id => $card) : ?>

                <tr>
                    <td>
                        <?php echo esc_html($card->getOwnerName()); ?>
                    </td>
                    <td>
                        <?php echo esc_html(ucwords(str_replace('_', ' ', $card->getType()))); ?>
                    </td>
                    <td>
                        <?php echo esc_html("******" . $card->getLastFourDigits()->getValue()); ?>
                    </td>
                    <td>
                        <?php echo esc_html($card->getCreatedAt()->format("m/Y")); ?>
                    </td>
                    <td>
                        <?php echo esc_html($card->getBrand()->getName()); ?>
                    </td>
                    <td>
                        <button class="woocommerce-button button" data-action="remove-card" data-value="<?php echo esc_attr($card->getPagarmeId()->getValue()); ?>">
                            <?php esc_html_e('Remove', 'woo-pagarme-payments'); ?>
                        </button>
                    </td>
                </tr>

            <?php endforeach; ?>

        </tbody>
    </table>

<?php

else :

    printf(
        '<p class="woocommerce-message woocommerce-info">%s</p>',
        esc_html__('No credit card saved.', 'woo-pagarme-payments')
    );

endif;

do_action('woocommerce_after_account_orders');
