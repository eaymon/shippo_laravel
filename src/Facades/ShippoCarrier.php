<?php

namespace FarmToYou\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object getCarriersOnly($carriers = null, $forceRefresh = false)
 * @method static object getCarriersServiceLevels(array|null $carriers = null, bool $forceRefresh = false)
 * @method static object|null getServiceLevel(string $serviceCode)
 * @method static bool clearCache()
 * @method static array getFormattedForSelect(string $groupBy = 'carrier')
 * @method static array getRates(array $shipment)
 * @method static object validateAddress(array $address)
 * @method static object createLabel(array $transaction)
 * @method static object trackShipment(string $trackingNumber, string|null $carrier = null)
 * 
 * @see \FarmToYou\ShippoLaravel\ShippoCarrier
 */
class ShippoCarrier extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shippo-laravel';
    }
}