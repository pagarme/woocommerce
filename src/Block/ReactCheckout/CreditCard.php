<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Block\Checkout\Form\Installments as InstallmentsBlock;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Payment\CreditCard as CreditCardModel;

class CreditCard extends AbstractCard
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-credit_card';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'credit_card';

    /** @var string */
    const ARIA_LABEL = 'Credit Card payment method';

    /** @var CreditCardModel */
    protected $paymentModel;

    public function __construct()
    {
        $paymentModel = new CreditCardModel();
        parent::__construct($paymentModel);
    }
}
