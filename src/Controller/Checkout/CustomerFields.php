<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Controller\Checkout;

class CustomerFields
{
    const ADDRESS_TYPES = [
        'billing',
        'shipping'
    ];
    const DOCUMENT_TYPES = [
        'cpf',
        'cnpj',
    ];

    /**
     * @param $fields
     *
     * @return bool
     */
    public function hasDocumentField($fields): bool
    {
        return array_key_exists('billing_cpf', $fields['billing'])
               || array_key_exists('billing_cnpj', $fields['billing']);
    }

    /**
     * @param string $cpf
     *
     * @return bool
     */
    public function isValidCpf(string $cpf): bool
    {
        if (!$this->isValidCpfFormat($cpf)) {
            return false;
        }

        for ($digit = 9; $digit < 11; $digit ++) {
            if (!$this->isValidCpfDigit($cpf, $digit)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $cnpj
     *
     * @return bool
     */
    public function isValidCnpj(string $cnpj): bool
    {
        if (!$this->isValidCnpjFormat($cnpj)) {
            return false;
        }

        $firstCheckDigit = $this->calculateCnpjCheckDigit(substr($cnpj, 0, 12), 5);
        if ($cnpj[12] != $firstCheckDigit) {
            return false;
        }

        $secondCheckDigit = $this->calculateCnpjCheckDigit(substr($cnpj, 0, 13), 6);

        return $cnpj[13] == $secondCheckDigit;
    }

    public function displayDocumentOrderMeta($order, $addressType)
    {
        if (!$order) {
            return;
        }

        $documentNumber = esc_html($order->get_meta($this->getDocumentMetaNameByAddressType($addressType), true));

        if (!$documentNumber) {
            return;
        }

        $metaLabel = sprintf(
            __(
                '%s Document',
                'woo-pagarme-payments'
            ),
            __(
                ucfirst($addressType),
                'woo-pagarme-payments'
            )
        );

        echo "<p><strong>{$metaLabel}:</strong> {$documentNumber}</p>";
    }

    /**
     * @param string $addressType
     *
     * @return string
     */
    private function getDocumentMetaNameByAddressType(string $addressType): string
    {
        return "_{$addressType}_document";
    }

    /**
     * @param string $cpf
     *
     * @return bool
     */
    private function isValidCpfFormat(string $cpf): bool
    {
        // Check if CPF length is exactly 11 and not all digits are the same
        return strlen($cpf) === 11 && !preg_match('/(\d)\1{10}/', $cpf);
    }

    /**
     * @param string $cpf
     * @param int $digit
     *
     * @return bool
     */
    private function isValidCpfDigit(string $cpf, int $digit): bool
    {
        $calculatedDigit = $this->calculateCpfDigit($cpf, $digit);

        return $cpf[$digit] == $calculatedDigit;
    }

    /**
     * @param string $cpf
     * @param int $digit
     *
     * @return int
     */
    private function calculateCpfDigit(string $cpf, int $digit): int
    {
        $sum = 0;

        for ($i = 0; $i < $digit; $i ++) {
            $sum += $cpf[$i] * (($digit + 1) - $i);
        }

        $remainder = (10 * $sum) % 11;

        return ($remainder === 10) ? 0 : $remainder;
    }

    /**
     * @param string $cnpj
     *
     * @return bool
     */
    private function isValidCnpjFormat(string $cnpj): bool
    {
        // Check if CNPJ is 14 characters long and not a sequence of repeated digits
        return strlen($cnpj) == 14 && !preg_match('/(\d)\1{13}/', $cnpj);
    }

    /**
     * @param string $cnpj
     * @param int $initialWeight
     *
     * @return int
     */
    private function calculateCnpjCheckDigit(string $cnpj, int $initialWeight): int
    {
        $sum = 0;
        $weight = $initialWeight;

        for ($i = 0; $i < strlen($cnpj); $i ++) {
            $sum += $cnpj[$i] * $weight;
            $weight = ($weight == 2) ? 9 : $weight - 1;
        }

        $remainder = $sum % 11;

        return ($remainder < 2) ? 0 : 11 - $remainder;
    }
}
