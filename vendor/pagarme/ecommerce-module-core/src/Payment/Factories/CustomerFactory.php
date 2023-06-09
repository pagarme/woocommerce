<?php

namespace Pagarme\Core\Payment\Factories;

use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformCustomerInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\ValueObjects\CustomerPhones;
use Pagarme\Core\Payment\ValueObjects\CustomerType;
use Pagarme\Core\Payment\ValueObjects\Phone;

class CustomerFactory implements FactoryInterface
{
    /**
     *
     * @param  \stdClass $postData
     * @return Customer
     */
    public function createFromPostData($postData)
    {
        $postData = json_decode(json_encode($postData));

        $customer = new Customer();

        $customer->setPagarmeId(
            new CustomerId($postData->id)
        );

        if (!empty($postData->code)) {
            $customer->setCode($postData->code);
        }

        return $customer;
    }

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $customer = new Customer;

        $customer->setName($data->name);
        $customer->setEmail($data->email);
        $customer->setDocument($data->document);
        $customer->setType(CustomerType::individual());

        $homePhone = new Phone($data->homePhone);
        $mobilePhone = new Phone($data->mobilePhone);

        $customer->setPhones(
            CustomerPhones::create([$homePhone, $mobilePhone])
        );

        $addressFactory = new AddressFactory();
        $customer->setAddress($addressFactory->createFromJson($json));

        return $customer;
    }

    /**
     *
     * @param  array $dbData
     * @return Customer
     */
    public function createFromDbData($dbData)
    {
        $customer = new Customer;

        $customer->setCode($dbData['code']);
        $customer->setPagarmeId(new CustomerId($dbData['pagarme_id']));

        return $customer;
    }

    public function createFromPlatformData(PlatformCustomerInterface $platformData)
    {
        $customer = new Customer;

        if ($platformData->getPagarmeId()) {
            $customer->setPagarmeId(
                new CustomerId($platformData->getPagarmeId())
            );
        }
        $customer->setCode($platformData->getCode());
        $customer->setName($platformData->getName());
        $customer->setEmail($platformData->getEmail());
        $customer->setDocument($platformData->getDocument());
        $customer->setType($platformData->getType());
        $customer->setPhones($platformData->getPhones());
        /** @todo set address */

        return $customer;
    }
}
