<?php

namespace App\Services;

use App\Models\Reseller;
use App\Models\ResellerWalletTransaction;
use Illuminate\Support\Facades\DB;

class ResellerWalletService
{
    /**
     * Credit funds to reseller's wallet.
     */
    public function credit(Reseller $reseller, float $amount, string $description, string $refType = null, int $refId = null): ResellerWalletTransaction
    {
        return DB::transaction(function () use ($reseller, $amount, $description, $refType, $refId) {
            $reseller->increment('balance', $amount);

            return ResellerWalletTransaction::create([
                'reseller_id' => $reseller->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);
        });
    }

    /**
     * Debit funds from reseller's wallet.
     */
    public function debit(Reseller $reseller, float $amount, string $description, string $refType = null, int $refId = null): ResellerWalletTransaction
    {
        return DB::transaction(function () use ($reseller, $amount, $description, $refType, $refId) {
            // Check balance
            if ($reseller->balance < $amount) {
                throw new \Exception("Insufficient wallet balance. Available: BDT {$reseller->balance}");
            }

            $reseller->decrement('balance', $amount);

            return ResellerWalletTransaction::create([
                'reseller_id' => $reseller->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);
        });
    }

    /**
     * Adjust reseller balance (can be credit or debit from Admin).
     */
    public function adjustBalance(Reseller $reseller, float $amount, string $type, string $description): ResellerWalletTransaction
    {
        if ($type === 'credit') {
            return $this->credit($reseller, $amount, $description, 'admin_adjustment');
        } elseif ($type === 'debit') {
            return $this->debit($reseller, $amount, $description, 'admin_adjustment');
        }

        throw new \InvalidArgumentException("Invalid transaction type: {$type}");
    }
}
