<?php

namespace FarmTo\ShippoLaravel;

use Illuminate\Support\ServiceProvider;

class ShippoCarrierServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('shippo-laravel', function ($app) {
            return new ShippoCarrier(
                config('shippo.api_key'),
                config('shippo.cache_enabled', true),
                config('shippo.cache_ttl', 1440)
            );
        });

        $this->app->bind('shippo-shipment', function ($app) {
            return new ShippoShipment(
                config('shippo.api_key')
            );
        });

        $this->app->bind('shippo-rates', function ($app) {
            return new ShippoRates(
                config('shippo.api_key'),
                config('shippo.cache_enabled', true),
                config('shippo.cache_ttl', 1440)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/shippo.php' => config_path('shippo.php'),
        ], 'config');
    }
}