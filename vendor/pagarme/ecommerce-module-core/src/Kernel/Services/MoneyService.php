<?php

namespace Pagarme\Core\Kernel\Services;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;

final class MoneyService
{
    /**
     * @param int $amount
     *
     * @return float
     * @throws InvalidParamException
     */
    public function centsToFloat($amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidParamException("Amount should be an integer!", $amount);
        }

        return round($amount / 100, 2);
    }

    /**
     *
     * @param  float $amount
     * @return int
     */
    public function floatToCents($amount)
    {
        $amount = (float) $amount;
        return (int) round($amount * 100, 2);
    }

    /**
     * @param int $amount
     * @param string $currency
     *
     * @return string
     * @throws InvalidParamException
     */
    public function centsToPriceWithCurrencySymbol($amount, $currency = 'BRL')
    {
        $symbolsArray = [
            'BRL' => 'R$'
        ];

        $amount = $this->centsToFloat($amount);
        $amount = number_format($amount, 2, ',', '.');
        return $symbolsArray[$currency] . ' ' . $amount;
    }

    public function removeSeparators($amount)
    {
        return str_replace(
            ['.', ','],
            "",
            $amount ?? ''
        );
    }
}
