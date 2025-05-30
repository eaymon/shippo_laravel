<?php

namespace FarmTo\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object getRates(string $shipmentId, array $filters = [], bool $forceRefresh = false) Returns an object with properties such as 'rates' (array), 'currency' (string), and 'provider' (string).
 * @method static object getRate(string $rateId)
 * @method static array formatForSelect(array $rates, string $labelFormat = '{carrier} - {service} ({amount} {currency})')
 *         Expected keys in $labelFormat:
 *         - carrier (string): The name of the carrier.
 *         - service (string): The name of the service.
 *         - amount (float): The cost of the service.
 *         - currency (string): The currency code (e.g., 'USD').
 * @see \FarmTo\ShippoLaravel\ShippoTransaction Handles Shippo transactions, including retrieving rates, formatting data, and managing cache.
 * @method static object|null getCheapestRate(array $rates, array $filters = [])
 * @method static object|null getFastestRate(array $rates, array $filters = [])
 * @method static bool clearCache()
 * 
 * @see \FarmTo\ShippoLaravel\ShippoTransaction
 */
// This class serves as a Laravel facade for Shippo transactions, providing static methods to interact with Shippo's API.
class ShippoTransaction extends Facade
{
    /**
     * Get the registered name of the component.
     * This method binds the facade to the service container using the defined accessor.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'shippo-transaction';
    }
}