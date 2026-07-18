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
        $ctrl = app(MikrotikController::class);

        // 1. Sync voucher usage for all active routers first (collects first login time)
        $routers = RouterList::where('action', 'connected')->get();
        foreach ($routers as $router) {
            $this->syncRouterVouchers($router->router_name, $ctrl);
        }

        // 2. Get all realtime vouchers that have expired in DB
        $vouchers = HotspotVoucher::realtimeExpired()->get();

        if ($vouchers->isEmpty()) {
            return;
        }

        $expiredCount = 0;
        $grouped = $vouchers->groupBy('router_name');

        foreach ($grouped as $routerName => $routerVouchers) {
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
                    // OLD CODE (Disable & Expired Status):
                    // // Disable user on router
                    // $ctrl->disableHotspotUser($routerName, $v->username);
                    // // Disconnect active session if any
                    // try {
                    //     $ctrl->disconnectHotspotUser($routerName, $v->username);
                    // } catch (\Exception $e) {}
                    // // Clean up the scheduler on router (if any exist)
                    // $ctrl->removeHotspotExpiryScheduler($routerName, $v->username);
                    // // Mark as expired in DB
                    // $v->update(['status' => 'expired']);

                    // NEW CODE (Remove & Delete):
                    // Remove user from router
                    $ctrl->removeHotspotUser($routerName, $v->username);

                    // Disconnect active session if any
                    try {
                        $ctrl->disconnectHotspotUser($routerName, $v->username);
                    } catch (\Exception $e) {
                        // Session might not exist, that's fine
                    }

                    // Clean up the scheduler on router (if any exist)
                    $ctrl->removeHotspotExpiryScheduler($routerName, $v->username);

                    // Delete from DB
                    $v->delete();

                    $expiredCount++;
                    Log::info("Realtime expired and deleted: {$v->username} on {$routerName}");
                } catch (\Exception $e) {
                    Log::warning("Failed to expire/delete {$v->username} on {$routerName}: " . $e->getMessage());
                }
            }
        }

        if ($expiredCount > 0) {
            $this->info("Expired {$expiredCount} realtime hotspot user(s).");
            Log::info("ExpireRealtimeHotspotUsers: Expired {$expiredCount} user(s).");
        }
    }

    private function syncRouterVouchers(string $routerName, MikrotikController $ctrl): void
    {
        try {
            // Fetch active sessions
            $sessions = $ctrl->getHotspotActiveSessions($routerName);
            if (is_array($sessions)) {
                $activeUsernames = array_column($sessions, 'user');

                // Mark unused vouchers with active sessions as "used"
                $unusedActive = HotspotVoucher::forRouter($routerName)
                    ->where('status', 'unused')
                    ->whereIn('username', $activeUsernames)
                    ->get();

                foreach ($unusedActive as $v) {
                    $session = collect($sessions)->firstWhere('user', $v->username);
                    $this->markVoucherAsUsedInConsole($v, $session, $routerName);
                }
            }

            // Fetch all hotspot users from router
            $routerUsers = $ctrl->getHotspotUsers($routerName);
            $dbVouchers = HotspotVoucher::forRouter($routerName)
                ->where('status', 'unused')
                ->get();

            foreach ($dbVouchers as $v) {
                $rUser = collect($routerUsers)->firstWhere('name', $v->username);
                if ($rUser) {
                    $uptime = $rUser['uptime'] ?? '0s';
                    if ($uptime !== '0s' && ! empty($uptime)) {
                        $this->markVoucherAsUsedInConsole($v, $rUser, $routerName);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Console voucher sync failed for router {$routerName}: " . $e->getMessage());
        }
    }

    private function markVoucherAsUsedInConsole(HotspotVoucher $v, $rUser, string $routerName): void
    {
        try {
            \App\Models\HotspotSale::create([
                'router_name' => $routerName,
                'voucher_code' => $v->code,
                'profile' => $v->profile,
                'username' => $v->username,
                'amount' => $v->price,
                'payment_method' => 'voucher',
                'note' => 'Auto-synced from router usage (Console)',
                'sale_date' => now()->toDateString(),
                'sold_by' => $v->created_by,
            ]);
        } catch (\Exception $saleEx) {
            // Ignore duplicate sale record if any, continue to update status
        }

        $updateData = [
            'status' => 'used',
            'used_by' => $v->username,
            'used_at' => now(),
            'mac_address' => $rUser['mac-address'] ?? $rUser['mac_address'] ?? null,
        ];

        if ($v->validity_type === 'realtime' && ! $v->first_login_at) {
            $updateData['first_login_at'] = now();
            if ($v->validity_duration) {
                $updateData['expires_at'] = now()->addSeconds(
                    HotspotVoucher::parseMikrotikDuration($v->validity_duration)
                );
            }
        }

        $v->update($updateData);
    }
}
