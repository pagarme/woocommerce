<?php
if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Customer;
use Woocommerce\Mundipagg\Model\Setting;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Account;

$customer = new Customer( get_current_user_id() );

do_action( 'woocommerce_before_account_wallet' );

if ( $customer->cards ) :
	$api_route = get_home_url( null, '/wc-api/' . Account::WALLET_ENDPOINT );
	$swal_data = apply_filters( Core::tag_name( 'account_wallet_swal_data' ), array(
		'title'          => __( 'Waiting...', Core::TEXTDOMAIN ),
		'text'           => __( 'We are processing your request.', Core::TEXTDOMAIN ),
		'confirm_title'  => __( 'Are you sure?', Core::TEXTDOMAIN ),
		'confirm_text'   => __( 'You won\'t be able to revert this!', Core::TEXTDOMAIN ),
		'confirm_button' => __( 'Yes, delete it!', Core::TEXTDOMAIN ),
		'cancel_button'  => __( 'No, cancel!', Core::TEXTDOMAIN ),
		'confirm_color'  => '#3085d6',
		'cancel_color'   => '#d33',
	) );
?>

<table class="woocommerce-wallet-table shop_table shop_table_responsive"
	   data-swal='<?php echo wp_json_encode( $swal_data, JSON_HEX_APOS ); ?>'
	   data-api-request="<?php echo esc_url( $api_route ); ?>"
	   <?php echo Utils::get_component( 'wallet' ); ?>>
	<thead>
		<tr>
			<th class="woocommerce-wallet-name">
				<?php _e( 'Name', Core::TEXTDOMAIN ); ?>
			</th>
			<th class="woocommerce-wallet-last-digits">
				<?php _e( 'Last digits', Core::TEXTDOMAIN ); ?>
			</th>
			<th class="woocommerce-wallet-status">
				<?php _e( 'Status', Core::TEXTDOMAIN ); ?>
			</th>
			<th class="woocommerce-wallet-expire">
				<?php _e( 'Expire', Core::TEXTDOMAIN ); ?>
			</th>
			<th class="woocommerce-wallet-brand">
				<?php _e( 'Brand', Core::TEXTDOMAIN ); ?>
			</th>
			<th class="woocommerce-wallet-brand">
				<?php _e( 'Action', Core::TEXTDOMAIN ); ?>
			</th>
		</tr>
	</thead>
	<tbody>

		<?php foreach( $customer->cards as $card_id => $card ) : ?>

		<tr>
			<td>
				<?php echo esc_html( $card['holder_name'] ); ?>
			</td>
			<td>
				<?php echo esc_html( $card['last_four_digits'] ); ?>
			</td>
			<td>
				<?php echo esc_html( $card['status'] ); ?>
			</td>
			<td>
				<?php printf( '%s/%s', esc_html( $card['exp_month'] ), esc_html( $card['exp_year'] ) ); ?>
			</td>
			<td>
				<?php echo esc_html( $card['brand'] ); ?>
			</td>
			<td>
				<button class="woocommerce-button button" data-action="remove-card" data-value="<?php echo esc_attr( $card_id ); ?>">
					<?php _e( 'Remove', Core::TEXTDOMAIN ); ?>
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
		__( 'No credit card saved.', Core::TEXTDOMAIN )
	);

endif;

do_action( 'woocommerce_after_account_orders' );
