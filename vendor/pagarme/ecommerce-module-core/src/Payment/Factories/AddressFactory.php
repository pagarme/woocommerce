<?php

namespace Pagarme\Core\Payment\Factories;

use Pagarme\Core\Payment\Aggregates\Address;

final class AddressFactory
{
    public function createFromJson($json)
    {
        $data = json_decode($json);

        $address = new Address();

        $address->setStreet($data->street);
        $address->setNumber($data->number);
        $address->setNeighborhood($data->neighborhood);
        $address->setComplement($data->complement);
        $address->setCity($data->city);
        $address->setState($data->state);
        $address->setZipCode($data->zipCode);
        $address->setCountry('BR');

        return $address;
    }
}