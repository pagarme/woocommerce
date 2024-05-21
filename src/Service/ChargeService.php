<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Proxy\ChargeProxy;
use PagarmeCoreApiLib\APIException;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;

class ChargeService
{
    /**
     * @var CoreAuth
     */
    private $coreAuth;

    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
    }

    /**
     * @param $chargeId
     * @param $amount
     *
     * @return mixed
     * @throws APIException
     */
    public function refundCharge($chargeId, $amount)
    {
        $refundProxy = new ChargeProxy($this->coreAuth);
        return $refundProxy->refundCharge($chargeId, $amount);
    }
}
