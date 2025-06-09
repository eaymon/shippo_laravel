<?php

namespace FarmToYou\ShippoLaravel;

use Shippo;
use Shippo_Shipment as Shipment;
use Shippo_Rate as Rate;
use Illuminate\Support\Facades\Cache;
use Exception;

class ShippoRates
{
    /**
     * @var string|null
     */
    protected $apiKey;

    /**
     * @var bool
     */
    protected $cacheEnabled;

    /**
     * @var int
     */
    protected $cacheTtl;

    /**
     * Constructor
     * 
     * @param string|null $apiKey Shippo API key
     * @param bool $cacheEnabled Whether to cache results
     * @param int $cacheTtl Cache TTL in minutes
     */
    public function __construct($apiKey = null, $cacheEnabled = true, $cacheTtl = 1440)
    {
        $this->apiKey = $apiKey;
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheTtl = $cacheTtl;

        if ($this->apiKey) {
            Shippo::setApiKey($this->apiKey);
        }
    }

    /**
     * Get rates for a shipment
     *
     * @param string $shipmentId The shipment ID
     * @param array $filters Optional filters for rates (carrier, service, etc.)
     * @param bool $forceRefresh Force refresh the cached rates
     * @return object The rates object
     * @throws Exception
     */
    public function getRates($shipmentId, array $filters = [], bool $forceRefresh = false)
    {
        $cacheKey = 'shippo_rates_' . $shipmentId . '_' . md5(json_encode($filters));

        if ($this->cacheEnabled && !$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $shipment = Shipment::retrieve($shipmentId);
            
            if (!isset($shipment->rates) || empty($shipment->rates)) {
                throw new Exception('No rates available for this shipment');
            }

            $rates = $shipment->rates;

            // Apply filters if provided
            if (!empty($filters)) {
                $rates = array_filter($rates, function($rate) use ($filters) {
                    foreach ($filters as $key => $value) {
                        if (!isset($rate->$key) || $rate->$key != $value) {
                            return false;
                        }
                    }
                    return true;
                });
            }

            // Sort by price
            usort($rates, function($a, $b) {
                return $a->amount - $b->amount;
            });

            $result = (object) ['rates' => $rates];

            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $result, $this->cacheTtl * 60);
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception('Error retrieving rates: ' . $e->getMessage());
        }
    }

    /**
     * Get specific rate by ID
     * 
     * @param string $rateId
     * @return object The rate object
     * @throws Exception
     */
    public function getRate($rateId)
    {
        try {
            return Rate::retrieve($rateId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving rate: ' . $e->getMessage());
        }
    }

    /**
     * Format rates for a select dropdown
     * 
     * @param array $rates The rates to format
     * @param string $labelFormat Format string for labels. Use {carrier}, {service}, {amount}, {currency}
     * @return array The formatted rates
     */
    public function formatForSelect($rates, $labelFormat = '{carrier} - {service} ({amount} {currency})')
    {
        $formattedRates = [];
        
        foreach ($rates as $rate) {
            $label = str_replace(
                ['{carrier}', '{service}', '{amount}', '{currency}'],
                [$rate->provider, $rate->servicelevel->name, $rate->amount, $rate->currency],
                $labelFormat
            );
            
            $formattedRates[$rate->object_id] = $label;
        }
        
        return $formattedRates;
    }

    /**
     * Group rates by a specific field
     * 
     * @param array $rates The rates to group
     * @param string $groupBy Field to group by (e.g., 'provider', 'servicelevel')
     * @return array Grouped rates
     */
    public function groupRates($rates, $groupBy = 'provider')
    {
        $grouped = [];
        
        foreach ($rates as $rate) {
            $key = $groupBy === 'servicelevel' ? $rate->servicelevel->name : $rate->$groupBy;
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            
            $grouped[$key][] = $rate;
        }
        
        return $grouped;
    }

    /**
     * Get the cheapest rate
     * 
     * @param array $rates The rates to search
     * @param array $filters Optional filters
     * @return object|null The cheapest rate or null if none found
     */
    public function getCheapestRate($rates, array $filters = [])
    {
        // Apply filters if provided
        if (!empty($filters)) {
            $rates = array_filter($rates, function($rate) use ($filters) {
                foreach ($filters as $key => $value) {
                    if (!isset($rate->$key) || $rate->$key != $value) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        if (empty($rates)) {
            return null;
        }
        
        return array_reduce($rates, function($carry, $rate) {
            if ($carry === null || $rate->amount < $carry->amount) {
                return $rate;
            }
            return $carry;
        });
    }

    /**
     * Get the fastest rate
     * 
     * @param array $rates The rates to search
     * @param array $filters Optional filters
     * @return object|null The fastest rate or null if none found
     */
    public function getFastestRate($rates, array $filters = [])
    {
        // Apply filters if provided
        if (!empty($filters)) {
            $rates = array_filter($rates, function($rate) use ($filters) {
                foreach ($filters as $key => $value) {
                    if (!isset($rate->$key) || $rate->$key != $value) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        if (empty($rates)) {
            return null;
        }
        
        return array_reduce($rates, function($carry, $rate) {
            // Compare estimated days for delivery
            if ($carry === null || $rate->estimated_days < $carry->estimated_days) {
                return $rate;
            }
            return $carry;
        });
    }
    /**
     * Get preview shipping rates without creating a shipment
     *
     * @param array $fromAddress The sender's address
     * @param array $toAddress The recipient's address
     * @param array $parcel The parcel details
     * @param array $options Additional options for the shipment
     * @return object The shipping rates object
     * @throws Exception
     */
    public function getPreviewShippingRates(
        array $fromAddress,
        array $toAddress,
        array $parcel,
        array $options = []
    ) {
        $shipmentRates = Shipment::get_shipping_rates([
            $fromAddress,
            $toAddress,
            $parcel,
            $options
        ]);
        if (isset($shipmentRates->object_state) && $shipmentRates->object_state === 'error') {
            throw new Exception('Error retrieving shipping rates: ' . $shipmentRates->messages[0]->text);
        }
        return $shipmentRates;
    }
    /**
     * Clear the rates cache
     *
     * @return bool
     */
    public function clearCache()
    {
        $cleared = Cache::forget('shippo_rates_*');
        return $cleared;
    }
}