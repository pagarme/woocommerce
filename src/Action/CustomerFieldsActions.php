<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Action;

use Exception;
use Woocommerce\Pagarme\Controller\Checkout\CustomerFields;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use WP_Error;

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
        add_action('woocommerce_init', array($this, 'addDocumentFieldOnCheckoutBlocks'));
        add_action('woocommerce_validate_additional_field', array($this, 'validateCheckoutBlocksDocument'), 10, 3);
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
            $parameters['ver'],
            true
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
     * @throws Exception
     */
    public function addDocumentFieldOnCheckoutBlocks()
    {
        if (
            $this->customerFields->hasCheckoutBlocksDocumentField()
            || !function_exists('woocommerce_register_additional_checkout_field')
        ) {
            return;
        }

        woocommerce_register_additional_checkout_field(
            array(
                'id'                         => 'address/document',
                'label'                      => __('CPF or CNPJ', 'woo-pagarme-payments'),
                'location'                   => 'address',
                'type'                       => 'text',
                'class'                      => array('form-row-wide'),
                'required'                   => true,
                'index'                      => 25,
                'show_in_order_confirmation' => true
            )
        );
    }

    /**
     * @return void
     */
    public function validateDocument()
    {
        $this->customerFields->validateDocument();
    }

    /**
     * @param WP_Error $errors
     * @param $fieldKey
     * @param $documentNumber
     *
     * @return void
     */
    public function validateCheckoutBlocksDocument(WP_Error $errors, $fieldKey, $documentNumber)
    {
        if ($fieldKey == 'address/document') {
            $this->customerFields->validateCheckoutBlocksDocument($errors, $documentNumber);
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
        $config = new Config();

        if (!$config->getModifyAddress()) {
            return $fields;
        }

        $fields['address_1']['placeholder'] = __(
            'Street, number and neighbourhood',
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
