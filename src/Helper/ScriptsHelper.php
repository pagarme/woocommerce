<?php

namespace Woocommerce\Pagarme\Helper;

use Woocommerce\Pagarme\Core;

class ScriptsHelper
{
    public function jsUrl($jsFileName, $path = 'admin')
    {
        $path = preg_replace("^/|/$", '', trim($path));
        return Core::plugins_url("assets/javascripts/{$path}/{$jsFileName}.js");
    }

}
