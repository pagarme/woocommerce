<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Block\Checkout\Form\Installments as InstallmentsBlock;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Payment\Card;

abstract class AbstractCard extends AbstractPaymentMethodBlock
{
    /** @var Card */
    protected $paymentModel;

    /** @var Config */
    protected $config;

    /** @var InstallmentsBlock */
    protected $installmentsBlock;

    public function __construct($paymentModel)
    {
        $this->config = new Config();
        $this->installmentsBlock = new InstallmentsBlock();
        parent::__construct($paymentModel);
    }

    /**
     * @return array
     */
    protected function getAdditionalPaymentMethodData()
    {
        $additionalData = [
            'walletEnabled' => $this->isWalletEnabled(),
            'installmentsType' => intval($this->config->getCcInstallmentType() ?? 1),
            'appId' => $this->config->getPublicKey(),
            'installments' => $this->getInstallments(),
            'fieldsLabels' => $this->getFieldsLabels(),
            'brands' => $this->paymentModel->getConfigDataProvider()['brands'],
            'errorMessages' => Core::credit_card_errors_pt_br(),
            'cards' => $this->paymentModel->getCards()
        ];

        if ($additionalData['walletEnabled']) {
            $additionalData['cards'] = $this->assembleCardsInfoToCheckoutBlock();
        }

        return $additionalData;
    }

    protected function isWalletEnabled()
    {
        return $this->config->getCcAllowSave();
    }

    protected function getInstallments()
    {
        if ($this->installmentsBlock->isCcInstallmentTypeByFlag()) {
            return [];
        }

        return $this->installmentsBlock->render();
    }

    protected function getFieldsLabels()
    {
        return [
            'holderNameLabel' => __('Card Holder Name', 'woo-pagarme-payments'),
            'numberLabel' => __('Card Number', 'woo-pagarme-payments'),
            'expiryLabel' => __('Expiration Date', 'woo-pagarme-payments'),
            'cvvLabel' => __('Card code', 'woo-pagarme-payments'),
            'installmentsLabel' => __('Installments quantity', 'woo-pagarme-payments'),
            'saveCardLabel' => __('Save this card for future purchases', 'woo-pagarme-payments'),
            'walletLabel' => __('Saved cards', 'woo-pagarme-payments'),
        ];
    }

    protected function assembleCardsInfoToCheckoutBlock()
    {
        $cards = $this->paymentModel->getCards();

        $cardsInfo = [
            0 => [
                'value' => '',
                'brand' => '',
                'label' => __('Choose your saved card', 'woo-pagarme-payments')
            ]
        ];

        foreach ($cards as $card) {
            $lastFourDigits = $card->getLastFourDigits()->getValue();
            $cardsInfo[] = [
                'value' => $card->getPagarmeId()->getValue(),
                'brand' => strtolower($card->getBrand()->getName()),
                'label' => "•••• •••• •••• $lastFourDigits"
            ];
        }

        return $cardsInfo;
    }

}
