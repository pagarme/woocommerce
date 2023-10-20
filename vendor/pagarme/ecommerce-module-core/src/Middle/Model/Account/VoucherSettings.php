<?php

namespace Pagarme\Core\Middle\Model\Account;

class VoucherSettings extends PaymentMethodSettings
{
    /**
     * @param StoreSettings $storeSettings
     * @return mixed|string
     */
    public function validate($storeSettings)
    {
        if ($this->isPSP()) {
            return '';
        }

        return parent::validate($storeSettings);
    }
}
