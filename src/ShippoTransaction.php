<?php

use Shippo_Transaction as Transaction;
class ShippoTransaction
{
    public function purchaseLabel($rateId)
    {
        try {
            if (empty($rateId)) {
                throw new Exception('Rate ID cannot be empty');
            }

            $transaction = Transaction::create([
                'rate' => $rateId,
                'label_file_type' => 'PDF',
                'async' => false,
            ]);

            return $transaction;
        } catch (Exception $e) {
            throw new Exception('Error purchasing label: ' . $e->getMessage());
        }
    }
    public function getTransaction($transactionId)
    {
        try {
            if (empty($transactionId)) {
                throw new Exception('Transaction ID cannot be empty');
            }

            return Transaction::retrieve($transactionId);
        } catch (Exception $e) {
            throw new Exception('Error retrieving transaction: ' . $e->getMessage());
        }
    }
    public function getAllTransactions(array $filters = [])
    {
        try {
            $transactions = Transaction::all($filters);
            return $transactions;
        } catch (Exception $e) {
            throw new Exception('Error retrieving transactions: ' . $e->getMessage());
        }
    }
    public function cancelTransaction($transactionId)
    {
        try {
            if (empty($transactionId)) {
                throw new Exception('Transaction ID cannot be empty');
            }

            $transaction = Transaction::retrieve($transactionId);
            if ($transaction->status !== 'CANCELLED') {
                $transaction->cancel();
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Error cancelling transaction: ' . $e->getMessage());
        }
    }
}