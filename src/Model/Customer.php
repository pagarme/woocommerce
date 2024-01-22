<?php

namespace Woocommerce\Pagarme\Model;
use Pagarme\Core\Middle\Model\Customer as CustomerMiddle;

if (!defined('ABSPATH')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Pagarme\Core\Payment\Repositories\SavedCardRepository as CoreSavedCardRepository;
use Pagarme\Core\Payment\Repositories\CustomerRepository as CoreCustomerRepository;
use Woocommerce\Pagarme\Service\CustomerService;

class Customer
{
    private $ID;

    private $cards;

    private $customer_id;

    private $save_credit_card;

    public $prefix = '_pagarme_wc_';

    private $customerRepository;
    private $cardRepository;

    /**
     * @param mixed $ID
     * @param CoreSavedCardRepository $cardRepository
     * @param CoreCustomerRepository $customerRepository
     */
    /** phpcs:disable */
    public function __construct($ID, $cardRepository, $customerRepository)
    {
        $this->ID = (int) $ID;
        $this->cardRepository = $cardRepository;
        $this->customerRepository = $customerRepository;
    }

    public function __get($prop_name)
    {
        if (isset($this->{$prop_name})) {
            return $this->{$prop_name};
        }

        return $this->get_property($prop_name);
    }

    public function __set($prop_name, $value)
    {
        if ($prop_name === 'cards') {
            $value = $this->filter_cards($value);
        }

        update_user_meta($this->ID, $this->get_meta_key($prop_name), $value);
        return $this;
    }

    public function __isset($prop_name)
    {
        return $this->__get($prop_name);
    }

    public function get_property($prop_name)
    {
        $value = get_user_meta($this->ID, $this->get_meta_key($prop_name), true);


        if ($prop_name === 'cards') {
            return $this->get_cards($value);
        }
        
        return $value;
    }

    public function get_cards($types = null, $includeEmptyType = true)
    {
        if ($this->cards) {
            return $this->cards;
        }
        $coreCustomer = $this->customerRepository->findByCode($this->ID);

        if (!$coreCustomer) {
            return null;
        }

        if (is_array($types)) {
            foreach ($types as $type) {
                if (is_object($type)) {
                    $types = null;
                    break;
                }
            }
        }

        $this->cards =
            $this->cardRepository->findByOwnerId(
                $coreCustomer->getPagarmeId(),
                $types,
                $includeEmptyType
            );

        return $this->cards;
    }

    public function get_meta_key($name)
    {
        return $this->prefix . $name;
    }

    public function filter_cards($cards)
    {
        return array_filter((array) $cards);
    }

    public function getPagarmeCustomerId()
    {
        $customer = $this->customerRepository->findByCode($this->ID);
        if (empty($customer)) {
            return false;
        }
        return $customer->getPagarmeId()->getValue();
    }
    
    public function savePagarmeCustomerId($code, $pagarmeId)
    {
        $customerService = new CustomerService();
        $customer = new CustomerMiddle();
        $customer->setCode($code);
        $customer->setPagarmeId($pagarmeId);
        $customerService->saveOnPlatform($customer);
    }
}