<?php
namespace Pagarme\Core\Mark1\Model;

use Pagarme\Core\Mark1\Interface\CardInterface;
use Woocommerce\Pagarme\Model\Customer;
use \Exception;
use InvalidArgumentException;
use \Throwable;
use WpOrg\Requests\Exception\InvalidArgument;

class Card implements CardInterface
{
    private string $token;   
    public function setToken($token)
    {
        $this->token = $token;
    }

    public function isValid()
    {
        if(empty($token)){
            return new InvalidArgumentException("Token not valid");
        }
        return true;
    }
    public function convertToSdk()
    {
        $array = [
            'options'=> [
                'verify_card'=> true
            ],
            'token' => $this->token
        ];
        return $array;
    }
}