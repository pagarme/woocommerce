<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Charge;

// WooCommerce
use WC_Order;

class Order extends Meta
{
	protected $response_data;
	protected $payment_method;
	protected $mundipagg_status;
	protected $mundipagg_id;

	// == BEGIN WC ORDER ==
	protected $billing_persontype;
	protected $billing_cnpj;
	protected $billing_first_name;
	protected $billing_last_name;
	protected $billing_email;
	protected $billing_birthdate;
	protected $billing_phone;
	protected $billing_address_1;
	protected $billing_address_2;
	protected $billing_number;
	protected $billing_neighborhood;
	protected $billing_city;
	protected $billing_state;
	protected $billing_postcode;
	protected $billing_cpf;
	// == END WC ORDER ==

	public $with_prefix = array(
		'payment_method'   => 1,
		'response_data'    => 1,
		'mundipagg_status' => 1,
		'mundipagg_id'     => 1
	);

	public function get_status_translate()
	{
		$status = strtolower( $this->__get( 'mundipagg_status' ) );
		$texts  = array(
			'paid'     => __( 'Paid', Core::TEXTDOMAIN ),
			'pending'  => __( 'Pending', Core::TEXTDOMAIN ),
			'canceled' => __( 'Canceled', Core::TEXTDOMAIN ),
			'failed'   => __( 'Failed', Core::TEXTDOMAIN )
		);

		return isset( $texts[ $status ] ) ? $texts[ $status ] : false;
	}

	public function payment_on_hold()
	{
		$order          = new WC_Order( $this->ID );
		$current_status = $order->get_status();

		if ( $current_status != 'on-hold' ) {
			$order->update_status( 'on-hold', __( 'MundiPagg: Awaiting payment confirmation.', Core::TEXTDOMAIN ) );
			wc_reduce_stock_levels( $order->get_order_number() );
		}
	}

	public function get_charges()
	{
		$model = new Charge();
		$items = $model->find_by_wc_order( $this->ID );

        if ( ! $items ) {
            return false;
        }

        $list = [];

        foreach ( $items as $item ) {
			$charge = new \stdClass();
			$charge = maybe_unserialize( $item->charge_data );
			$list[] = $charge;
		}
		
		return $list;
	}
}
