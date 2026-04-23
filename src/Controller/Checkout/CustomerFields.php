<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Controller\Checkout;

use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;
use Automattic\WooCommerce\Blocks\Package;
use Exception;
use InvalidArgumentException;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Helper\DocumentUtils;
use WP_Error;

class CustomerFields
{
    const ADDRESS_TYPES = [
        'billing',
        'shipping'
    ];
    const DOCUMENT_TYPES = [
        DocumentUtils::CPF,
        DocumentUtils::CNPJ,
    ];

    /**
     * @return bool
     * @throws Exception
     */
    public function hasCheckoutBlocksDocumentField()
    {
        if (!Utils::isCheckoutBlocksActive()) {
            return false;
        }
        $checkoutFields = Package::container()->get(CheckoutFields::class);
        $possibleNames = array_merge(
            self::DOCUMENT_TYPES,
            [
                'document'
            ]
        );

        foreach ($possibleNames as $possibleName) {
            $hasDocument = preg_grep("/{$possibleName}/", $checkoutFields->get_address_fields_keys());

            if ($hasDocument) {
                return true;
            }
        }

        return false;
    }

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
     * @return void
     */
    public function validateDocument()
    {
        foreach (self::ADDRESS_TYPES as $addressType) {
            $fieldName = "{$addressType}_document";
            $document = $_POST[$fieldName] ?? '';

            if (empty($document)) {
                continue;
            }

            $documentNumber = Utils::only_alphanumeric($document);

            if (!$this->isValidDocumentLength($documentNumber)) {
                $errorMessage = sprintf(
                    __(
                        'Please, enter a valid document number in the <b>%s Document</b>.',
                        'woo-pagarme-payments'
                    ),
                    _x(
                        ucfirst($addressType),
                        'checkout-document-error',
                        'woo-pagarme-payments'
                    )
                );

                wc_add_notice($errorMessage, 'error', ['pagarme-error' => $fieldName]);
                continue;
            }

            if (!$this->isValidDocument($documentNumber)) {
                $documentType = $this->getDocumentType($documentNumber);
                $errorMessage = sprintf(
                    __(
                        'Please, enter a valid %s number in the <b>%s Document</b>.',
                        'woo-pagarme-payments'
                    ),
                    strtoupper($documentType),
                    _x(
                        ucfirst($addressType),
                        'checkout-document-error',
                        'woo-pagarme-payments'
                    )
                );
                wc_add_notice($errorMessage, 'error', ['pagarme-error' => $fieldName]);
            }
        }
    }

    /**
     * @param $documentNumber
     *
     * @return bool
     */
    private function isValidDocumentLength($documentNumber): bool
    {
        $documentLength = strlen($documentNumber);

        return $documentLength === 11 || $documentLength === 14;
    }

    /**
     * @param $documentNumber
     *
     * @return bool
     */
    private function isValidDocument($documentNumber): bool
    {
        $documentType = $this->getDocumentType($documentNumber);

        if (!in_array($documentType, self::DOCUMENT_TYPES, true)) {
            throw new InvalidArgumentException();
        }        

        if ($documentType === DocumentUtils::CPF)
            return DocumentUtils::isValidCpf($documentNumber);

        return DocumentUtils::isValidCnpj($documentNumber);
    }

    /**
     * @param $documentNumber
     *
     * @return string
     */
    private function getDocumentType($documentNumber): string
    {
        return Utils::getDocumentTypeByDocumentNumber($documentNumber);
    }

    /**
     * @param WP_Error $errors
     * @param $documentNumber
     *
     * @return WP_Error
     */
    public function validateCheckoutBlocksDocument(WP_Error $errors, $documentNumber)
    {
        $documentNumber = Utils::only_alphanumeric($documentNumber);
        $errorCode = 'pagarme_invalid_document';

        if (!$this->isValidDocumentLength($documentNumber)) {
            $errorMessage = __(
                'Please, enter a valid document number.',
                'woo-pagarme-payments'
            );

            $errors->add(
                $errorCode,
                $errorMessage
            );

            return $errors;
        }

        if (!$this->isValidDocument($documentNumber)) {
            $documentType = $this->getDocumentType($documentNumber);
            $errorMessage = sprintf(
                __(
                    'Please, enter a valid %s number.',
                    'woo-pagarme-payments'
                ),
                strtoupper($documentType)
            );

            $errors->add(
                $errorCode,
                $errorMessage
            );

            return $errors;
        }

        $errors->remove($errorCode);

        return $errors;
    }

    /**
     * @param $order
     * @param $addressType
     *
     * @return void
     */
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
}