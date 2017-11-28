<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;

class Checkout
{
    private $setting;

    const API_REQUEST = 'e3hpgavff3cw';

    public function __construct()
    {
        $this->setting = Setting::get_instance();
    }
}
