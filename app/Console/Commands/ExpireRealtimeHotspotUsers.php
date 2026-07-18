<?php

namespace App\Console\Commands;

use App\Http\Controllers\MikrotikController;
use App\Models\HotspotVoucher;
use App\Models\RouterList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireRealtimeHotspotUsers extends Command
{
    protected $signature = 'app:expire-realtime-hotspot-users';

    protected $description = 'Check and disable/remove hotspot users whose realtime validity has expired. Runs as a backup to the MikroTik on-login scheduler.';

    public function handle(): void
    {
        // Get all realtime vouchers that have a first_login_at set and are not yet expired in DB
        $vouchers = HotspotVoucher::realtimeExpired()->get();

        if ($vouchers->isEmpty()) {
            return; // Nothing to do — most common case
        }

        $ctrl = app(MikrotikController::class);
        $expiredCount = 0;

        // Group by router to minimize connections
        $grouped = $vouchers->groupBy('router_name');

        foreach ($grouped as $routerName => $routerVouchers) {
            // Check router is still connected
            $router = RouterList::where('router_name', $routerName)
                ->where('action', 'connected')
                ->first();

            if (! $router) {
                continue;
            }

            foreach ($routerVouchers as $v) {
                if (! $v->isRealtimeExpired()) {
                    continue;
                }

                try {
                    // Disable user on router
                    $ctrl->disableHotspotUser($routerName, $v->username);

                    // Disconnect active session if any
                    try {
                        $ctrl->disconnectHotspotUser($routerName, $v->username);
                    } catch (\Exception $e) {
                        // Session might not exist, that's fine
                    }

                    // Clean up the scheduler on router (if MikroTik didn't already)
                    $ctrl->removeHotspotExpiryScheduler($routerName, $v->username);

                    // Mark as expired in DB
                    $v->update(['status' => 'expired']);

                    $expiredCount++;
                    Log::info("Realtime expired: {$v->username} on {$routerName}");
                } catch (\Exception $e) {
                    Log::warning("Failed to expire {$v->username} on {$routerName}: " . $e->getMessage());
                }
            }
        }

        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} realtime hotspot user(s).");
            Log::info("ExpireRealtimeHotspotUsers: Expired {$expiredCount} user(s).");
        }
    }
}
