<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Middle\Proxy\CardProxy;
use Woocommerce\Pagarme\Model\CoreAuth;
use Pagarme\Core\Middle\Model\Card;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Core\Payment\Aggregates\SavedCard;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\NumericString;
use Pagarme\Core\Payment\ValueObjects\CardId;

/**
 * This class implement Card
 */
class CardService
{


    protected $coreAuth;
    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
    }

    /**
     * @param string $token
     * @throws \Exception
     * @return mixed
     */
    public function create(string $token, $customerId)
    {
        $card = new Card();
        $card->setToken($token);
        $response = $this->createCardOnPagarme($card, $customerId);
        return $this->convertData($response);
    }

    public function getCard($cardId, $customerId)
    {
        $card = new Card();
        $card->setCardId($cardId);
        $response = $this->getCardOnPagarme($card, $customerId);
        return $this->convertData($response);
    }

    private function createCardOnPagarme(Card $card, $customerId)
    {
        $cardService = new CardProxy($this->coreAuth);
        return $cardService->createCard($card, $customerId);
    }

    private function getCardOnPagarme(Card $card, $customerId)
    {
        $cardService = new CardProxy($this->coreAuth);
        return $cardService->getCard($customerId, $card);
    }

    public function saveOnWalletPlatform($card)
    {
        $brand = $card['brand'];
        $cardRepository = new SavedCardRepository();
        $savedCard = new SavedCard();
        $savedCard->setPagarmeId(new CardId($card['cardId']));
        $savedCard->setBrand(CardBrand::$brand());
        $savedCard->setOwnerName($card['holder_name']);
        $savedCard->setFirstSixDigits(new NumericString($card['first_six_digits']));
        $savedCard->setLastFourDigits(new NumericString($card['last_four_digits']));
        $savedCard->setOwnerId(new CustomerId($card['owner_id']));
        $savedCard->setType($this->parsePaymentType($card['type']));
        $savedCard->setCreatedAt($card['created_at']);
        $cardRepository->save($savedCard);
    }

    private function parsePaymentType($type)
    {
        if ($type === 'credit') {
            return 'credit_card';
        }
        return $type;
    }

    public function convertData($response)
    {
        return [
            'cardId' => $response->id,
            'owner_id' => $response->customer->id,
            'brand' => $response->brand,
            'type' => $response->type,
            'holder_name' => $response->holderName,
            'first_six_digits' => $response->firstSixDigits,
            'last_four_digits' => $response->lastFourDigits,
            'created_at' => $response->createdAt,
        ];
    }


}