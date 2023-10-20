<?php

namespace Pagarme\Core\Middle\Model\Account;

abstract class PaymentEnum
{
    const BILLET = 'billet';

    const BILLET_ACCOUNT = 'boleto';

    const CREDIT_CARD = 'creditCard';

    const DEBIT_CARD = 'debitCard';

    const PIX = 'pix';

    const VOUCHER = 'voucher';
}
