<?php

namespace App\Livewire;

use App\Http\Controllers\MikrotikController;
use App\Models\PackageList;
use App\Models\RouterList;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PackageListSetup extends Component
{
    public $package_id;

    public $package_name;

    public $price;

    public $description;

    public $plan_label;

    public $speed;

    public $features_text = "24 HOURS UNLIMITED\nFiber Optics\nOTC Fee - 3000 Taka\n24/7 Customer Support";

    public $is_featured = false;

    public $show_on_site = true;

    public $sort_order = 0;

    public $mikrotik_rate_limit;

    public $mikrotik_local_address;

    public $mikrotik_remote_address;

    public $push_to_mikrotik = false;

    public $mikrotik_pools = [];

    public $router_name = null;

    public $packagesData = [];

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['package-setup'])) {
            abort(403, 'Unauthorized action.');
        }
        $this->reset([
            'package_id', 'package_name', 'price', 'description', 'plan_label', 'speed',
            'features_text', 'is_featured', 'show_on_site', 'sort_order',
            'mikrotik_rate_limit', 'mikrotik_local_address', 'mikrotik_remote_address', 'push_to_mikrotik', 'router_name',
        ]);
        $this->show_on_site = true;
        $this->dataRender();
    }

    public function dataRender()
    {
        $this->packagesData = PackageList::with('router')->orderBy('sort_order')->orderBy('price')->get()->toArray();
    }

    protected function rules(): array
    {
        return [
            'package_name' => [
                'required', 'string', 'max:255',
                Rule::unique('package_lists', 'package')
                    ->ignore($this->package_id)
                    ->where('router_name', $this->router_name),
            ],
            'router_name' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|max:255',
            'plan_label' => 'nullable|max:50',
            'speed' => 'nullable|max:100',
            'features_text' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'mikrotik_rate_limit' => 'nullable|max:100',
            'mikrotik_local_address' => 'nullable|max:100',
            'mikrotik_remote_address' => 'nullable|max:100',
        ];
    }

    public function updatedRouterName($value): void
    {
        if ($value) {
            $this->loadPools();
        } else {
            $this->mikrotik_pools = [];
        }
    }

    public function createPackage(): void
    {
        $this->validate();
        \Log::info('PackageListSetup: Starting createPackage for id='.($this->package_id ?? 'NEW').' name='.$this->package_name);

        try {
            // Convert newline-separated features to JSON array
            $features = collect(explode("\n", $this->features_text ?? ''))
                ->map(fn ($f) => ['value' => trim($f)])
                ->filter(fn ($f) => ! empty($f['value']))
                ->values()
                ->toArray();

            $controller = app(MikrotikController::class);
            $normalizedRouterName = ! empty($this->router_name) ? $this->router_name : null;

            // Capture old state if editing
            $oldPackage = null;
            if ($this->package_id) {
                $oldPackage = PackageList::find($this->package_id);
                \Log::debug('PackageListSetup: Detected edit mode. Previous name='.($oldPackage?->package ?? 'N/A'));
            }

            // 1. Update or Create local record
            $package = PackageList::updateOrCreate(
                ['id' => $this->package_id],
                [
                    'package' => $this->package_name,
                    'price' => $this->price,
                    'description' => $this->description,
                    'plan_label' => $this->plan_label,
                    'speed' => $this->speed,
                    'features' => $features,
                    'is_featured' => $this->is_featured,
                    'show_on_site' => $this->show_on_site,
                    'sort_order' => $this->sort_order ?? 0,
                    'mikrotik_rate_limit' => $this->mikrotik_rate_limit,
                    'mikrotik_local_address' => $this->mikrotik_local_address,
                    'mikrotik_remote_address' => $this->mikrotik_remote_address,
                    'push_to_mikrotik' => $this->push_to_mikrotik,
                    'router_name' => $normalizedRouterName,
                ]
            );

            // 2. Synchronous MikroTik Logic
            if ($oldPackage) {
                // CASE: Renaming on MikroTik (if sync was and still is ON)
                if ($oldPackage->push_to_mikrotik && $this->push_to_mikrotik && $oldPackage->package !== $this->package_name) {
                    \Log::info("PackageListSetup: Renaming profile on Mikrotik from '{$oldPackage->package}' to '{$this->package_name}'");
                    $controller->updateProfileOnRouters($oldPackage->package, $this->package_name, $this->mikrotik_rate_limit, $this->mikrotik_local_address, $this->mikrotik_remote_address, $oldPackage->router_name);
                }

                // CASE: Cleanup MikroTik (if sync toggled OFF OR router changed)
                if ($oldPackage->push_to_mikrotik && (! $this->push_to_mikrotik || $oldPackage->router_name !== $normalizedRouterName)) {
                    \Log::info("PackageListSetup: Toggled OFF or Router changed. Deleting profile '{$oldPackage->package}' from old router(s)");
                    $controller->deleteProfileFromRouters($oldPackage->package, $oldPackage->router_name);
                }
            }

            // CASE: Create or Update current configuration on MikroTik (if sync is ON)
            $pushError = null;
            \Log::debug('PackageListSetup: Sync check: push_to_mikrotik='.($this->push_to_mikrotik ? 'TRUE' : 'FALSE').', router='.($normalizedRouterName ?? 'ALL'));
            if ($this->push_to_mikrotik) {
                \Log::info("PackageListSetup: Pushing/Updating configuration for '{$this->package_name}' on router: ".($normalizedRouterName ?? 'ALL'));
                $pushResults = $controller->pushProfileToRouters($this->package_name, $this->mikrotik_rate_limit, $this->mikrotik_local_address, $this->mikrotik_remote_address, $normalizedRouterName);
                \Log::debug('PackageListSetup: Push Result: '.json_encode($pushResults));

                // Track failures
                $failures = collect($pushResults)->filter(fn ($res) => $res !== 'OK' && (! isset($res['status']) || ! $res['status']));
                if ($failures->isNotEmpty()) {
                    $pushError = 'MikroTik Sync failed on: '.$failures->keys()->implode(', ');
                }
            }

            if ($pushError) {
                flash()->warning('Package saved locally, but '.$pushError);
            } else {
                flash()->success('Package saved and synchronized successfully!');
            }

            \Log::info("PackageListSetup: createPackage COMPLETED for '{$this->package_name}'");
            $this->reset([
                'package_id', 'package_name', 'price', 'description', 'plan_label', 'speed',
                'features_text', 'is_featured', 'sort_order',
                'mikrotik_rate_limit', 'mikrotik_local_address', 'mikrotik_remote_address', 'push_to_mikrotik', 'router_name',
            ]);
            $this->show_on_site = true;
            $this->dataRender();
        } catch (\Exception $e) {
            \Log::error("PackageListSetup: FAILED to save or sync package '{$this->package_name}': ".$e->getMessage(), [
                'exception' => $e,
            ]);
            flash()->error('Error saving data: '.$e->getMessage());
        }
    }

    public function editPackage(int $id): void
    {
        $package = PackageList::findOrFail($id);
        $this->package_id = $id;
        $this->package_name = $package->package;
        $this->price = $package->price;
        $this->description = $package->description;
        $this->plan_label = $package->plan_label;
        $this->speed = $package->speed;
        $this->is_featured = $package->is_featured;
        $this->show_on_site = $package->show_on_site;
        $this->sort_order = $package->sort_order;
        $this->mikrotik_rate_limit = $package->mikrotik_rate_limit;
        $this->mikrotik_local_address = $package->mikrotik_local_address;
        $this->mikrotik_remote_address = $package->mikrotik_remote_address;
        $this->push_to_mikrotik = $package->push_to_mikrotik;
        $this->router_name = $package->router_name;
        $this->features_text = collect($package->features ?? [])->pluck('value')->implode("\n");
    }

    public function deletePackage(int $id): void
    {
        $package = PackageList::findOrFail($id);

        // Remove from MikroTik routers first if push was enabled
        if ($package->push_to_mikrotik) {
            app(MikrotikController::class)->deleteProfileFromRouters($package->package, $package->router_name);
        }

        $package->delete();
        flash()->success('Package deleted successfully!');
        $this->dataRender();
    }

    public function updateSortOrder($reorder)
    {
        $this->packagesData = collect($reorder)->map(function ($pkg) {
            return collect($this->packagesData)->firstWhere('id', (int) $pkg['value']);
        })->toArray();
        flash()->addInfo('Package List Successfully Reordered. Click "Save Order" to persist.');
    }

    public function saveSortOrder()
    {
        foreach ($this->packagesData as $index => $field) {
            PackageList::where('id', $field['id'])->update(['sort_order' => $index + 1]);
        }
        flash()->success('Package order saved successfully!');
        $this->dataRender();
    }

    public function loadPools(): void
    {
        if (empty($this->router_name)) {
            flash()->warning('Please select a specific router first to load its IP pools.');

            return;
        }

        try {
            $routersPools = app(MikrotikController::class)->routerList(
                $this->router_name,
                '/ip/pool/print',
                '/ip pool print without-paging terse'
            );

            $uniquePools = [];
            foreach ($routersPools as $routerName => $result) {
                if (isset($result['status']) && $result['status'] && is_array($result['data'])) {
                    foreach ($result['data'] as $pool) {
                        if (isset($pool['name'])) {
                            $uniquePools[] = $pool['name'];
                        }
                    }
                } else {
                    // It might be an error from checkConnection
                    $message = $result['message'] ?? 'Unknown error';
                    flash()->error("[$routerName] $message");
                }
            }

            $this->mikrotik_pools = array_unique($uniquePools);
            if (empty($this->mikrotik_pools)) {
                flash()->warning('No IP pools found on the selected router.');
            } else {
                flash()->success('Loaded '.count($this->mikrotik_pools).' pool(s) from '.array_key_first($routersPools).'.');
            }
        } catch (\Exception $e) {
            flash()->error('Error loading pools: '.$e->getMessage());
        }
    }

    public function syncFromMikrotik(): void
    {
        \Log::info('PackageListSetup: Starting full Two-Way Synchronization.');
        try {
            $controller = app(MikrotikController::class);
            $profilesResults = $controller->routerList(
                null,
                '/ppp/profile/print',
                '/ppp profile print without-paging terse'
            );

            $syncedCount = 0;
            $restoredCount = 0;
            $errors = [];
            $existingOnRouter = []; // Track actual profiles: ['RouterName' => ['profile1', 'profile2']]

            // --- PHASE 1: Pull from MikroTik to Local DB ---
            foreach ($profilesResults as $routerName => $result) {
                if (! isset($result['status']) || ! $result['status'] || ! is_array($result['data'])) {
                    $errors[] = "$routerName: ".($result['message'] ?? 'Connection failed');

                    continue;
                }

                $routerProfiles = $result['data'];
                $existingOnRouter[$routerName] = [];

                foreach ($routerProfiles as $profile) {
                    $name = $profile['name'] ?? null;
                    if (! $name || $name === 'default' || $name === 'default-encryption') {
                        continue;
                    }

                    $existingOnRouter[$routerName][] = $name;

                    // Parse rate-limit to Mbps for local display
                    $rateLimit = $profile['rate-limit'] ?? null;
                    $speed = null;
                    if ($rateLimit) {
                        $parts = explode('/', $rateLimit);
                        $upload = trim($parts[0] ?? '');
                        $download = trim($parts[1] ?? $upload);
                        $speed = is_numeric($upload)
                            ? round($upload / 1048576, 1).'/'.round($download / 1048576, 1).' Mbps'
                            : "$upload / $download";
                    }

                    $localAddress = $profile['local-address'] ?? null;
                    $remoteAddress = $profile['remote-address'] ?? null;

                    $existing = PackageList::where('package', $name)->where('router_name', $routerName)->first();

                    PackageList::updateOrCreate(
                        ['package' => $name, 'router_name' => $routerName],
                        [
                            'speed' => $speed, // Source of truth: Router
                            'mikrotik_rate_limit' => $rateLimit, // Source of truth: Router
                            'mikrotik_local_address' => $localAddress, // Source of truth: Router
                            'mikrotik_remote_address' => $remoteAddress, // Source of truth: Router
                            'price' => $existing?->price ?? 0,
                            'description' => $existing?->description ?? null,
                            'plan_label' => $existing?->plan_label ?? null,
                            'features' => $existing?->features ?? [],
                            'is_featured' => $existing?->is_featured ?? false,
                            'show_on_site' => $existing?->show_on_site ?? false,
                            'sort_order' => $existing?->sort_order ?? 0,
                            'push_to_mikrotik' => $existing?->push_to_mikrotik ?? false,
                        ]
                    );

                    $syncedCount++;
                }
            }

            // --- PHASE 2: Push missing local packages to MikroTik ---
            $allConnectedRouters = array_keys(array_filter($profilesResults, fn ($r) => $r['status'] ?? false));
            $packagesToSync = PackageList::where('push_to_mikrotik', true)->get();

            foreach ($packagesToSync as $package) {
                // If package defines a specific router, use it. Otherwise, assume it should exist on ALL connected routers.
                $targetRouters = ! empty($package->router_name) ? [$package->router_name] : $allConnectedRouters;

                foreach ($targetRouters as $rName) {
                    // Skip if the router failed connection during pull
                    if (! isset($existingOnRouter[$rName])) {
                        continue;
                    }

                    // If profile is missing from this specific router, push it back
                    if (! in_array($package->package, $existingOnRouter[$rName])) {
                        $rateLimit = $package->mikrotik_rate_limit ?: null;
                        \Log::info("PackageListSetup: Restoring missing profile '{$package->package}' to router '{$rName}' (Push enabled)");
                        $controller->pushProfileToRouters($package->package, $rateLimit, $package->mikrotik_local_address, $package->mikrotik_remote_address, $rName);
                        $restoredCount++;
                    }
                }
            }

            // --- FINAL MESSAGING ---
            if (! empty($errors)) {
                flash()->warning('Sync partially completed: '.implode(', ', $errors));
            }

            $msg = "$syncedCount profile(s) imported/updated.";
            if ($restoredCount > 0) {
                $msg .= " AND $restoredCount missing profile(s) restored to MikroTik.";
            }
            flash()->success($msg);

            $this->dataRender();
        } catch (\Exception $e) {
            \Log::error('PackageListSetup: Two-Way Sync FAILED: '.$e->getMessage());
            flash()->error('Full sync failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->get();

        return view('livewire.package-list-setup', compact('routers'))->layout('layouts.app');
    }
}
