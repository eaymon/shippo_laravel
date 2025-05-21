# FarmTo Shippo Carrier for Laravel

A Laravel package that integrates the [Shippo API](https://goshippo.com/) into your Laravel application for easy shipping label generation, tracking, and carrier management.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farmto/shippo-laravel.svg?style=flat-square)](https://packagist.org/packages/farmto/shippo-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/farmto/shippo-laravel.svg?style=flat-square)](https://packagist.org/packages/farmto/shippo-laravel)

## Installation

You can install the package via composer:

```bash
composer require farmto/shippo-laravel
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="FarmTo\ShippoLaravel\ShippoCarrierServiceProvider"
```

Then update your `.env` file with your Shippo API credentials:

```
SHIPPO_API_KEY=your_api_key_here
SHIPPO_API_VERSION=2018-02-08
```

## Usage

```php
use FarmTo\ShippoLaravel\Facades\ShippoCarrier;

// Create a shipping label
$shipment = ShippoCarrier::createShipment([
    'address_from' => [
        'name' => 'John Doe',
        'street1' => '123 Main St',
        'city' => 'San Francisco',
        'state' => 'CA',
        'zip' => '94105',
        'country' => 'US',
    ],
    'address_to' => [
        'name' => 'Jane Doe',
        'street1' => '456 Oak St',
        'city' => 'New York',
        'state' => 'NY',
        'zip' => '10001',
        'country' => 'US',
    ],
    'parcels' => [
        [
            'length' => 10,
            'width' => 8,
            'height' => 4,
            'distance_unit' => 'in',
            'weight' => 2,
            'mass_unit' => 'lb',
        ],
    ],
]);

// Get available shipping rates
$rates = ShippoCarrier::getShipmentRates($shipment['object_id']);

// Purchase a label
$transaction = ShippoCarrier::purchaseShippingLabel($rates[0]['object_id']);
```

## Troubleshooting

If you encounter the error "Class 'FarmTo\ShippoLaravels\ShippoCarriersServiceProvider' not found", there might be a namespace discrepancy. Make sure your service provider uses the correct namespace as defined in your composer.json file.

### For service provider issues:

Check that your [composer.json](composer.json) has the correct PSR-4 autoloading configuration:

```json
"autoload": {
    "psr-4": {
        "FarmTo\\ShippoLaravel\\": "src/"
    }
}
```

Then run:

```bash
composer dump-autoload
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Christian Martin Cabucos](https://github.com/christianmartincabucos)