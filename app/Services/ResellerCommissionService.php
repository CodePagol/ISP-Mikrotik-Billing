<?php

namespace App\Services;

use App\Models\CustomersInfo;
use App\Models\ResellerCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResellerCommissionService
{
    protected $walletService;

    public function __construct(ResellerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Process commission generation for a reseller customer package sale.
     */
    public function processCommission(CustomersInfo $customer, float $saleAmount): ?ResellerCommission
    {
        // Check if customer belongs to a reseller
        if (!$customer->reseller_id) {
            return null;
        }

        $reseller = $customer->reseller;

        // Check if reseller exists and is active
        if (!$reseller || !$reseller->isActive()) {
            Log::info("Reseller commission skipped: Reseller is inactive or not found for customer {$customer->customer_unique_id}");
            return null;
        }

        // Skip commission if it was paid upfront (discounted) during voucher generation
        $recentVoucher = \App\Models\Voucher::where('used_by_customer_id', $customer->id)
            ->where('status', 'used')
            ->where('used_at', '>=', now()->subSeconds(30))
            ->first();

        if ($recentVoucher && $recentVoucher->commission_paid_upfront) {
            Log::info("Reseller commission skipped: commission was paid upfront via voucher discount for voucher {$recentVoucher->code}");
            return null;
        }

        return DB::transaction(function () use ($customer, $reseller, $saleAmount) {
            // Calculate commission based on package price, or fallback to the saleAmount
            $package = $customer->package;
            $packagePrice = $package ? (float)$package->price : 0.00;
            $baseAmount = $packagePrice > 0 ? $packagePrice : $saleAmount;

            if ($baseAmount <= 0) {
                Log::warning("Reseller commission skipped: Base amount is zero for customer {$customer->customer_unique_id}");
                return null;
            }

            $percentage = (float) $reseller->commission_percentage;
            $commissionAmount = round($baseAmount * ($percentage / 100), 2);

            // 1. Create commission log record
            $commission = ResellerCommission::create([
                'reseller_id' => $reseller->id,
                'customer_id' => $customer->id,
                'package_id' => $customer->package_id,
                'amount' => $commissionAmount,
                'commission_percentage' => $percentage,
            ]);

            // 2. Credit reseller wallet
            $description = "Commission of {$percentage}% on package sale for customer: " . ($customer->customer_name ?? $customer->customer_unique_id);
            $this->walletService->credit(
                $reseller,
                $commissionAmount,
                $description,
                'commission',
                $commission->id
            );

            Log::info("Reseller commission of BDT {$commissionAmount} successfully processed for reseller {$reseller->id}");

            return $commission;
        });
    }
}
