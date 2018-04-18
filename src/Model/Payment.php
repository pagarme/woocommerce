<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Setting;
use Woocommerce\Mundipagg\Resource\Tokens;

class Payment
{
	/**
	 * Payment methods : billet, billet_and_card, 2_cards, credit_card
	 * @var string
	 */
	public $payment_method;

	public function __construct( $payment_method )
	{
		$this->payment_method = $payment_method;
		$this->settings       = Setting::get_instance();
	}

	/**
	 * Return the payment array for API request
	 *
	 * @param $form_fields array Sent form fields
	 * @param $customer object response of Woocommerce\Mundipagg\Resource\Customer
	 *
	 * @return array
	 */
	public function get_payment_data( $form_fields, $customer )
	{
		$method_name = $this->get_method_name();

		if ( ! method_exists( $this, $method_name ) ) {
	    	throw new \Exception( 'Payment method name not exists' );
	  	}

	  	$data = $this->{$method_name}( $form_fields, $customer );

	  	if ( $method_name == 'pay_billet_and_card' || $method_name == 'pay_2_cards' ) {
	  		return $data;
	  	}

	  	return array( $data );
	}

	/**
	 * Return method name according to the selected payment method
	 *
	 * @return string
	 */
	public function get_method_name()
	{
		return "pay_{$this->payment_method}";
	}

	/**
	 * Return payment data for "boleto"
	 *
	 * @return array
	 */
	public function pay_billet()
    {
    	$expiration_date = new \DateTime();

    	if ( $days = (int)$this->settings->billet_deadline_days ) {
    		$expiration_date->modify( "+{$days} day" );
    	}

    	return array(
			'payment_method' => 'boleto',
			'boleto' => array(
				'bank'         => $this->settings->billet_bank,
				'instructions' => $this->settings->billet_instructions,
				'due_at'       => $expiration_date->format( 'c' )
			)
		);
    }

    /**
	 * Return payment data for "credit_card"
	 *
	 * @param $form_fields array Sent form fields
	 * @param $customer object response of Woocommerce\Mundipagg\Resource\Customer
	 *
	 * @return array
	 */
	public function pay_credit_card( $form_fields, $customer, $is_second_card = false )
    {
		$suffix     = $is_second_card ? '2' : '';
		$card_data = array(
			'payment_method' => 'credit_card',
			'credit_card'    => array(
				'installments'         => Utils::get_value_by( $form_fields, "installments{$suffix}" ),
				'statement_descriptor' => $this->settings->cc_soft_descriptor,
				'capture'              => $this->settings->is_active_capture()
			)
		);

		return $this->_handle_credit_card_type( $form_fields, $card_data, $suffix );
    }

    /**
	 * Return payment data for "billet_and_card"
	 *
	 * @param $form_fields array Sent form fields
	 * @param $customer object response of Woocommerce\Mundipagg\Resource\Customer
	 *
	 * @return array
	 */
    public function pay_billet_and_card( $form_fields, $customer )
    {
		$billet = $this->pay_billet();
		$card   = $this->pay_credit_card( $form_fields, $customer );

		if ( ! is_array( $card ) && $card->code != 200 ) {
			return $card;
		}

		$billet_amount = Utils::get_value_by( $form_fields, 'billet_value' );
		$card_amount   = Utils::get_value_by( $form_fields, 'card_order_value' );
		$card_amount   = $this->get_price_with_interest( 
			Utils::str_to_float( $card_amount ),
			Utils::get_value_by( $form_fields, 'installments' )
		);

		$billet['amount'] = Utils::format_order_price( $billet_amount );
		$card['amount']   = Utils::format_order_price( $card_amount );

		return array( $billet, $card );
    }

     /**
	 * Return payment data for "2_cards"
	 *
	 * @param $form_fields array Sent form fields
	 * @param $customer object response of Woocommerce\Mundipagg\Resource\Customer
	 *
	 * @return array
	 */
    public function pay_2_cards( $form_fields, $customer )
    {
		$card  = $this->pay_credit_card( $form_fields, $customer );
		$card2 = $this->pay_credit_card( $form_fields, $customer, true );

		$card_amount  = Utils::get_value_by( $form_fields, 'card_order_value' );
		$card2_amount = Utils::get_value_by( $form_fields, 'card_order_value2' );
		
		$card_amount = $this->get_price_with_interest( 
			Utils::str_to_float( $card_amount ),
			Utils::get_value_by( $form_fields, 'installments' )
		);
		
		$card2_amount = $this->get_price_with_interest( 
			Utils::str_to_float( $card2_amount ),
			Utils::get_value_by( $form_fields, 'installments2' )
		);

		$card['amount']  = Utils::format_order_price( $card_amount );
		$card2['amount'] = Utils::format_order_price( $card2_amount );

		return array( $card, $card2 );
	}
	
	public function get_price_with_interest( $price, $installments )
	{
		$amount            = $price;
		$max_installments  = intval( $this->settings->cc_installments_maximum );
		$no_interest       = intval( $this->settings->cc_installments_without_interest );
		$interest          = Utils::str_to_float( $this->settings->cc_installments_interest );
		$interest_increase = Utils::str_to_float( $this->settings->cc_installments_interest_increase );

		if ( $installments <= $no_interest ) {
			return $amount;
		}

		if ( $interest ) {
			if ( $interest_increase && $installments > $no_interest + 1 ) {
				$interest += ( $interest_increase * ( $installments - ( $no_interest + 1 ) ) );
			}
			$amount += Utils::calc_percentage( $interest, $price );
		}

		return $amount;
	}

    /**
     * Return which credit card type will be send. (card ou card_token)
     *
     * @param  array $form_fields
     * @param  array $card_data
     * @param  string $suffix Suffix for each attribute
     *
     * @return array
     */
    private function _handle_credit_card_type( $form_fields, $card_data, $suffix = '' )
    {
		$card_id = Utils::get_value_by( $form_fields, "card_id{$suffix}", false );
		$munditoken = ! $suffix ? 'munditoken1' : "munditoken{$suffix}";

    	if ( $card_id ) {
    		$card_data['credit_card']['card_id'] = $card_id;
    	} else {
    		$card_data['credit_card']['card_token'] = Utils::get_value_by( $form_fields, $munditoken );
    	}

    	return $card_data;
    }
}
