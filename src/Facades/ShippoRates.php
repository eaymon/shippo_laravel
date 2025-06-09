<?php

namespace FarmToYou\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object getRates(string $shipmentId, array $filters = [], bool $forceRefresh = false)
 * @method static object getRate(string $rateId)
 * @method static array formatForSelect(array $rates, string $labelFormat = '{carrier} - {service} ({amount} {currency})')
 * @method static array groupRates(array $rates, string $groupBy = 'provider')
 * @method static object|null getCheapestRate(array $rates, array $filters = [])
 * @method static object|null getFastestRate(array $rates, array $filters = [])
 * @method static bool clearCache()
 * 
 * @see \FarmToYou\ShippoLaravel\ShippoRates
 */
class ShippoRates extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shippo-rates';
    }
}