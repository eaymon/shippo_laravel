<?php

namespace FarmTo\ShippoLaravel;

use Shippo;
use Shippo_Shipment as Shipment;
use Shippo_Transaction as Transaction;
use Exception;

class ShippoShipment
{
    protected $apiKey;

    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;

        if ($this->apiKey) {
            Shippo::setApiKey($this->apiKey);
        }
    }

    /**
     * Create a shipment in Shippo
     *
     * @param array $fromAddress The sender's address
     * @param array $toAddress The recipient's address
     * @param array $parcel The parcel details
     * @param array $options Additional shipment options
     * @return object The shipment object
     * @throws Exception
     */
    public function createShipment(array $fromAddress, array $toAddress, array $parcel, array $options = [])
    {
        try {
            $shipmentParams = [
                'address_from' => $fromAddress,
                'address_to' => $toAddress,
                'parcels' => [$parcel],
                'async' => false
            ];

            // Merge additional options
            if (!empty($options)) {
                $shipmentParams = array_merge($shipmentParams, $options);
            }

            return Shipment::create($shipmentParams);
        } catch (Exception $e) {
            throw new Exception('Error creating shipment: ' . $e->getMessage());
        }
    }

    /**
     * Purchase a label for a rate
     * 
     * @param string $rateId The rate ID to purchase
     * @return object The transaction object
     * @throws Exception
     */
    public function purchaseLabel($rateId)
    {
        try {
            return Transaction::create([
                'rate' => $rateId,
                'label_file_type' => 'PDF',
                'async' => false
            ]);
        } catch (Exception $e) {
            throw new Exception('Error purchasing label: ' . $e->getMessage());
        }
    }
}