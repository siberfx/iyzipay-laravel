<?php

namespace Iyzico\IyzipayLaravel\StorableClasses;

use Iyzico\IyzipayLaravel\Exceptions\Fields\AddressFieldsException;

class Address extends StorableClass
{

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $country;

    /**
     * @var string
     */
    public $address;

    protected function getFieldExceptionClass(): string
    {
        return AddressFieldsException::class;
    }
}
