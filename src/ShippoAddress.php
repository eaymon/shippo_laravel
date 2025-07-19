<?php

namespace FarmToYou\ShippoLaravel;

use Shippo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;
use Exception;

class ShippoAddress
{
    /**
     * @var string Base API URL
     */
    protected const API_BASE = 'https://api.goshippo.com/v2';
    
    /**
     * @var string Shippo API key
     */
    protected $apiKey;
    
    /**
     * @var bool Whether to cache results
     */
    protected $cacheEnabled;
    
    /**
     * @var int Cache TTL in seconds
     */
    protected $cacheTtl;
    
    /**
     * Constructor
     * 
     * @param string|null $apiKey Shippo API key
     * @param bool $cacheEnabled Whether to cache results
     * @param int $cacheTtl Cache TTL in seconds
     */
    public function __construct($apiKey, $cacheEnabled = false, $cacheTtl = 3600)
    {
        $this->apiKey = $apiKey;
        $this->cacheEnabled = $cacheEnabled;
        $this->cacheTtl = $cacheTtl;
        
        if ($this->apiKey) {
            Shippo::setApiKey($this->apiKey);
        }
    }
    
    /**
     * Create an address
     * 
     * @param array $address Address data
     * @return array
     * @throws Exception
     */
    public function createAddress(array $address): array
    {
        $endpoint = '/addresses';
        $payload = [
            'name' => $address['name'] ?? null,
            'organization' => $address['organization'] ?? null,
            'street1' => $address['street1'] ?? null,
            'street2' => $address['street2'] ?? null,
            'city' => $address['city'] ?? null,
            'state' => $address['state'] ?? null,
            'zip' => $address['zip'] ?? null,
            'country' => $address['country'] ?? null,
        ];
        
        $response = $this->makeRequest('post', $endpoint, $payload);
        $addressData = $response->json();
        
        $this->cacheResponse('address_' . ($addressData['object_id'] ?? md5(json_encode($address))), $addressData);
        
        return $addressData;
    }
    
    /**
     * Validate an address
     * 
     * @param array $address Address data
     * @return array
     * @throws Exception
     */
    public function validateAddress(array $address): array
    {
        $endpoint = '/addresses/validate';
        $params = [
            'name' => $address['name'] ?? null,
            'organization' => $address['organization'] ?? null,
            'address_line_1' => $address['street1'] ?? $address['address_line_1'] ?? null,
            'address_line_2' => $address['street2'] ?? $address['address_line_2'] ?? null,
            'city_locality' => $address['city'] ?? $address['city_locality'] ?? null,
            'state_province' => $address['state'] ?? $address['state_province'] ?? null,
            'postal_code' => $address['zip'] ?? $address['postal_code'] ?? null,
            'country_code' => $address['country'] ?? $address['country_code'] ?? null,
        ];
        
        $response = $this->makeRequest('get', $endpoint, $params);
        return $response->json();
    }
    
    /**
     * Get an address by ID
     * 
     * @param string $addressId Address ID
     * @return array
     * @throws Exception
     */
    public function getAddress(string $addressId): array
    {
        $cacheKey = 'address_' . $addressId;
        
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $endpoint = "/addresses/{$addressId}";
        $response = $this->makeRequest('get', $endpoint);
        $addressData = $response->json();
        
        $this->cacheResponse($cacheKey, $addressData);
        
        return $addressData;
    }
    
    /**
     * Make an HTTP request to the Shippo API
     * 
     * @param string $method HTTP method (get, post, etc.)
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @return Response
     * @throws Exception
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        try {
            $request = Http::withToken($this->apiKey, 'ShippoToken')
                ->withHeaders(['Content-Type' => 'application/json']);
            
            $url = self::API_BASE . $endpoint;
            
            $response = $method === 'get'
                ? $request->get($url, $data)
                : $request->$method($url, $data);
            
            if ($response->failed()) {
                $error = $response->json()['messages'][0]['text'] ?? $response->status();
                throw new Exception("Shippo API request failed: {$error}");
            }
            
            return $response;
        } catch (Exception $e) {
            Log::error("Shippo API error: {$e->getMessage()}", [
                'method' => $method,
                'endpoint' => $endpoint,
                'data' => $data
            ]);
            throw new Exception($e->getMessage());
        }
    }
    
    /**
     * Cache a response if caching is enabled
     * 
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @return void
     */
    protected function cacheResponse(string $key, $data): void
    {
        if ($this->cacheEnabled) {
            $key = "shippo_{$key}";
            Cache::put($key, $data, $this->cacheTtl);
        }
    }
}