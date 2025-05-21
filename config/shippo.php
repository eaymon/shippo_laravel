<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shippo API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key for the Shippo API. You can find this in your Shippo
    | dashboard under API.
    |
    */
    'api_key' => env('SHIPPO_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configure caching settings for carrier and service level data.
    |
    */
    'cache_enabled' => env('SHIPPO_CACHE_ENABLED', true),
    'cache_ttl' => env('SHIPPO_CACHE_TTL', 1440), // in minutes (24 hours)

    /*
    |--------------------------------------------------------------------------
    | Service Level Display Format
    |--------------------------------------------------------------------------
    |
    | Define how service levels should be formatted for display
    |
    */
    'display_format' => [
        'show_estimated_days' => true,
        'show_carrier_logo' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Carriers
    |--------------------------------------------------------------------------
    |
    | List of carriers to load by default when using getCarriers() without parameters
    |
    */
    'default_carriers' => [
        'usps',
        'dhl',
    ],
];