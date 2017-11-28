<?php
namespace Woocommerce\Mundipagg\Model;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

use Woocommerce\Mundipagg\Helper\Utils;

class Account
{
    private $setting;

    const WALLET_ENDPOINT = 'zff3yg2have4pcw';

    public function __construct()
    {
        $this->setting = Setting::get_instance();
    }
}
