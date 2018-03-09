<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

// Exeption
use Exception;

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Setting;

// WooCommerce
use WC_Order;

class Gateway
{
	/**
	 * Credit Card Installment Type - Single
	 *
	 * A single settings for all flags.
	 *
	 */
	const CC_TYPE_SINGLE  = 1;

	/**
	 * Credit Card Installment Type - By Flag
	 *
	 * Settings for each flag.
	 *
	 */
	const CC_TYPE_BY_FLAG = 2;

	public $settings;

	public function __construct()
	{
		$this->settings = Setting::get_instance();
	}

	public function supported_currency()
	{
		return ( get_woocommerce_currency() === 'BRL' );
	}

	public function get_installment_options()
	{
		return array(
			2  => 2,
			3  => 3,
			4  => 4,
			5  => 5,
			6  => 6,
			7  => 7,
			8  => 8,
			9  => 9,
			10 => 10,
			11 => 11,
			12 => 12,
		);
	}

	public function get_installments_by_type( $total, $flag = false )
	{
		$flags             = $this->settings->flags;
		$type              = $this->settings->cc_installment_type;
		$max_installments  = intval( $this->settings->cc_installments_maximum );
		$no_interest       = intval( $this->settings->cc_installments_without_interest );
		$interest          = Utils::str_to_float( $this->settings->cc_installments_interest );
		$interest_increase = Utils::str_to_float( $this->settings->cc_installments_interest_increase );

		$method = '_calc_installments_' . $type;

		return $this->{$method}(
			compact( 'max_installments', 'no_interest', 'interest', 'interest_increase', 'total', 'flag' )
		);
	}

	public function render_installments_options( $total, $max_installments, $interest, $interest_increase, $no_interest )
	{
		$output = sprintf( 
			'<option value="1">%1$s</option>',
			__( 'At sight', Core::SLUG ) . ' ('. wc_price( $total ) . ')' 
		);

		$interest_base = $interest;

		for ( $times = 2; $times <= $max_installments; $times++ ) {
			$interest = $interest_base;
			$amount = $total;

			if ( $interest ) {

				if ( $interest_increase && $times > $no_interest + 1 ) {
					$interest += ( $interest_increase * ( $times - ( $no_interest + 1 ) ) );
				}

				$amount += Utils::calc_percentage( $interest, $total );
			}

			$value = $amount;

			if ( $times <= $no_interest ) {
				$value = $total;
			}

			$price = ceil( $value / $times * 100 ) / 100;
			$text  = sprintf( __( '%dx of %s (%s)', Core::TEXTDOMAIN ),
				$times,
				wc_price( $price ),
				wc_price( $value )
			);

			$amount = $total;

			if ( $times > $no_interest && $interest ) {
				$text .= " c/juros de {$interest}%";
			}

			$output .= sprintf( '<option value="%1$s">%2$s</option>', $times, $text );
		}

		return $output;
	}

	private function _calc_installments_1( array $params )
	{
		extract( $params );

		return $this->render_installments_options( $total, $max_installments, $interest, $interest_increase, $no_interest );
	}

	private function _calc_installments_2( array $params )
	{
		$settings_by_flag = $this->settings->cc_installments_by_flag;

		extract( $params );

		if ( ! $flag || ! isset( $settings_by_flag['max_installment'][ $flag ] ) ) {
			return sprintf( '<option value="">%s</option>', __( 'This flag not is allowed on checkout.', Core::SLUG ) );
		}

		$max_installments  = intval( $settings_by_flag['max_installment'][ $flag ] );
		$no_interest       = intval( $settings_by_flag['no_interest'][ $flag ] );
		$interest          = Utils::str_to_float( $settings_by_flag['interest'][ $flag ] );
		$interest_increase = Utils::str_to_float( $settings_by_flag['interest_increase'][ $flag ] );

		return $this->render_installments_options( $total, $max_installments, $interest, $interest_increase, $no_interest );
	}
}
