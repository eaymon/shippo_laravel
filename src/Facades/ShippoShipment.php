<?php

namespace FarmToYou\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object createShipment(array $fromAddress, array $toAddress, array $parcel, array $options = [])
 * @method static object purchaseLabel(string $rateId)
 * @method static object getShipment(string $shipmentId)
 * 
 * @see \FarmToYou\ShippoLaravel\ShippoShipment
 */
class ShippoShipment extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shippo-shipment';
    }
}