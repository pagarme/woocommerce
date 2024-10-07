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

defined( 'ABSPATH' ) || exit;

use stdClass;
use WC_Order;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Customer;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;

/**
 * Abstract AbstractPayment
 * @package Woocommerce\Pagarme\Model\Payment
 */
abstract class AbstractPayment
{
    /** @var int */
    protected $suffix = null;

    /** @var string */
    protected $name = null;

    /** @var string */
    protected $code = null;

    /** @var array */
    protected $requirementsData = [];

    /** @var array */
    protected $dictionary = [];

    /** @var null */
    protected $charOrderValue = null;

    /** @var array */
    private $settings = [];

    /**
     * @return int
     * @throws \Exception
     */
    public function getSuffix()
    {
        return $this->suffix ?? $this->error($this->suffix);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getName()
    {
        return $this->name ?? $this->error($this->name);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getMethodCode()
    {
        return $this->code ?? $this->error($this->code);
    }

    /**
     * @return array
     */
    public function getRequirementsData()
    {
        return $this->requirementsData;
    }

    /**
     * @return array
     */
    public function renameFieldsPost(
        $field,
        $formattedPost,
        $arrayFieldKey
    ) {
        foreach ($this->dictionary as $fieldKey => $formatedPostKey) {
            if (in_array($fieldKey, $field)) {
                $field['name'] = $formatedPostKey;
                $formattedPost['fields'][$arrayFieldKey] = $field;
            }
        }
        return $formattedPost;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getReference()
    {
        return sha1((string)random_int(1, 1000));
    }

    /**
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    private function error($field)
    {
        throw new \Exception(__('Invalid data for payment method: ', 'woo-pagarme-payments') . $field);
    }

    /**
     * @param int|null $customerId
     * @return Customer
     */
    public function getCustomer(?int $customerId = null)
    {
        if(!$customerId) {
            $customerId = get_current_user_id();
        }
        return new Customer($customerId, new SavedCardRepository(), new CustomerRepository());
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return new Config();
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        $jsConfigProvider = [];
        if (is_subclass_of($this, Card::class )) {
            $jsConfigProvider['is_card'] = true;
        }
        return $jsConfigProvider;
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    public function getPayRequestBase(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        return ['payment_method' => $this->getMethodCode()];
    }

    /**
     * @param WC_Order $wc_order
     * @param array $form_fields
     * @param stdClass|null $customer
     * @return null[]|string[]
     * @throws \Exception
     */
    public function getPayRequest(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        $request = [];
        $content = $this->getPayRequestBase($wc_order, $form_fields, $customer);
        $content['amount'] = Utils::format_desnormalized_order_price($this->getAmount($wc_order, $form_fields));
        if ($multicustomers = $this->getMulticustomerData($this->code, $form_fields)) {
            $content['customer'] = $multicustomers;
        }
        $request[] = $content;
        return $request;
    }

    public function getSettings()
    {
        if (empty($this->settings)) {
            $optionName = sprintf('woocommerce_woo-pagarme-payments-%s_settings', $this->code);
            $this->settings = get_option($optionName, []);
        }

        return $this->settings;
    }

    protected function getAmount(WC_Order $wc_order, $form_fields)
    {
        $amount = $wc_order->get_total();
        $charOrderValue = $this->charOrderValue ?? $this->code . '_value';
        if ($value = Utils::get_value_by($form_fields, $charOrderValue)) {
            $amount = $value;
        }
        return $amount;
    }

    protected function getMulticustomerData($type, $form_fields)
    {
        $prefix     = "multicustomer_{$type}";
        $is_enabled = Utils::get_value_by($form_fields, "enable_multicustomers_{$type}");

        if (!$is_enabled) {
            return false;
        }

        $cpf      = Utils::get_value_by($form_fields, $prefix . '[cpf]');
        $zip_code = Utils::get_value_by($form_fields, $prefix . '[zip_code]');

        return array(
            'name'     => Utils::get_value_by($form_fields, $prefix . '[name]'),
            'email'    => Utils::get_value_by($form_fields, $prefix . '[email]'),
            'document' => Utils::format_document($cpf),
            'type'     => 'individual',
            'address' => array(
                'street'       => Utils::get_value_by($form_fields, $prefix . '[street]'),
                'number'       => Utils::get_value_by($form_fields, $prefix . '[number]'),
                'complement'   => Utils::get_value_by($form_fields, $prefix . '[complement]'),
                'neighborhood' => Utils::get_value_by($form_fields, $prefix . '[neighborhood]'),
                'zip_code'     => preg_replace('/[^\d]+/', '', $zip_code),
                'city'         => Utils::get_value_by($form_fields, $prefix . '[city]'),
                'state'        => Utils::get_value_by($form_fields, $prefix . '[state]'),
                'country'      => 'BR',
            ),
        );
    }

    protected function getBillingAddressFromCustomer($customer, WC_Order $wc_order)
    {
        $addressArray = isset($customer->address) ? (array) $customer->address : [];
        if (empty($addressArray)) {
            $addressArray = $this->getCustomerAddressFromWcOrder($wc_order);
        }

        return [
            'street' => $addressArray["street"],
            'complement' => $addressArray["complement"],
            'number' => $addressArray["number"],
            'zip_code' => $addressArray["zip_code"],
            'neighborhood' => $addressArray["neighborhood"],
            'city' => $addressArray["city"],
            'state' => $addressArray["state"],
            'country' => $addressArray["country"]
        ];
    }

    /**
     * @param WC_Order $wc_order
     * @return array
     */
    private function getCustomerAddressFromWcOrder(WC_Order $wc_order)
    {
        $order = new Order($wc_order->get_id());
        return Utils::build_customer_address_from_order($order);
    }
}
