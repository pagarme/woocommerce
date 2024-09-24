<?php

namespace Pagarme\Core\Payment\Factories;

use Pagarme\Core\Payment\Aggregates\Address;

final class AddressFactory
{
    public function createFromJson($json)
    {
        $data = json_decode($json);

        $multipleLineStreet = !empty($data->number) && !empty($data->neighborhood);

        $address = new Address();

        $address->setStreet($data->street, $multipleLineStreet);
        $address->setComplement($data->complement);
        $address->setCity($data->city);
        $address->setState($data->state);
        $address->setZipCode($data->zipCode);
        $address->setCountry('BR');

        if (!empty($data->number)) {
            $address->setNumber($data->number);
        }

        if (!empty($data->neighborhood)) {
            $address->setNeighborhood($data->neighborhood);
        }

        return $address;
    }
}
