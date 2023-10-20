<?php

namespace Pagarme\Core\Middle\Model\Account;

class VoucherSettings extends PaymentMethodSettings
{
    public function validate(StoreSettings $storeSettings)
    {
        if ($this->isPSP()) {
            return '';
        }

        return parent::validate($storeSettings);
    }
}
