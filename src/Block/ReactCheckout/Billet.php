<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Model\Payment\Billet as BilletModel;

class Billet extends AbstractPaymentWithCheckoutInstructionsBlock
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-billet';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'billet';

    /** @var string */
    const ARIA_LABEL = 'Billet payment method';

    /** @var BilletModel */
    protected $paymentModel;

    public function __construct()
    {
        $paymentModel = new BilletModel();
        parent::__construct($paymentModel);
    }
}
