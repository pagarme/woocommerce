<?php
namespace Pagarme\Core\Middle\Model;

use PagarmeCoreApiLib\Models\CreateCardOptionsRequest;
use Pagarme\Core\Middle\Interfaces\CardInterface;
use PagarmeCoreApiLib\Models\CreateCardRequest;
use InvalidArgumentException;

/**
 * This class is responsible for the business rules
 */
class Card implements CardInterface
{
    private $token;
    private $cardId;

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setCardId($cardId)
    {
        $this->cardId = $cardId;
    }

    public function getCardId()
    {
        return $this->cardId;
    }

    public function isValid()
    {
        if (empty($this->getToken())) {
            return new InvalidArgumentException("Token not valid");
        }
        return true;
    }

    public function convertToSdk()
    {
        $cardRequest = new CreateCardRequest();
        $cardRequest->options = new CreateCardOptionsRequest(true);
        $cardRequest->token = $this->getToken();
        return $cardRequest;
    }
}
