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
    private $customerFields;

    public function __construct()
    {
        $this->customerFields = new CustomerFields();
    }

    public function run()
    {
        add_filter('woocommerce_checkout_init', array($this, 'enqueueScript'));
        add_filter('woocommerce_checkout_fields', array($this, 'addDocumentField'));
        add_action('woocommerce_checkout_process', array($this, 'validateDocument'));
        add_action(
            'woocommerce_admin_order_data_after_billing_address',
            array($this, 'displayBillingDocumentOrderMeta')
        );
        add_action(
            'woocommerce_admin_order_data_after_shipping_address',
            array($this, 'displayShippingDocumentOrderMeta')
        );
        add_filter('woocommerce_default_address_fields', array($this, 'overrideAddressFields'));
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

        foreach ($this->customerFields::ADDRESS_TYPES as $addressType) {
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
     * @uses CustomerFields::isValidCnpj()
     * @uses CustomerFields::isValidCpf()
     */
    public function validateDocument()
            array(
                'id'                => 'pagarme/document',
    {
        foreach ($this->customerFields::ADDRESS_TYPES as $addressType) {
            $fieldName = "{$addressType}_document";
            $document = $_POST[$fieldName];

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
                    _x(
                        ucfirst($addressType),
                        'checkout-document-error',
                        'woo-pagarme-payments'
                    )
                );

                wc_add_notice($errorMessage, 'error', ['pagarme-error' => $fieldName]);
                continue;
            }

            $documentType = $documentLength === 11
                ? $this->customerFields::DOCUMENT_TYPES[0]
                : $this->customerFields::DOCUMENT_TYPES[1];
            $functionName = 'isValid' . ucfirst($documentType);
            $isValidDocument = $this->customerFields->{$functionName}($documentNumber);

            if (!$isValidDocument) {
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
     * @param $order
     *
     * @return void
     */
    public function displayBillingDocumentOrderMeta($order)
    {
        $this->customerFields->displayDocumentOrderMeta($order, $this->customerFields::ADDRESS_TYPES[0]);
    }

    /**
     * @param $order
     *
     * @return void
     */
    public function displayShippingDocumentOrderMeta($order)
    {
        $this->customerFields->displayDocumentOrderMeta($order, $this->customerFields::ADDRESS_TYPES[1]);
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
}
