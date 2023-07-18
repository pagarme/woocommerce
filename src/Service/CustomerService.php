<?php

namespace Woocommerce\Pagarme\Service;

use WC_Customer;
use Woocommerce\Pagarme\Model\CoreAuth;
use Pagarme\Core\Middle\Model\Customer;
use Pagarme\Core\Middle\Proxy\CustomerProxy;
use Pagarme\Core\Middle\Model\Customer\Phones;
use Pagarme\Core\Middle\Model\Customer\Address;
use Pagarme\Core\Payment\Services\CustomerService as ServicesCustomerService;

class CustomerService
{

    protected $coreAuth;
    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
    }

    public function createCustomerByOrder($wcOrder)
    {
        $customerData = $this->extractCustomerDataByWcOrder($wcOrder);
        $phones = $this->createPhones($customerData);
        $address = $this->createAddress($customerData);
        $customer = new Customer();
        $customer->setCode($customerData['code']);
        $customer->setEmail($customerData['email']);
        $customer->setName($customerData['name']);
        $customer->setDocument($customerData['document']);
        $customer->setPhones($phones->convertToSdk());
        $customer->setAddress($address->convertToSdk());
        $customerId = $this->createCustomerOnPagarme($customer);
        $customer->setPagarmeId($customerId);
        $this->saveOnPlatform($customer);
        return $customer->getPagarmeId();
    }

    private function createCustomerOnPagarme($customer)
    {
        $customerProxy = new CustomerProxy($this->coreAuth);
        $data = $customerProxy->createCustomer($customer->convertToSdk());
        return $data->id;
    }

    public function createPhones($phoneData)
    {
        $phone = new Phones();
        $phone->setHomePhone($phoneData['home_phone']);
        $phone->setMobilePhone($phoneData['mobile_phone']);
        return $phone;
    }

    public function createAddress($addressData)
    {
        $address = new Address();
        $address->setCountry($addressData['country']);
        $address->setState($addressData['state']);
        $address->setCity($addressData['city']);
        $address->setNeighborhood($addressData['neighborhood']);
        $address->setZipCode($addressData['zipcode']);
        $address->setStreet($addressData['street']);
        $address->setNumber($addressData['number']);
        $address->setComplement($addressData['complement']);
        return $address;
    }
    
    public function extractCustomerDataByWcOrder($wcOrder)
    {
        $billingData = $wcOrder->get_data()['billing'];
        $document = $wcOrder->get_meta('_billing_cpf');
        if (empty($document)) {
            $document = $wcOrder->get_meta('_billing_cnpj');
        }
        return [
            'code' => $wcOrder->get_customer_id(),
            'email' => $billingData['email'],
            'name' => $billingData['first_name'] . ' ' . $billingData['last_name'],
            'document' => $document,
            'document_type' => empty($wcOrder->get_meta('_billing_cnpj')) ? 'cpf' : 'cnpj',
            'home_phone' => $billingData['phone'],
            'mobile_phone' => $wcOrder->get_meta('_billing_cellphone'),
            'street' => $billingData['street'],
            'neighborhood' => $billingData['neighborhood'],
            'number' => $billingData['number'],
            'country' => $billingData['country'],
            'city' => $billingData['city'],
            'state' => $billingData['state'],
            'complement' => $billingData['complement'],
            'zipcode' => $billingData['postcode']
        ];
    }

    public function saveOnPlatform($customer)
    {
        $this->saveOnLegacyMethod($customer);
        $this->save($customer);
    }

    /**
     * Remove this function after refactoring all classes
     *
     * @param Customer $customer
     * @return void
     */
    private function saveOnLegacyMethod($customer)
    {
        $customerRepository = new ServicesCustomerService();
        $customerRepository->saveCustomer($customer->convertToLegacy());
    }

    public function save($customer)
    {
        $wcCustomer = new WC_Customer($customer->getCode());
        $wcCustomer->add_meta_data('_pagarme_customer_id', $customer->getPagarmeId(), true);
        $wcCustomer->save();
    }
}