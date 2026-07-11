<?php

namespace App\Listeners;

use App\Events\PackagePurchased;
use App\Services\ResellerCommissionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GenerateResellerCommission
{
    protected $commissionService;

    /**
     * Create the event listener.
     */
    public function __construct(ResellerCommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Handle the event.
     */
    public function handle(PackagePurchased $event): void
    {
        try {
            $this->commissionService->processCommission($event->customer, $event->amount);
        } catch (\Exception $e) {
            Log::error("Failed to generate reseller commission in listener: " . $e->getMessage());
        }
    }
}
