<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\HotspotSale;
use App\Models\HotspotVoucher;
use App\Models\PackageList;
use App\Models\RouterList;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class HotspotManager extends Component
{
    use WithPagination;

    // ── Router ──────────────────────────────────────────────────────────────
    public string $selectedRouter = '';

    public string $activeTab = 'dashboard';

    // ── User form ────────────────────────────────────────────────────────────
    public string $u_name = '';

    public string $u_password = '';

    public string $u_profile = 'default';

    public string $u_comment = '';

    public string $u_limit_uptime = '';

    public string $u_limit_bytes = '';

    public string $u_validity_type = 'uptime';

    public ?string $editUserId = null;

    public string $original_u_name = '';

    // ── User Profile form ────────────────────────────────────────────────────
    public string $up_name = '';

    public string $up_rate_limit = '';

    public int $up_shared_users = 1;

    public string $up_session_timeout = '';

    public string $up_idle_timeout = '';

    public string $up_status_autorefresh = '1m';

    public string $up_address_pool = 'none';

    public string $up_comment = '';

    public ?string $editUserProfileId = null;

    public string $original_up_name = '';

    // ── Voucher Generator ────────────────────────────────────────────────────
    public string $v_profile = '';

    public int $v_count = 10;

    public int $v_length = 6;

    public int $v_pwd_length = 6;

    public string $v_prefix = '';

    public string $v_type = 'alphanumeric_mixed';

    public bool $v_user_equals_pass = true;

    public float $v_price = 0;

    public string $v_batch_name = '';

    public string $v_comment = '';

    public string $v_limit_uptime = '';

    public string $v_validity_type = 'uptime';

    public bool $v_push_to_router = true;

    public array $generatedVouchers = [];

    public bool $showVoucherPreview = false;

    // ── Voucher filter ───────────────────────────────────────────────────────
    public string $voucherFilter = 'all';

    public string $voucherSearch = '';

    // ── Sale / Income ────────────────────────────────────────────────────────
    public string $s_voucher_code = '';

    public string $s_username = '';

    public string $s_profile = '';

    public float $s_amount = 0;

    public string $s_payment_method = 'cash';

    public string $s_note = '';

    public string $s_date = '';

    // ── Report filters ───────────────────────────────────────────────────────
    public string $report_from = '';

    public string $report_to = '';

    public string $reportType = 'daily';

    // ── Router data ──────────────────────────────────────────────────────────
    public array $servers = [];

    public array $profiles = [];

    public array $users = [];

    public array $userProfiles = [];

    public array $sessions = [];

    public array $hosts = [];

    public array $ipPools = [];

    public array $routerResources = [];

    public array $hsLogs = [];

    // ── Computed stats ───────────────────────────────────────────────────────
    public int $onlineCount = 0;

    public float $todayIncome = 0;

    public float $monthIncome = 0;

    public int $totalVouchers = 0;

    public int $usedVouchers = 0;

    public array $chartData = [];

    protected function ctrl(): MikrotikController
    {
        return app(MikrotikController::class);
    }

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['mikrotik-setup'])) {
            abort(403);
        }

        $this->report_from = now()->startOfMonth()->toDateString();
        $this->report_to = now()->toDateString();
        $this->s_date = now()->toDateString();

        $first = RouterList::where('action', 'connected')->first();
        if ($first) {
            $this->selectedRouter = $first->router_name;
            $this->loadData();
        }
    }

    public function updatedSelectedRouter(): void
    {
        $this->resetData();
        $this->resetPage();
        if ($this->selectedRouter) {
            $this->loadData();
            $this->dispatch('reinit-chart');
        }
    }

    public function updatedActiveTab(): void
    {
        if ($this->activeTab === 'sessions') {
            $this->refreshSessions();
        }
        if ($this->activeTab === 'dashboard') {
            $this->loadStats();
            $this->dispatch('reinit-chart');
        }
        $this->dispatch('reinit-datatables');
    }

    // =========================================================================
    // DATA LOADING
    // =========================================================================

    public function loadData(): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        try {
            $this->servers = $this->ctrl()->getHotspotServers($this->selectedRouter);
            $this->userProfiles = $this->ctrl()->getHotspotUserProfiles($this->selectedRouter);
            $this->profiles = $this->ctrl()->getHotspotProfiles($this->selectedRouter);
            $this->users = $this->ctrl()->getHotspotUsers($this->selectedRouter);
            $this->ipPools = $this->ctrl()->getIpPools($this->selectedRouter);
            $this->sessions = $this->ctrl()->getHotspotActiveSessions($this->selectedRouter);
            $this->onlineCount = count($this->sessions);
            $this->loadResources();
            $this->refreshLogs();
            $this->performSync(false); // Auto-sync without flash message
            $this->loadStats();
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function loadStats(): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        $this->todayIncome = HotspotSale::forRouter($this->selectedRouter)
            ->whereDate('sale_date', today())->sum('amount');
        $this->monthIncome = HotspotSale::forRouter($this->selectedRouter)
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)->sum('amount');
        $this->totalVouchers = HotspotVoucher::forRouter($this->selectedRouter)->count();
        $this->usedVouchers = HotspotVoucher::forRouter($this->selectedRouter)->where('status', 'used')->count();

        // 7-day chart data
        $days = [];
        $sums = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $days[] = now()->subDays($i)->format('D');
            $sums[] = HotspotSale::forRouter($this->selectedRouter)
                ->whereDate('sale_date', $date)
                ->sum('amount');
        }
        $this->chartData = ['labels' => $days, 'data' => $sums];
    }

    public function refreshSessions(): void
    {
        try {
            $this->sessions = $this->ctrl()->getHotspotActiveSessions($this->selectedRouter);
            $this->hosts = $this->ctrl()->getHotspotHosts($this->selectedRouter);
            $this->onlineCount = count($this->sessions);
            $this->loadResources();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function loadResources(): void
    {
        try {
            $res = $this->ctrl()->singleRead(
                $this->selectedRouter,
                '/system/resource/print',
                'system resource print'
            );
            $this->routerResources = (isset($res[0]) && is_array($res[0])) ? $res[0] : [];
        } catch (\Exception $e) {
        }
    }

    public function refreshLogs(): void
    {
        try {
            // Fetch logs and filter for hotspot topic manually if needed,
            // or just show recent logs. Mikhmon usually filters by 'hotspot'.
            $logs = $this->ctrl()->getRouterLogs($this->selectedRouter, 20);
            $this->hsLogs = array_filter($logs, function ($l) {
                return str_contains(strtolower($l['topics'] ?? ''), 'hotspot') ||
                       str_contains(strtolower($l['message'] ?? ''), 'hotspot');
            });
            // If empty, just show all logs
            if (empty($this->hsLogs)) {
                $this->hsLogs = array_slice($logs, 0, 10);
            }
        } catch (\Exception $e) {
        }
    }

    public function syncVoucherSales(): void
    {
        try {
            // 1. Check active sessions (already doing this for active sales)
            if (! empty($this->sessions)) {
                $activeUsernames = array_column($this->sessions, 'user');
                $unusedActive = HotspotVoucher::forRouter($this->selectedRouter)
                    ->where('status', 'unused')
                    ->whereIn('username', $activeUsernames)
                    ->get();

                foreach ($unusedActive as $v) {
                    $this->markVoucherAsUsed($v, collect($this->sessions)->firstWhere('user', $v->username));
                }
            }

            // 2. Deep Sync: Check ALL users on router for uptime usage
            // This catches users who logged in/out while we weren't looking.
            $routerUsers = $this->ctrl()->getHotspotUsers($this->selectedRouter);
            $routerUsernames = array_column($routerUsers, 'name');

            $dbVouchers = HotspotVoucher::forRouter($this->selectedRouter)
                ->where('status', 'unused')
                ->get();

            foreach ($dbVouchers as $v) {
                $rUser = collect($routerUsers)->firstWhere('name', $v->username);
                if ($rUser) {
                    // Check for usage on router
                    $uptime = $rUser['uptime'] ?? '0s';
                    if ($uptime !== '0s' && ! empty($uptime)) {
                        $this->markVoucherAsUsed($v, $rUser);
                    }
                } else {
                    // Voucher is in DB but missing from Router?
                    // This is 'Unsynced'. We'll show a warning or provide a button to push.
                }
            }

            // 3. Auto-disable expired countdown (realtime) vouchers
            $this->expireRealtimeVouchers();
        } catch (\Exception $e) {
        }
    }

    protected function markVoucherAsUsed($v, $rUser): void
    {
        HotspotSale::create([
            'router_name' => $this->selectedRouter,
            'voucher_code' => $v->code,
            'profile' => $v->profile,
            'username' => $v->username,
            'amount' => $v->price,
            'payment_method' => 'voucher',
            'note' => 'Auto-synced from router usage',
            'sale_date' => now()->toDateString(),
            'sold_by' => $v->created_by,
        ]);

        $updateData = [
            'status' => 'used',
            'used_by' => $v->username,
            'used_at' => now(),
            'mac_address' => $rUser['mac-address'] ?? $rUser['mac_address'] ?? null,
        ];

        // For realtime vouchers, record first login time for app-side expiry tracking
        if ($v->validity_type === 'realtime' && ! $v->first_login_at) {
            $uptimeStr = $rUser['uptime'] ?? '0s';
            $uptimeSeconds = HotspotVoucher::parseUptimeToSeconds($uptimeStr);
            $firstLogin = now()->subSeconds($uptimeSeconds);

            $updateData['first_login_at'] = $firstLogin;
            // Also set the absolute expires_at for easy querying
            if ($v->validity_duration) {
                $updateData['expires_at'] = $firstLogin->copy()->addSeconds(
                    HotspotVoucher::parseMikrotikDuration($v->validity_duration)
                );
            }
        }

        $v->update($updateData);
    }

    /**
     * Check and disable expired countdown (realtime) vouchers on the router.
     * Vouchers with status=used, first_login_at set, and validity_duration passed will be disabled.
     */
    protected function expireRealtimeVouchers(): void
    {
        $expired = HotspotVoucher::forRouter($this->selectedRouter)
            ->realtimeExpired()
            ->get();

        foreach ($expired as $v) {
            if (! $v->isRealtimeExpired()) {
                continue;
            }

            try {
                // OLD CODE (Disable & Expired Status):
                // // Disable user on router
                // $this->ctrl()->disableHotspotUser($this->selectedRouter, $v->username);
                // // Disconnect active session if any
                // try {
                //     $this->ctrl()->disconnectHotspotUser($this->selectedRouter, $v->username);
                // } catch (\Exception $e) {}
                // // Clean up the scheduler on router
                // $this->ctrl()->removeHotspotExpiryScheduler($this->selectedRouter, $v->username);
                // // Mark as expired in DB
                // $v->update(['status' => 'expired']);

                // NEW CODE (Remove & Delete):
                // Remove user from router
                $this->ctrl()->removeHotspotUser($this->selectedRouter, $v->username);

                // Disconnect active session if any
                try {
                    $this->ctrl()->disconnectHotspotUser($this->selectedRouter, $v->username);
                } catch (\Exception $e) {
                    // Session might not exist
                }

                // Clean up the scheduler on router
                $this->ctrl()->removeHotspotExpiryScheduler($this->selectedRouter, $v->username);

                // Delete from DB
                $v->delete();

                \Log::info("Realtime expired and deleted (sync): {$v->username} on {$this->selectedRouter}");
            } catch (\Exception $e) {
                \Log::warning("Failed to expire/delete {$v->username}: " . $e->getMessage());
            }
        }
    }

    public function forceSyncVouchers(): void
    {
        $this->performSync(true);
    }

    protected function performSync(bool $showFlash = true): void
    {
        try {
            // 1. Sync Status (Used/Unused) based on Router Uptime
            $this->syncVoucherSales();

            // 2. Fetch all router users
            $routerUsers = $this->ctrl()->getHotspotUsers($this->selectedRouter);
            $routerUsernames = array_column($routerUsers, 'name');

            // 3. Identify and Push missing DB vouchers TO the router
            $missingOnRouter = HotspotVoucher::forRouter($this->selectedRouter)
                ->where('status', 'unused')
                ->get();

            $pushedCount = 0;
            foreach ($missingOnRouter as $v) {
                if (! in_array($v->username, $routerUsernames)) {
                    try {
                        $this->ctrl()->addHotspotUser($this->selectedRouter, [
                            'name' => $v->username,
                            'password' => $v->password,
                            'profile' => $v->profile,
                            'comment' => 'Synced: '.($v->batch_name ?: 'bulk'),
                        ]);
                        $pushedCount++;
                    } catch (\Exception $ex) {
                        \Log::warning("Failed to push voucher {$v->username} to router: " . $ex->getMessage());
                    }
                }
            }

            // 4. Identify and Import missing Router users INTO the DB (The Pull)
            $dbUsernames = HotspotVoucher::forRouter($this->selectedRouter)->pluck('username')->toArray();
            $importedCount = 0;
            foreach ($routerUsers as $ru) {
                $rName = $ru['name'] ?? null;
                if (! $rName || in_array($rName, $dbUsernames)) {
                    continue;
                }

                $rProfile = $ru['profile'] ?? 'default';

                // Find price from linked DB packages if possible
                $pkg = collect($this->hotspotPackages)->firstWhere('package', $rProfile);

                HotspotVoucher::create([
                    'router_name' => $this->selectedRouter,
                    'username' => $rName,
                    'password' => $ru['password'] ?? $rName,
                    'code' => $rName,
                    'profile' => $rProfile,
                    'price' => $pkg ? $pkg->price : 0,
                    'batch_name' => 'Imported from Router',
                    'status' => ($ru['uptime'] ?? '0s') !== '0s' ? 'used' : 'unused',
                    'created_by' => auth()->id(),
                ]);
                $importedCount++;
            }

            if ($showFlash) {
                $msg = 'Sync Complete!';
                if ($pushedCount > 0) {
                    $msg .= " Pushed $pushedCount to router.";
                }
                if ($importedCount > 0) {
                    $msg .= " Imported $importedCount into database.";
                }
                flash()->success($msg);
            }

            if ($showFlash || $pushedCount > 0 || $importedCount > 0) {
                $this->loadStats();
            }
        } catch (\Exception $e) {
            if ($showFlash) {
                flash()->error('Sync Failed: '.$e->getMessage());
            }
        }
    }

    public function editUser(array $u): void
    {
        $this->editUserId = $u['.id'] ?? null;
        $this->original_u_name = $u['name'] ?? '';
        $this->u_name = $u['name'] ?? '';
        
        $dbVoucher = HotspotVoucher::forRouter($this->selectedRouter)
            ->where('username', $u['name'] ?? '')
            ->first();
        $this->u_password = !empty($u['password']) ? $u['password'] : ($dbVoucher?->password ?? '');

        $this->u_profile = $u['profile'] ?? 'default';
        $this->u_comment = $u['comment'] ?? '';
        $this->u_limit_uptime = $u['limit-uptime'] ?? '';
        $this->u_limit_bytes = $u['limit-bytes-total'] ?? '';

        // Detect validity type from comment prefix or DB record
        $comment = $u['comment'] ?? '';
        if (str_starts_with($comment, 'RT:')) {
            $this->u_validity_type = 'realtime';
            // Extract duration from comment (RT:1h → 1h)
            $this->u_limit_uptime = substr($comment, 3, strpos($comment . ' ', ' ') - 3) ?: $this->u_limit_uptime;
        } else {
            // Check DB record
            $dbVoucher = HotspotVoucher::where('username', $u['name'] ?? '')->first();
            $this->u_validity_type = $dbVoucher?->validity_type ?? 'uptime';
        }
    }

    public function startAddUser(): void
    {
        $this->reset(['u_name', 'u_password', 'u_comment', 'editUserId', 'original_u_name', 'u_limit_uptime', 'u_limit_bytes', 'u_validity_type']);
        $this->u_profile = collect($this->userProfiles)->pluck('name')->first() ?: 'default';
        $this->dispatch('open-modal', 'userModal');
    }

    public function editUserByName(string $name): void
    {
        $u = collect($this->users)->firstWhere('name', $name);
        if (! $u) {
            flash()->error('User not found. Try Refresh.');

            return;
        }
        $this->editUser($u);
        $this->dispatch('open-modal', 'userModal');
    }

    public function printHotspotUserSlip(string $username): void
    {
        $u = collect($this->users)->firstWhere('name', $username);
        if (! $u) {
            flash()->error('User not found. Try Refresh.');

            return;
        }
        $v = HotspotVoucher::where('username', $username)->first();
        $this->dispatch('print-vouchers', vouchers: [[
            'username' => $u['name'],
            'password' => (string) ($u['password'] ?? ''),
            'profile' => (string) ($u['profile'] ?? 'default'),
            'price' => $v ? (float) $v->price : 0,
            'qr_code' => 'yes',
        ]], batch: $v ? 'Voucher' : 'Hotspot User', router: $this->selectedRouter);
    }

    public function addUser(): void
    {
        $this->validate([
            'u_name' => 'required|string|max:100',
            'u_password' => 'required|string|max:100',
            'u_profile' => 'required|string',
            'u_comment' => 'nullable|string',
            'u_limit_uptime' => 'nullable|string',
            'u_limit_bytes' => 'nullable|string',
            'u_validity_type' => 'required|in:uptime,realtime',
        ]);

        try {
            $isRealtime = $this->u_validity_type === 'realtime';

            $p = [
                'name' => $this->u_name,
                'password' => $this->u_password,
                'profile' => $this->u_profile,
            ];

            if ($isRealtime && $this->u_limit_uptime) {
                // For realtime: store duration in comment, don't set limit-uptime
                $p['comment'] = 'RT:' . $this->u_limit_uptime . ($this->u_comment ? ' ' . $this->u_comment : '');
            } else {
                // For uptime: use limit-uptime as before
                $p['comment'] = $this->u_comment;
                if ($this->u_limit_uptime) {
                    $p['limit-uptime'] = $this->u_limit_uptime;
                }
            }

            if ($this->u_limit_bytes) {
                $p['limit-bytes-total'] = $this->u_limit_bytes;
            }

            // Find price from linked DB packages if possible
            $pkg = collect($this->hotspotPackages)->firstWhere('package', $this->u_profile);
            $price = $pkg ? (float) $pkg->price : 0;

            // 1. Create or Update local DB voucher record first to keep password safe
            HotspotVoucher::updateOrCreate(
                [
                    'router_name' => $this->selectedRouter,
                    'username' => $this->u_name,
                ],
                [
                    'code' => $this->u_name,
                    'password' => $this->u_password,
                    'profile' => $this->u_profile,
                    'price' => $price,
                    'status' => 'unused',
                    'comment' => $p['comment'] ?? '',
                    'validity_type' => $this->u_validity_type,
                    'validity_duration' => $isRealtime ? $this->u_limit_uptime : null,
                    'created_by' => auth()->id() ?: 1,
                ]
            );

            // If username was renamed, clean up old DB voucher record
            if ($this->original_u_name && $this->original_u_name !== $this->u_name) {
                HotspotVoucher::forRouter($this->selectedRouter)
                    ->where('username', $this->original_u_name)
                    ->delete();
            }

            // 2. Add/Update on MikroTik Router
            $this->ctrl()->addHotspotUser($this->selectedRouter, $p, $this->original_u_name ?: null);



            flash()->success($this->editUserId ? 'User updated!' : 'User added!');
            $this->reset(['u_name', 'u_password', 'u_comment', 'editUserId', 'original_u_name', 'u_limit_uptime', 'u_limit_bytes', 'u_validity_type']);
            $this->users = $this->ctrl()->getHotspotUsers($this->selectedRouter);
            $this->dispatch('reinit-datatables');
            $this->dispatch('close-modal', 'userModal');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeUser(string $name): void
    {
        try {
            $this->ctrl()->removeHotspotUser($this->selectedRouter, $name);
            HotspotVoucher::forRouter($this->selectedRouter)->where('username', $name)->delete();
            flash()->success("User '{$name}' removed.");
            $this->users = $this->ctrl()->getHotspotUsers($this->selectedRouter);
            $this->loadStats();
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function disconnectSession(string $user): void
    {
        try {
            $this->ctrl()->disconnectHotspotUser($this->selectedRouter, $user);
            flash()->success("Session for '{$user}' disconnected.");
            $this->refreshSessions();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    // =========================================================================
    // USER PROFILE CRUD
    // =========================================================================

    public function editUserProfile(array $up): void
    {
        $this->editUserProfileId = $up['.id'] ?? null;
        $this->original_up_name = $up['name'] ?? '';
        $this->up_name = $up['name'] ?? '';
        $this->up_rate_limit = $up['rate-limit'] ?? '';
        $this->up_shared_users = (int) ($up['shared-users'] ?? 1);
        $this->up_session_timeout = $up['session-timeout'] ?? '';
        $this->up_idle_timeout = $up['idle-timeout'] ?? '';
        $this->up_status_autorefresh = $up['status-autorefresh'] ?? '1m';
        $this->up_address_pool = $up['address-pool'] ?? 'none';
        $this->up_comment = $up['comment'] ?? '';
    }

    public function editUserProfileByName(string $name): void
    {
        $up = collect($this->userProfiles)->firstWhere('name', $name);
        if (! $up) {
            flash()->error("Profile '{$name}' not found. Try Refresh.");

            return;
        }
        $this->editUserProfile($up);
    }

    public function addUserProfile(): void
    {
        $this->validate([
            'up_name' => 'required|string|max:100',
            'up_shared_users' => 'required|integer|min:1',
            'up_rate_limit' => 'nullable|string',
            'up_session_timeout' => 'nullable|string',
            'up_idle_timeout' => 'nullable|string',
            'up_status_autorefresh' => 'nullable|string',
            'up_address_pool' => 'nullable|string',
        ]);
        try {
            $this->ctrl()->addHotspotUserProfile($this->selectedRouter, [
                'name' => $this->up_name,
                'rate_limit' => $this->up_rate_limit,
                'shared_users' => $this->up_shared_users,
                'session_timeout' => $this->up_session_timeout,
                'idle_timeout' => $this->up_idle_timeout,
                'status_autorefresh' => $this->up_status_autorefresh,
                'address_pool' => $this->up_address_pool,
            ], $this->original_up_name ?: null);

            flash()->success($this->editUserProfileId ? 'Profile updated!' : 'Profile added!');
            $this->reset([
                'up_name', 'up_rate_limit', 'up_session_timeout', 'up_idle_timeout',
                'up_comment', 'editUserProfileId', 'original_up_name',
                'up_status_autorefresh', 'up_address_pool',
            ]);
            $this->up_shared_users = 1;
            $this->userProfiles = $this->ctrl()->getHotspotUserProfiles($this->selectedRouter);
            $this->dispatch('reinit-datatables');
            $this->dispatch('close-modal', 'profileModal');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeUserProfile(string $name): void
    {
        try {
            $this->ctrl()->removeHotspotUserProfile($this->selectedRouter, $name);
            flash()->success('Profile removed.');
            $this->userProfiles = $this->ctrl()->getHotspotUserProfiles($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function startAddUserProfile(): void
    {
        $this->reset([
            'up_name', 'up_rate_limit', 'up_session_timeout', 'up_idle_timeout',
            'up_comment', 'editUserProfileId', 'original_up_name',
            'up_status_autorefresh', 'up_address_pool',
        ]);
        $this->up_shared_users = 1;
    }

    public function cancelEditUserProfile(): void
    {
        $this->reset([
            'up_name', 'up_rate_limit', 'up_session_timeout', 'up_idle_timeout',
            'up_comment', 'editUserProfileId', 'original_up_name',
            'up_status_autorefresh', 'up_address_pool',
        ]);
        $this->up_shared_users = 1;
    }

    // =========================================================================
    // VOUCHER GENERATION
    // =========================================================================

    private function generateVoucherString(string $type, int $length): string
    {
        $chars = match ($type) {
            'numeric' => '0123456789',
            'upper' => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
            'lower' => 'abcdefghjklmnpqrstuvwxyz',
            'mixed' => 'abcdefghjklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ',
            'alphanumeric_lower' => 'abcdefghjklmnpqrstuvwxyz23456789',
            'alphanumeric_upper' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',
            default => 'abcdefghjklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789', // alphanumeric_mixed
        };

        // Avoid potentially matching exact substrings by excessive shuffling
        return substr(str_shuffle(str_repeat($chars, 5)), 0, $length);
    }

    public function generateVouchers(): void
    {
        $this->validate([
            'v_profile' => 'required|string',
            'v_count' => 'required|integer|min:1|max:500',
            'v_length' => 'required|integer|min:3|max:20',
            'v_pwd_length' => 'required|integer|min:3|max:20',
            'v_validity_type' => 'required|in:uptime,realtime',
        ]);

        $isRealtime = $this->v_validity_type === 'realtime';
        $batch = $this->v_batch_name ?: ('BATCH-'.strtoupper(Str::random(6)));
        $this->v_batch_name = $batch;
        $this->generatedVouchers = [];

        $existing = HotspotVoucher::pluck('code')->toArray();

        $created = [];
        $attempts = 0;
        while (count($created) < $this->v_count && $attempts < 5000) {
            $attempts++;
            $prefix = strtoupper($this->v_prefix);
            $username = $prefix.$this->generateVoucherString($this->v_type, $this->v_length);
            // $username acts as the unique 'code' for the voucher record
            if (in_array($username, $existing) || in_array($username, array_column($created, 'code'))) {
                continue;
            }

            $password = $this->v_user_equals_pass ? $username : $this->generateVoucherString($this->v_type, $this->v_pwd_length);

            $commentText = $isRealtime
                ? 'RT:' . $this->v_limit_uptime . ($this->v_comment ? ' ' . $this->v_comment : '')
                : $this->v_comment;

            $created[] = [
                'router_name' => $this->selectedRouter,
                'code' => $username,
                'profile' => $this->v_profile,
                'username' => $username,
                'password' => $password,
                'price' => $this->v_price,
                'batch_name' => $batch,
                'status' => 'unused',
                'comment' => $commentText,
                'limit_uptime' => $this->v_limit_uptime,
                'validity_type' => $this->v_validity_type,
                'validity_duration' => $this->v_limit_uptime ?: null,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Save to DB (remove non-DB keys before insert)
        HotspotVoucher::insert(array_map(fn ($v) => array_diff_key($v, ['limit_uptime' => '']), $created));

        // Push to router if requested
        if ($this->v_push_to_router) {


            $batch_users = array_map(fn ($v) => [
                'name' => $v['username'],
                'password' => $v['password'],
                'profile' => $v['profile'],
                'comment' => $v['comment'] ?: $v['batch_name'],
                // Only set limit-uptime for uptime type, not for realtime
                'limit-uptime' => $isRealtime ? '' : $v['limit_uptime'],
            ], $created);
            $results = $this->ctrl()->pushHotspotUserBatch($this->selectedRouter, $batch_users);
            $errors = array_filter($results, fn ($r) => str_starts_with($r, 'Error'));
            if ($errors) {
                flash()->warning(count($errors).' voucher(s) failed to push to router.');
            }
        }

        $this->generatedVouchers = $created;
        $this->showVoucherPreview = true;
        $this->loadStats();
        $typeLabel = $isRealtime ? ' (Real-time)' : ' (Uptime)';
        flash()->success(count($created).' vouchers generated in batch "'.$batch.'"!' . $typeLabel);
    }

    public function deleteVoucherBatch(string $batch): void
    {
        HotspotVoucher::where('batch_name', $batch)
            ->where('status', 'unused')
            ->delete();
        flash()->success("Unused vouchers in batch '{$batch}' deleted.");
        $this->loadStats();
    }

    public function triggerPrintBatch(string $batchName): void
    {
        $vouchers = array_map(function ($v) {
            $v['qr_code'] = 'yes';

            return $v;
        }, HotspotVoucher::where('batch_name', $batchName)
            ->where('router_name', $this->selectedRouter)
            ->get(['username', 'password', 'profile', 'price'])
            ->toArray());

        // Dispatch JS event to cleanly render print HTML without relying on pagination views
        $this->dispatch('print-vouchers', vouchers: $vouchers, batch: $batchName, router: $this->selectedRouter);
    }

    public function triggerPrintAll(): void
    {
        $q = HotspotVoucher::forRouter($this->selectedRouter);
        if ($this->voucherFilter !== 'all') {
            $q->where('status', $this->voucherFilter);
        }
        if ($this->voucherSearch) {
            $q->where(function ($q) {
                $q->where('code', 'like', '%'.$this->voucherSearch.'%')
                    ->orWhere('batch_name', 'like', '%'.$this->voucherSearch.'%')
                    ->orWhere('profile', 'like', '%'.$this->voucherSearch.'%');
            });
        }

        $vouchers = array_map(function ($v) {
            $v['qr_code'] = 'yes';

            return $v;
        }, $q->orderByDesc('created_at')->get(['username', 'password', 'profile', 'price'])->toArray());

        if (empty($vouchers)) {
            flash()->warning('No vouchers found matching your filter to print.');

            return;
        }

        $this->dispatch('print-vouchers', vouchers: $vouchers, batch: 'Filtered Vouchers', router: $this->selectedRouter);
    }

    public function syncDatabasePackages(): void
    {
        try {
            $packages = $this->hotspotPackages;
            if ($packages->isEmpty()) {
                flash()->warning('No packages found in database to sync.');

                return;
            }

            $results = $this->ctrl()->syncHotspotProfilesToRouter($this->selectedRouter, $packages);
            flash()->success(count($results).' packages synced to router.');
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function toggleUserStatus(string $username, string $currentStatus): void
    {
        try {
            $enable = ($currentStatus === 'true'); // if true (disabled), we want to enable (true)
            $this->ctrl()->toggleHotspotUser($this->selectedRouter, $username, $enable);

            flash()->success($enable ? "User '{$username}' enabled." : "User '{$username}' disabled.");
            $this->loadData(); // Refresh UI
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function deleteSingleVoucher(int $id): void
    {
        try {
            $v = HotspotVoucher::find($id);
            if ($v) {
                // Remove from Router
                $this->ctrl()->removeHotspotUser($this->selectedRouter, $v->username);
                // Remove from DB
                $v->delete();
                flash()->success('Voucher and Router User removed.');
            }
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editVoucher(int $id): void
    {
        $v = HotspotVoucher::find($id);
        if ($v) {
            // Re-use the existing user modal logic
            $u = collect($this->users)->firstWhere('name', $v->username);
            if ($u) {
                $this->editUser($u);
                $this->dispatch('open-modal', 'userModal');
            } else {
                flash()->warning('Could not find user properties on router.');
            }
        }
    }

    public function triggerPrintSingle(int $id, string $qr_code = 'yes'): void
    {
        $v = HotspotVoucher::find($id);
        if ($v) {
            $vouchers = [[
                'username' => $v->username,
                'password' => $v->password,
                'profile' => $v->profile,
                'price' => $v->price,
                'qr_code' => $qr_code,
            ]];
            $this->dispatch('print-vouchers', vouchers: $vouchers, batch: 'Single', router: $this->selectedRouter);
        }
    }

    // =========================================================================
    // INCOME / SALES
    // =========================================================================

    public function updatedVProfile(): void
    {
        $pkg = collect($this->hotspotPackages)->firstWhere('package', $this->v_profile);
        if ($pkg) {
            $this->v_price = (float) $pkg->price;
            $this->v_limit_uptime = $pkg->validity ?? '';
        }
    }

    public function recordSale(): void
    {
        $this->validate([
            's_username' => 'required|string|max:100',
            's_profile' => 'required|string',
            's_amount' => 'required|numeric|min:0',
            's_date' => 'required|date',
        ]);

        HotspotSale::create([
            'router_name' => $this->selectedRouter,
            'voucher_code' => $this->s_voucher_code ?: null,
            'profile' => $this->s_profile,
            'username' => $this->s_username,
            'amount' => $this->s_amount,
            'payment_method' => $this->s_payment_method,
            'note' => $this->s_note,
            'sale_date' => $this->s_date,
            'sold_by' => auth()->id(),
        ]);

        // Mark voucher as used if provided
        if ($this->s_voucher_code) {
            HotspotVoucher::where('code', $this->s_voucher_code)
                ->update(['status' => 'used', 'used_by' => $this->s_username, 'used_at' => now()]);
        }

        flash()->success('Sale recorded successfully!');
        $this->reset(['s_voucher_code', 's_username', 's_note']);
        $this->s_amount = 0;
        $this->s_date = now()->toDateString();
        $this->loadStats();
        $this->dispatch('close-modal', 'sale-modal');
    }

    public function deleteSale(int $id): void
    {
        HotspotSale::destroy($id);
        flash()->success('Sale record deleted.');
        $this->loadStats();
    }

    // =========================================================================
    // AUTO-FILL SALE FROM VOUCHER
    // =========================================================================

    public function updatedSVoucherCode(): void
    {
        $v = HotspotVoucher::where('code', $this->s_voucher_code)->first();
        if ($v) {
            $this->s_username = $v->username;
            $this->s_profile = $v->profile;
            $this->s_amount = $v->price;
        }
    }

    // =========================================================================
    // REPORT HELPERS
    // =========================================================================

    public function getReportData(): array
    {
        $from = $this->report_from ?: now()->startOfMonth()->toDateString();
        $to = $this->report_to ?: now()->toDateString();

        $sales = HotspotSale::forRouter($this->selectedRouter)
            ->forPeriod($from, $to)
            ->orderBy('sale_date')
            ->get();

        $grouped = $sales->groupBy(fn ($s) => $s->sale_date->toDateString());

        $byProfile = $sales->groupBy('profile')->map(fn ($g) => [
            'count' => $g->count(),
            'total' => $g->sum('amount'),
            'profile' => $g->first()->profile,
        ])->values();

        return [
            'total' => $sales->sum('amount'),
            'count' => $sales->count(),
            'daily' => $grouped,
            'byProfile' => $byProfile,
            'from' => $from,
            'to' => $to,
        ];
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function resetData(): void
    {
        $this->servers = $this->profiles = $this->users = $this->userProfiles =
        $this->sessions = $this->hosts = [];
        $this->onlineCount = 0;
        $this->generatedVouchers = [];
        $this->showVoucherPreview = false;
    }

    public function getVouchersProperty()
    {
        $q = HotspotVoucher::forRouter($this->selectedRouter);
        if ($this->voucherFilter !== 'all') {
            $q->where('status', $this->voucherFilter);
        }
        if ($this->voucherSearch) {
            $q->where(function ($q) {
                $q->where('code', 'like', '%'.$this->voucherSearch.'%')
                    ->orWhere('batch_name', 'like', '%'.$this->voucherSearch.'%')
                    ->orWhere('profile', 'like', '%'.$this->voucherSearch.'%');
            });
        }

        return $q->orderByDesc('created_at')->paginate(25);
    }

    public function getVoucherBatchesProperty()
    {
        return HotspotVoucher::forRouter($this->selectedRouter)
            ->selectRaw('batch_name, profile, price, count(*) as total,
                sum(status="unused") as unused_count,
                sum(status="used") as used_count,
                max(created_at) as created_at')
            ->groupBy('batch_name', 'profile', 'price')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getSalesProperty()
    {
        return HotspotSale::forRouter($this->selectedRouter)
            ->forPeriod($this->report_from ?: now()->startOfMonth()->toDateString(), $this->report_to ?: now()->toDateString())
            ->orderByDesc('sale_date')
            ->get();
    }

    public function getHotspotPackagesProperty()
    {
        // Packages from local DB that match this router — for profile autofill
        return PackageList::where('router_name', $this->selectedRouter)
            ->orWhereNull('router_name')
            ->orderBy('package')
            ->get();
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();
        $reportData = $this->activeTab === 'report' ? $this->getReportData() : [];
        $vouchers = $this->activeTab === 'vouchers' ? $this->vouchers : collect();
        $voucherBatches = $this->activeTab === 'vouchers' ? $this->voucherBatches : collect();
        $sales = in_array($this->activeTab, ['income', 'report']) ? $this->sales : collect();

        $hotspotPackages = $this->hotspotPackages;

        return view('livewire.mikrotik.hotspot-manager', compact(
            'routers', 'reportData', 'vouchers', 'voucherBatches', 'sales', 'hotspotPackages'
        ))->layout('layouts.app');
    }
}
