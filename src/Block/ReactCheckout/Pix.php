<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Model\Payment\Pix as PixModel;
use Woocommerce\Pagarme\Block\ReactCheckout\AbstractPaymentMethodBlock;

class Pix extends AbstractPaymentMethodBlock
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-pix';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'pix';

    /** @var string */
    const ARIA_LABEL = 'Pix payment method';

    /** @var PixModel */
    protected $paymentModel;

    public function __construct()
    {
        $paymentModel = new PixModel();
        parent::__construct($paymentModel);
    }

    /**
     * @return array
     */
    protected function getAdditionalPaymentMethodData()
    {
        return [
            'instructions' => $this->paymentModel->getMessage(),
            'logo' => $this->paymentModel->getImage()
        ];
    }
}
