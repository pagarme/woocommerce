<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Action;

use Woocommerce\Pagarme\Controller\Checkout\CustomerFields;
use Woocommerce\Pagarme\Helper\Utils;

defined('ABSPATH') || exit;

/**
 * @used-by ActionsRunner
 */
class CustomerFieldsActions implements RunnerInterface
{
    const ADDRESS_TYPES = [
        'billing',
        'shipping'
    ];
    const DOCUMENT_TYPES = [
        'cpf',
        'cnpj',
    ];

    private $customerFields;

    public function __construct()
    {
        $this->customerFields = new CustomerFields();
    }

    public function run()
    {
        add_filter('woocommerce_checkout_init', array($this, 'enqueueScript'));
        add_filter('woocommerce_default_address_fields', array($this, 'overrideAddressFields'));
        add_filter('woocommerce_checkout_fields', array($this, 'addDocumentField'));
        add_action('woocommerce_checkout_process', array($this, 'validateDocument'));
    }

    public function enqueueScript()
    {
        $parameters = Utils::getRegisterScriptParameters('front/checkout', 'checkoutFields', ['jquery.mask']);
        wp_register_script(
            'pagarme_customer_fields',
            $parameters['src'],
            $parameters['deps'],
            $parameters['ver']
        );
        wp_enqueue_script('pagarme_customer_fields');
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function addDocumentField(array $fields): array
    {
        if ($this->customerFields->hasDocumentField($fields)) {
            return $fields;
        }

        foreach (self::ADDRESS_TYPES as $addressType) {
            $fields[$addressType]["{$addressType}_document"] = array(
                'label'       => __('Document', 'woo-pagarme-payments'),
                'placeholder' => __('CPF or CNPJ', 'woo-pagarme-payments'),
                'required'    => true,
                'class'       => array('form-row-wide'),
                'priority'    => 25
            );
        }

        return $fields;
    }

    /**
     * @return void
     * @uses isValidCnpj()
     * @uses isValidCpf()
     */
    public function validateDocument()
    {
        foreach (self::ADDRESS_TYPES as $addressType) {
            $document = $_POST["{$addressType}_document"];

            if (!$document) {
                continue;
            }

            $documentNumber = preg_replace('/\D/', '', $document);
            $documentLength = strlen($documentNumber);

            if ($documentLength !== 11 && $documentLength !== 14) {
                $errorMessage = sprintf(
                    __(
                        'Please, enter a valid document number in the <b>%s Document</b>.',
                        'woo-pagarme-payments'
                    ),
                    __(
                        ucwords($addressType),
                        'woo-pagarme-payments'
                    )
                );

                wc_add_notice($errorMessage, 'error');
                continue;
            }

            $documentType = $documentLength === 11 ? self::DOCUMENT_TYPES[0] : self::DOCUMENT_TYPES[1];
            $functionName = 'isValid' . ucwords($documentType);
            $isValidDocument = $this->{$functionName}($documentNumber);

            if (!$isValidDocument) {
                $errorMessage = sprintf(
                    __(
                        'Please, enter a valid %s number in the <b>%s Document</b>.',
                        'woo-pagarme-payments'
                    ),
                    strtoupper($documentType),
                    __(
                        ucwords($addressType),
                        'woo-pagarme-payments'
                    )
                );
                wc_add_notice($errorMessage, 'error');
            }
        }
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function overrideAddressFields(array $fields): array
    {
        $fields['address_1']['placeholder'] = __(
            'Street name, house number and neighbourhood',
            'woo-pagarme-payments'
        );
        $fields['address_2']['label'] = __(
            'Additional address data',
            'woo-pagarme-payments'
        );
        $fields['address_2']['label_class'] = '';

        return $fields;
    }

    /**
     * @param $cpf
     *
     * @return bool
     */
    private function isValidCpf($cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t ++) {
            for ($d = 0, $c = 0; $c < $t; $c ++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $cnpj
     *
     * @return bool
     */
    private function isValidCnpj($cnpj): bool
    {
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i ++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;

        if ($cnpj[12] != ($remainder < 2 ? 0 : 11 - $remainder)) {
            return false;
        }

        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i ++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;

        return $cnpj[13] == ($remainder < 2 ? 0 : 11 - $remainder);
    }
}
