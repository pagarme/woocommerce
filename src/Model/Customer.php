<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;

class Customer
{
	private $ID;

	private $cards;

	private $save_credit_card;

	public $prefix = '_mundipagg_wc_';

	public function __construct( $ID )
	{
		$this->ID = (int)$ID;
	}

	public function __get( $prop_name )
	{
		if ( isset( $this->{$prop_name} ) ) {
			return $this->{$prop_name};
		}

		return $this->get_property( $prop_name );
	}

	public function __set( $prop_name, $value )
	{
		switch ( $prop_name ) :

			case 'cards' :
				$value = $this->filter_cards( $value );
				break;

		endswitch;

		update_user_meta( $this->ID, $this->get_meta_key( $prop_name ), $value );
	}

	public function __isset( $prop_name )
	{
		return $this->__get( $prop_name );
	}

	public function get_property( $prop_name )
	{
		$value = get_user_meta( $this->ID, $this->get_meta_key( $prop_name ), true );

		switch ( $prop_name ) :

			case 'cards' :
				return $this->filter_cards( $value );

			default :
				return $value;
		endswitch;
	}

	public function get_meta_key( $name )
	{
		return $this->prefix . $name;
	}

	public function filter_cards( $cards )
	{
		return array_filter( (array)$cards );
	}
}
