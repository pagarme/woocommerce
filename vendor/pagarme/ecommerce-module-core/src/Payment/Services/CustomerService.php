<?php

namespace Pagarme\Core\Payment\Services;

use Pagarme\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Factories\CustomerFactory;
use Pagarme\Core\Payment\Repositories\CustomerRepository;

class CustomerService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'CustomerService',
            true
        );
    }

    public function updateCustomerAtPagarme(PlatformCustomerInterface $platformCustomer)
    {
        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPlatformData($platformCustomer);

        if ($customer->getPagarmeId() !== null) {
            $this->logService->info("Update customer at Pagarme: [{$customer->getPagarmeId()}]");
            $this->logService->info("Customer request", $customer);
            $apiService = new ApiService();
            $apiService->updateCustomer($customer);
        }
    }

    public function deleteCustomerOnPlatform(PlatformCustomerInterface $platformCustomer)
    {
        $customerFactory = new CustomerFactory();
        $customer = $customerFactory->createFromPlatformData($platformCustomer);

        $customerRepository = new CustomerRepository();
        $customerRepository->deleteByCode($customer->getCode());
    }

    /**
     * @param Customer $customer
     */
    public function saveCustomer(Customer $customer)
    {

        if (empty($customer) || $customer->getCode() === null) {
            return;
        }

        $customerRepository = new CustomerRepository();

        if ($customerRepository->findByCode($customer->getCode()) !== null) {
            $customerRepository->deleteByCode($customer->getCode());
        }

        if (
            $customerRepository->findByPagarmeId($customer->getPagarmeId()) === null
        ) {
            $customerRepository->save($customer);
        }
    }
}