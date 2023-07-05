<?php

namespace Woocommerce\Pagarme\Model;

use Pagarme\Core\Middle\Service\CardService;
use Woocommerce\Pagarme\Model\CoreAuth;
use Pagarme\Core\Middle\Model\Card as CoreCard;
use Pagarme\Core\Middle\Interfaces\CardInterface;

/**
 * This class implement Card
 */
class Card implements CardInterface
{


    protected $coreAuth;
    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
    }

    /**
     * Summary of create
     * @param string $token
     * @param Customer $customer
     * @throws \Exception
     * @return mixed
     */
    public function create(string $token, $customer)
    {
        $card = new CoreCard();
        $card->setToken($token);
        $response = $this->createCardOnPagarme($card, $customer);
        return $this->convertData($response);
    }

    public function getCard($cardId, $customer)
    {
        $card = new CoreCard();
        $card->setCardId($cardId);
        $response = $this->getCardOnPagarme($card, $customer);
        return $this->convertData($response);
    }
    private function createCardOnPagarme(CoreCard $card, $customer)
    {
        $cardService = new CardService($this->coreAuth);
        return $cardService->createCard($card, $customer);
    }
    private function getCardOnPagarme(CoreCard $card, $customer)
    {
        $cardService = new CardService($this->coreAuth);
        return $cardService->getCard($customer, $card);
    }

    public function convertData($response)
    {
        $data = [
            'cardId' => $response->id,
            'brand' => $response->brand,
            'holder_name' => $response->holderName,
            'first_six_digits' => $response->firstSixDigits,
            'last_four_digits' => $response->lastFourDigits
        ];

        return $data;
    }


}