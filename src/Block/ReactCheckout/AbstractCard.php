<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Block\Checkout\Form\Card as CardBlock;
use Woocommerce\Pagarme\Block\Checkout\Form\Installments as InstallmentsBlock;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Payment\Card;

abstract class AbstractCard extends AbstractPaymentMethodBlock {
    /** @var Card */
    protected $paymentModel;

    /** @var Config */
    protected $config;

    /** @var InstallmentsBlock */
    protected $installmentsBlock;

    public function __construct( $paymentModel ) {
        $this->config            = new Config();
        $this->installmentsBlock = new InstallmentsBlock();
        parent::__construct( $paymentModel );
    }

    /**
     * @return array
     */
    protected function getAdditionalPaymentMethodData() {
        $additionalData = [
            'walletEnabled'    => $this->isWalletEnabled(),
            'installmentsType' => intval( $this->config->getCcInstallmentType() ?? 1 ),
            'appId'            => $this->config->getPublicKey(),
            'installments'     => $this->getInstallments(),
            'fieldsLabels'     => $this->getFieldsLabels(),
            'brands'           => $this->paymentModel->getConfigDataProvider()['brands'],
            'errorMessages'    => CardBlock::getCardErrorsMessagesTranslated(),
            'cards'            => $this->paymentModel->getCards(),
            'fieldErrors'      => $this->getFieldErrors()
        ];

        if ( $additionalData['walletEnabled'] ) {
            $additionalData['cards'] = $this->assembleCardsInfoToCheckoutBlock();
        }

        return $additionalData;
    }

    protected function isWalletEnabled() {
        return $this->config->getCcAllowSave();
    }

    protected function getInstallments() {
        if ( $this->installmentsBlock->isCcInstallmentTypeByFlag() ) {
            return [];
        }

        return $this->installmentsBlock->render();
    }

    protected function getFieldsLabels() {
        return [
            'holderNameLabel'   => __( 'Card Holder Name', 'woo-pagarme-payments' ),
            'numberLabel'       => __( 'Card number', 'woo-pagarme-payments' ),
            'expiryLabel'       => __( 'Expiration Date (MM/AA)', 'woo-pagarme-payments' ),
            'cvvLabel'          => __( 'Card code (CVV)', 'woo-pagarme-payments' ),
            'installmentsLabel' => __( 'Installments quantity', 'woo-pagarme-payments' ),
            'saveCardLabel'     => __( 'Save this card for future purchases', 'woo-pagarme-payments' ),
            'walletLabel'       => __( 'Saved cards', 'woo-pagarme-payments' ),
        ];
    }

    protected function getFieldErrors() {
        return [
            'holderName'         => __( 'Please enter a valid name.', 'woo-pagarme-payments' ),
            'cardNumber'         => __( 'Please enter a valid credit card number.', 'woo-pagarme-payments' ),
            'emptyExpiry'        => __( 'Please enter a expiry date.', 'woo-pagarme-payments' ),
            'invalidExpiryMonth' => __( 'The expiry month must be between 01 and 12.', 'woo-pagarme-payments' ),
            'invalidExpiryYear'  => __( 'The expiry year must have two digits.', 'woo-pagarme-payments' ),
            'expiredCard'        => __( 'The expiration date is expired.', 'woo-pagarme-payments' ),
            'emptyCvv'           => __( 'Please enter a valid CVV number.', 'woo-pagarme-payments' ),
            'invalidCvv'         => __( 'The CVV number must be between 3 and 4 characters.', 'woo-pagarme-payments' ),
        ];
    }

    protected function assembleCardsInfoToCheckoutBlock() {
        $cards = $this->paymentModel->getCards();

        if ( ! $cards ) {
            return [];
        }

        $cardsInfo = [
            0 => [
                'value' => '',
                'brand' => '',
                'label' => __( 'Choose your saved card', 'woo-pagarme-payments' )
            ]
        ];

        foreach ( $cards as $card ) {
            $lastFourDigits = $card->getLastFourDigits()->getValue();
            $cardsInfo[]    = [
                'value' => $card->getPagarmeId()->getValue(),
                'brand' => strtolower( $card->getBrand()->getName() ),
                'label' => "•••• •••• •••• $lastFourDigits"
            ];
        }

        return $cardsInfo;
    }

}
