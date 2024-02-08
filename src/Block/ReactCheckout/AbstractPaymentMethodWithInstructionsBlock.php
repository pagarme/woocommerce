<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Block\NewCheckout\AbstractPaymentMethodBlock;
use Woocommerce\Pagarme\Model\Payment\AbstractPaymentWithCheckoutInstructions;

class AbstractPaymentWithCheckoutInstructionsBlock extends AbstractPaymentMethodBlock
{
    /** @var AbstractPaymentWithCheckoutInstructions */
    protected $paymentModel;

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
