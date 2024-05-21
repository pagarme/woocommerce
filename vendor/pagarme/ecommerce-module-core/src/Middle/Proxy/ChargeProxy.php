<?php

namespace Pagarme\Core\Middle\Proxy;

use Pagarme\Core\Middle\Client;
use PagarmeCoreApiLib\APIException;
use PagarmeCoreApiLib\Models\CreateCancelChargeRequest;

class ChargeProxy
{

    private $client;

    /**
     * @param Client $auth
     */
    public function __construct(Client $auth)
    {
        $this->client = $auth->services();
    }

    /**
     * @throws APIException
     */
    public function refundCharge($chargeId, $amount)
    {
        return $this->client->getCharges()->cancelCharge(
            $chargeId,
            new CreateCancelChargeRequest($amount, null, null, null)
        );
    }

}
