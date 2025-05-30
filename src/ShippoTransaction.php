<?php

class ShippoTransaction
{
    public function purchaseLabel($rateId)
    {
        try {
            if (empty($rateId)) {
                throw new Exception('Rate ID cannot be empty');
            }

            $transaction = Shippo_Transaction::create([
                'rate' => $rateId,
                'label_file_type' => 'PDF',
                'async' => false,
            ]);

            return $transaction;
        } catch (Exception $e) {
            throw new Exception('Error purchasing label: ' . $e->getMessage());
        }
    }
}