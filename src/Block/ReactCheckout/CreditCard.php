<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Block\Checkout\Form\Installments as InstallmentsBlock;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Payment\CreditCard as CreditCardModel;

class CreditCard extends AbstractPaymentMethodBlock
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-credit_card';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'credit_card';

    /** @var string */
    const ARIA_LABEL = 'Credit Card payment method';

    /** @var string */
    const TOKENIZE_URL = 'https://api.pagar.me/core/v5/tokens';

    /** @var CreditCardModel */
    protected $paymentModel;

    /** @var Config */
    protected $config;

    /** @var InstallmentsBlock */
    protected $installmentsBlock;

    public function __construct()
    {
        $paymentModel = new CreditCardModel();
        $this->config = new Config();
        $this->installmentsBlock = new InstallmentsBlock();
        parent::__construct($paymentModel);
    }

    /**
     * @return array
     */
    protected function getAdditionalPaymentMethodData()
    {
        return [
            'walletEnabled' => $this->config->getCcAllowSave(),
            'installmentsType' => intval($this->config->getCcInstallmentType()),
            'appId' => $this->config->getPublicKey(),
            'installments' => $this->getInstallments(),
            'fieldsLabels' => $this->getFieldsLabels(),
            'brands' => $this->paymentModel->getConfigDataProvider()['brands'],
            'errorMessages' => Core::credit_card_errors_pt_br()
        ];
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
            'walletLabel' => __('Saved Cards', 'woo-pagarme-payments'),
        ];
    }
}
