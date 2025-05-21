
# Shippo Laravel Integration

A Laravel package that provides seamless integration with Shippo's shipping API, allowing you to easily work with carriers, shipping rates, and service levels in your Laravel application.

---

## Features

- Simple integration with Shippo API  
- Support for multiple carriers (USPS, DHL, etc.)  
- Intelligent caching for API responses  
- Formatting utilities for UI display  
- Easy configuration  

---

## Installation

You can install the package via Composer:

```bash
composer require farmto/shippo-laravel
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=config
```

This will create a `shippo.php` configuration file in your `config` directory. Update it with your Shippo API key:

```php
<?php
// config/shippo.php
return [
    'api_key' => env('SHIPPO_API_KEY', ''),
    'cache_enabled' => true,
    'cache_ttl' => 1440, // minutes (24 hours)
    'default_carriers' => [
        'usps',
        'dhl',
        // Add other carriers you want to use by default
    ],
];
```

Make sure to add your Shippo API key to your `.env` file:

```env
SHIPPO_API_KEY=your-api-key-here
```

---

## Usage

### Basic Usage

```php
<?php
// Get all carriers with their service levels
$carriers = ShippoCarrier::getCarriers();

// Get a specific service level by code
$service = ShippoCarrier::getServiceLevel('usps_priority');

// Format carriers and service levels for a select dropdown
$options = ShippoCarrier::getFormattedForSelect('carrier');
```

### Using with Forms

**In your controller:**

```php
<?php
public function create()
{
    $shippingOptions = ShippoCarrier::getFormattedForSelect('type');
    return view('checkout.shipping', compact('shippingOptions'));
}
```

**In your Blade template:**

```blade
<select name="shipping_method">
    @foreach($shippingOptions as $group => $options)
        <optgroup label="{{ $group }}">
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </optgroup>
    @endforeach
</select>
```

---

## Clearing Cache

```php
<?php
// Clear the carrier cache
ShippoCarrier::clearCache();
```

---

## Available Methods

| Method                                   | Description                                         |
|------------------------------------------|-----------------------------------------------------|
| `getCarriers($carriers = null, $forceRefresh = false)` | Get all carriers with their service levels         |
| `getServiceLevel($serviceCode)`          | Get a specific service level by code               |
| `clearCache()`                           | Clear the carrier cache                            |
| `getFormattedForSelect($groupBy = 'carrier')` | Format carriers for select dropdowns         |

---

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## License

The MIT License (MIT). Please see the [LICENSE](LICENSE) file for more information.
