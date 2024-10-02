<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

use ReflectionClass;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Payment\Data\PaymentRequest;

defined( 'ABSPATH' ) || exit;

/**
 * Class PostFormatter
 * @package Woocommerce\Pagarme\Model\Payment
 */
class PostFormatter
{
    /** @var string */
    private $paymentMethod;

    /** @var ?string|int */
    private $orderId;

    /** @var Gateway */
    private $gateway;

    /**
     * @param Gateway|null $gateway
     * @param null $paymentMethod
     * @param null $orderId
     */
    public function __construct(
        Gateway $gateway = null,
        $paymentMethod = null,
        $orderId = null
    ) {
        $this->paymentMethod = $paymentMethod;
        $this->gateway = $gateway ?? new Gateway;
        $this->orderId = $orderId;
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function getPaymentMethod()
    {
        if ($this->paymentMethod) {
            return $this->paymentMethod;
        }
        if (!$this->paymentMethod) {
            if (array_key_exists('payment_method', $_POST)) {
                $this->paymentMethod = str_replace('woo-pagarme-payments-', '', sanitize_text_field($_POST['payment_method']));
                return $this->paymentMethod ;
            }
        }
        throw new \Exception(__('Empty payment method', 'woo-pagarme-payments'));
    }

    public function format($orderId = null)
    {
        $result = [];
        if ($orderId) {
            $this->orderId = $orderId;
        }
        if (!$this->orderId) {
            throw new \Exception(__('$orderId cannot be null', 'woo-pagarme-payments'));
        }
        $result['order'] = $this->orderId;
        $result['fields'] = [];
        $filteredPost = array_intersect_key($_POST, array_flip(
            $this->dataToFilterFromPost($this->getPaymentMethod())
        ));
        $result = $this->addsFilteredDataInFormattedPostArray($filteredPost, $result);
        $result = $this->renameFieldsFromFormattedPost($result, $this->getPaymentMethod());
        $result = $this->formatMulticustomerCardArray($result);
        $_POST = $result;
    }

    /**
     * @return void
     */
    public function assemblePaymentRequest()
    {
        $_POST[PaymentRequest::PAGARME_PAYMENT_REQUEST_KEY] = new PaymentRequest();
    }

    public function formatReactCheckout()
    {
        if (!empty($_POST['pagarme']) && is_string($_POST['pagarme'])) {
            $_POST['pagarme'] = json_decode($_POST['pagarme'], true);
        }
    }

    /**
     * @param $paymentMethod
     * @return array
     * @throws \Exception
     */
    private function dataToFilterFromPost($paymentMethod)
    {
        if ($paymentMethod) {
            return $this->gateway->getPaymentInstance($paymentMethod)->getRequirementsData();
        }
        return $_POST;
    }

    /**
     * @param $filteredPost
     * @param $formattedPost
     * @return array
     */
    private function addsFilteredDataInFormattedPostArray($filteredPost, $formattedPost)
    {
        foreach ($filteredPost as $key => $value) {
            $formattedPost['fields'][] = [
                "name" => sanitize_text_field($key),
                "value" => sanitize_text_field($value)
            ];
        }
        return $formattedPost;
    }

    /**
     * @param $formattedPost
     * @param $paymentMethod
     * @return array|mixed
     * @throws \Exception
     */
    private function renameFieldsFromFormattedPost($formattedPost, $paymentMethod)
    {
        foreach ($formattedPost['fields'] as $arrayFieldKey => $field) {
            $formattedPost = $this->applyForAllFields(
                $field,
                $formattedPost,
                $arrayFieldKey
            );
            if ($paymentMethod) {
                $formattedPost = $this->gateway->getPaymentInstance($paymentMethod)->renameFieldsPost($field, $formattedPost, $arrayFieldKey);
            }
        }
        return $formattedPost;
    }

    /**
     * @param $field
     * @param $formattedPost
     * @param $arrayFieldKey
     * @return array
     */
    private function applyForAllFields(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {
        if (in_array('payment_method', $field)) {
            $field['value'] = $this->paymentMethod;
            $formattedPost['fields'][$arrayFieldKey] = $field;
        }
        return $formattedPost;
    }

    /**
     * @param $formattedPost
     * @return array|mixed
     */
    private function formatMulticustomerCardArray($formattedPost)
    {
        foreach ($formattedPost['fields'] as $fieldsValue) {
            if (strstr($fieldsValue['name'], 'multicustomer_')) {
                $formattedPost = $this->addsDataInFormattedPost(
                    $fieldsValue['value'],
                    $fieldsValue['name'],
                    $formattedPost
                );
            }
        }
        return $formattedPost;
    }

    /**
     * @param $fieldValue
     * @param $fieldValueName
     * @param $formattedPost
     * @return array
     */
    private function addsDataInFormattedPost(
        $fieldValue,
        $fieldValueName,
        $formattedPost
    ) {
        foreach ($fieldValue as $key => $value) {
            $formattedPost['fields'][] = [
                "name" => $fieldValueName . '[' . $key . ']',
                "value" => $value
            ];
        }
        return $formattedPost;
    }
}
