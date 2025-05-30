<?php

namespace FarmTo\ShippoLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object purchaseLabel(string $rateId)
 * @method static object getTransaction(string $transactionId)
 * @method static array getAllTransactions(array $filters = [])
 * @method static bool cancelTransaction(string $transactionId)
 *
 * @see \FarmTo\ShippoLaravel\ShippoTransaction
 */
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