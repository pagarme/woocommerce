<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Block\ReactCheckout\AbstractPaymentMethodBlock;
use Woocommerce\Pagarme\Model\Payment\AbstractPaymentWithCheckoutInstructions;

abstract class AbstractPaymentWithCheckoutInstructionsBlock extends AbstractPaymentMethodBlock
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
