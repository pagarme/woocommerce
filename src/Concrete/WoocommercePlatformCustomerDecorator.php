<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Pagarme\Core\Payment\Repositories\CustomerRepository;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use Woocommerce\Pagarme\Model\Customer;

class WoocommercePlatformCustomerDecorator implements PlatformCustomerInterface
{
    protected $platformCustomer;

    /**
     * @var CustomerId
     */
    protected $pagarmeId;

    public function __construct($platformCustomer = null)
    {
        $this->platformCustomer = $platformCustomer;
    }

    public function getCode()
    {
        return $this->platformCustomer->get_id();
    }

    /**
     * @return CustomerId|null
     */
    public function getPagarmeId()
    {
        $customer = new Customer(
            $this->platformCustomer->get_id(),
            new SavedCardRepository(),
            new CustomerRepository()
        );

        if ($customer !== null) {
            $this->pagarmeId = $customer->customer_id;
            return $this->pagarmeId;
        }

        return null;
    }

    public function getName()
    {
        $fullname = [
            $this->platformCustomer->get_first_name(),
            $this->platformCustomer->get_last_name()
        ];

        return implode(" ", $fullname);
    }

    public function getEmail()
    {
        return $this->platformCustomer->get_email();
    }

    public function getDocument()
    {
        // Not implemented on Woocommerce because there is no document field on customer (wordpress user), only on order
    }

    public function getType()
    {
        return CustomerType::individual();
    }

    public function getAddress()
    {
        /** @TODO */
    }

    public function getPhones()
    {
        /** @TODO */
    }
}
