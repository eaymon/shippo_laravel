<?php

namespace FarmToYou\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object createAddress(array $address)
 * @method static object validateAddress(array $address)
 * @method static object getAddress(string $addressId)
 * 
 * @see \FarmToYou\ShippoLaravel\ShippoAddress
 */
class ShippoAddress extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shippo-address';
    }
}