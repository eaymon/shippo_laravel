<?php

namespace FarmTo\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object getCarriers(array|null $carriers = null, bool $forceRefresh = false)
 * @method static object|null getServiceLevel(string $serviceCode)
 * @method static bool clearCache()
 * @method static array getFormattedForSelect(string $groupBy = 'carrier')
 * 
 * @see \FarmTo\ShippoLaravel\ShippoCarrier
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