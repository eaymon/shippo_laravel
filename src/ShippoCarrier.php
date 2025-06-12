<?php

namespace FarmToYou\ShippoLaravel;

use Illuminate\Support\Facades\Cache;
use Shippo;
use Shippo_CarrierAccount;

class ShippoCarrier
{
    protected $apiKey;
    protected $cacheEnabled;
    protected $cacheTtl;
    
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
     * Get carrier accounts without service levels
     *
     * @param array|null $carriers Array of carrier codes to filter by
     * @param bool $forceRefresh Force refresh the cache
     * @return object Collection of carriers
     */
    public function getCarriersOnly($carriers = null, $forceRefresh = false)
    {
        $cacheKey = 'shippo_carriers_only_' . md5(json_encode($carriers));
        
        if ($this->cacheEnabled && !$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $checkCarriers = $carriers ?? config('shippo.default_carriers', []);
        $carriers = $this->isCarrierArray($checkCarriers);
        
        try {
            $allResults = [];
            
            foreach ($carriers as $carrier) {
                $response = Shippo_CarrierAccount::all([
                    'carrier' => $carrier,
                    'service_levels' => false
                ]);
                
                if (isset($response->results) && is_array($response->results)) {
                    foreach ($response->results as $result) {
                        $allResults[] = $result;
                    }
                }
            }
            
            $result = (object) ['results' => $allResults];
            
            if ($this->cacheEnabled) {
                Cache::put($cacheKey, $result, $this->cacheTtl * 60);
            }
            
            return $result;
        } catch (\Exception $e) {
            return (object) ['error' => 'Unable to fetch carriers: ' . $e->getMessage()];
        }
    }
    /**
     * Get all carriers with their service levels
     *
     * @param array|null $carriers Array of carrier codes to filter by
     * @param bool $forceRefresh Force refresh the cache
     * @return object Collection of carriers with service levels
     */
    public function getCarriersServiceLevels($carriers = null, $forceRefresh = false)
    {
        $cacheKey = 'shippo_carriers_' . md5(json_encode($carriers));
        
        if ($this->cacheEnabled && !$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $checkCarriers = $carriers ?? config('shippo.default_carriers', []);
        $carriers = $this->isCarrierArray($checkCarriers);
        $serviceLevels = $this->fetchCarrierServiceLevels($carriers);
        
        if ($this->cacheEnabled) {
            Cache::put($cacheKey, $serviceLevels, $this->cacheTtl * 60);
        }
        
        return $serviceLevels;
    }
    protected function isCarrierArray($carriers)
    {
        if (gettype($carriers) == 'string') {
            $carriers = [$carriers];
        } elseif (gettype($carriers) == 'array') {
            $carriers = array_values(array_unique($carriers));
        } else {
            $carriers = config('shippo.default_carriers', []);
        }
        return $carriers;
    }
    /**
     * Fetch carrier accounts with their service levels
     * 
     * @param array $carriers Array of carrier codes to filter by
     * @return object
     */
    protected function fetchCarrierServiceLevels($carriers)
    {
        // Get carrier accounts
        try {
            
            $allResults = array();

            foreach ($carriers as $carrier) {
                $response = Shippo_CarrierAccount::all([
                    'carrier' => $carrier,
                    'service_levels' => true
                ]);
                
                if (isset($response->results) && is_array($response->results)) {
                    foreach ($response->results as $result) {
                        $allResults[] = $result;
                    }
                }
            }
            
            // Process and format the returned data
            $formattedResults = [];
            
            foreach ($allResults as $carrierAccount) {
                $carrierId = $carrierAccount->carrier;
                $carrierName = $this->formatCarrierName($carrierId);
                
                $serviceLevels = [];
                if (isset($carrierAccount->service_levels)) {
                    foreach ($carrierAccount->service_levels as $serviceLevel) {
                        $serviceLevels[] = (object) [
                            'service_code' => $serviceLevel->token,
                            'name' => $serviceLevel->name,
                            'carrier' => $carrierId,
                            'token' => $serviceLevel->token,
                            'description' => $this->getServiceLevelDescription($carrierId, $serviceLevel->token),
                            'is_domestic' => !$this->isInternationalService($serviceLevel->name),
                            'estimated_days' => $serviceLevel->estimated_days ?? null,
                        ];
                    }
                }
                
                $formattedResults[] = (object) [
                    'carrier' => $carrierId,
                    'carrier_name' => $carrierName,
                    'object_id' => $carrierAccount->object_id,
                    'service_levels' => $serviceLevels,
                    'logo_url' => $this->getCarrierLogoUrl($carrierId),
                ];
            }
            
            return (object) ['results' => $formattedResults];
        } catch (\Exception $e) {
            // Handle exceptions (e.g., API errors)
            return (object) ['error' => 'Unable to fetch carrier service levels: ' . $e->getMessage()];
        }
    }
    
    /**
     * Format carrier name for display
     * 
     * @param string $carrierId
     * @return string
     */
    protected function formatCarrierName($carrierId)
    {
        $names = [
            'usps' => 'USPS',
            'dhl' => 'DHL Express',
        ];
        
        return $names[$carrierId] ?? ucfirst($carrierId);
    }
    
    /**
     * Check if a service is international based on name
     * 
     * @param string $serviceName
     * @return bool
     */
    protected function isInternationalService($serviceName)
    {
        $keywords = ['international', 'world', 'global', 'overseas'];
        
        foreach ($keywords as $keyword) {
            if (stripos($serviceName, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get description for a service level
     * 
     * @param string $carrier
     * @param string $serviceToken
     * @return string
     */
    protected function getServiceLevelDescription($carrier, $serviceToken)
    {
        // This could be expanded with a database of descriptions
        $descriptions = [
            'usps' => [
                'usps_priority' => 'On-Time Delivery Guarantee, Tracking, Delivery Confirmation, Coverage up to $100',
                'usps_express' => 'Fastest domestic service, money-back guarantee, tracking and insurance included',
            ],
            'dhl' => [
                'dhl_express' => 'Time-definite international shipping with delivery confirmation and full tracking',
            ],
        ];
        
        return $descriptions[$carrier][$serviceToken] ?? 'Standard shipping service';
    }
    
    /**
     * Get carrier logo URL
     * 
     * @param string $carrierId
     * @return string|null
     */
    protected function getCarrierLogoUrl($carrierId)
    {
        $logos = [
            'usps' => [
                "https://shippo-static-v2.s3.amazonaws.com/providers/75/USPS.png",
                "https://shippo-static-v2.s3.amazonaws.com/providers/200/USPS.png"
            ],
            'dhl' => [
                "https://shippo-static-v2.s3.amazonaws.com/providers/75/DHL.png",
                "https://shippo-static-v2.s3.amazonaws.com/providers/200/DHL.png"
            ],
        ];
        
        return $logos[$carrierId] ?? null;
    }
    
    /**
     * Get service level by code
     * 
     * @param string $serviceCode
     * @return object|null
     */
    public function getServiceLevel($serviceCode)
    {
        $allCarriers = $this->getCarriers();
        
        foreach ($allCarriers->results as $carrier) {
            foreach ($carrier->service_levels as $service) {
                if ($service->service_code === $serviceCode) {
                    return $service;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Clear carrier cache
     * 
     * @return bool
     */
    public function clearCache()
    {
        $cacheKeys = [
            'shippo_carriers_' . md5(json_encode(null)),
            'shippo_carriers_' . md5(json_encode(config('shippo.default_carriers', []))),
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        
        return true;
    }
    
    /**
     * Format carriers and services for select dropdowns
     * 
     * @param string $groupBy 'carrier' or 'type'
     * @return array
     */
    public function getFormattedForSelect($groupBy = 'carrier')
    {
        $carriers = $this->getCarriers();
        $options = [];
        
        if ($groupBy === 'carrier') {
            foreach ($carriers->results as $carrier) {
                $carrierOptions = [];
                
                foreach ($carrier->service_levels as $service) {
                    $carrierOptions[$service->service_code] = $service->name;
                }
                
                $options[$carrier->carrier_name] = $carrierOptions;
            }
        } elseif ($groupBy === 'type') {
            $domestic = [];
            $international = [];
            
            foreach ($carriers->results as $carrier) {
                foreach ($carrier->service_levels as $service) {
                    $label = "{$carrier->carrier_name} - {$service->name}";
                    
                    if ($service->is_domestic) {
                        $domestic[$service->service_code] = $label;
                    } else {
                        $international[$service->service_code] = $label;
                    }
                }
            }
            
            $options['Domestic'] = $domestic;
            $options['International'] = $international;
        }
        
        return $options;
    }
}