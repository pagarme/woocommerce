<?php

namespace Woocommerce\Pagarme\Helper;

 class DocumentUtils
{
    public const CPF = 'cpf';

    public const CNPJ = 'cnpj';

    public static function isValidCnpj(string $document): bool
    {
        if (empty($document)) {
            return false;
        }

        $cnpjLength = 14;

        // Remove all non-alphanumeric characters and convert to uppercase
        $cnpjCleaned = strtoupper(Utils::only_alphanumeric($document));

        // CNPJ must have exactly 14 caracters after cleaning
        if (strlen($cnpjCleaned) !== $cnpjLength) {
            return false;
        }

        if(Utils::hasAllEqualCharacters($cnpjCleaned)) {
            return false;
        }

        // Multipliers for checksum calculation
        $m1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $m2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum1 = 0;
        $sum2 = 0;

        // Calculate sums in a single loop
        for ($i = 0; $i < 12; $i++) {
            $val = ord($cnpjCleaned[$i]) - ord('0');
            $sum1 += $val * $m1[$i];
            $sum2 += $val * $m2[$i];
        }

        // First digit verification
        $mod1 = $sum1 % 11;
        $digit1 = $mod1 < 2 ? 0 : 11 - $mod1;

        if ((ord($cnpjCleaned[12]) - ord('0')) !== $digit1) {
            return false;
        }

        // Second digit verification
        // Add digit1 contribution to the second sum
        $sum2 += $digit1 * $m2[12];
        $mod2 = $sum2 % 11;
        $digit2 = $mod2 < 2 ? 0 : 11 - $mod2;

        return (ord($cnpjCleaned[13]) - ord('0')) === $digit2;
    }

    public static function isValidCpf(string $cpf): bool
    {
        if (empty($cpf)) {
            return false;
        }

        $cpfLength = 11;

        // Keep only numbers
        $cpfCleaned = Utils::only_numbers($cpf);

        // CPF must have exactly 11 characters after cleaning
        if (strlen($cpfCleaned) !== $cpfLength) {
            return false;
        }

        // Reject all-same-character sequences
        if (Utils::hasAllEqualCharacters($cpfCleaned)) {
            return false;
        }

        // Validate both check digits
        for ($digit = 9; $digit < 11; $digit++) {
            $sum = 0;

            for ($i = 0; $i < $digit; $i++) {
                $sum += $cpfCleaned[$i] * (($digit + 1) - $i);
            }

            $remainder = (10 * $sum) % 11;
            $calculatedDigit = ($remainder === 10) ? 0 : $remainder;

            if ($cpfCleaned[$digit] != $calculatedDigit) {
                return false;
            }
        }

        return true;
    }
}