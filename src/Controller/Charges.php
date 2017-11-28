<?php
namespace Woocommerce\Mundipagg\Controller;

if ( ! function_exists( 'add_action' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Model\Charge;

class Charges
{
    public function __construct()
    {
       $this->_build_actions();
    }

    public function handle_actions( $body )
    {
        $charge = new Charge();
        $charge->create_from_webhook( $body );
    }

    private function _build_actions()
    {
        $events = array(
            'charge_created',
            'charge_updated',
            'charge_paid',
            'charge_pending'
        );

        foreach( $events as $event ) {
            add_action( "on_mundipagg_{$event}", array( $this, 'handle_actions' ) );
        }
    }
}    