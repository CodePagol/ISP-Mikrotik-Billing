<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class TrafficMonitor extends Component
{
    public string $selectedRouter = '';

    public string $selectedInterface = '';

    public array $interfaces = [];

    // UI Data
    public float $rxSpeed = 0;

    public float $txSpeed = 0;

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['mikrotik-setup'])) {
            abort(403);
        }
        $first = RouterList::where('action', 'connected')->first();
        if ($first) {
            $this->selectedRouter = $first->router_name;
            $this->loadInterfaces();
        }
    }

    public function updatedSelectedRouter(): void
    {
        $this->interfaces = [];
        $this->selectedInterface = '';
        if ($this->selectedRouter) {
            $this->loadInterfaces();
        }
    }

    public function updatedSelectedInterface(): void
    {
        // Reset chart when changing interface
        $this->rxSpeed = 0;
        $this->txSpeed = 0;
        $this->dispatch('reset-chart');
    }

    public function loadInterfaces(): void
    {
        try {
            $ctrl = app(MikrotikController::class);
            // Fetch interfaces (including dynamic PPPoE/Hotspot users)
            $this->interfaces = collect($ctrl->getInterfaces($this->selectedRouter))
                ->map(fn ($i) => $i['name'] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (count($this->interfaces) > 0 && empty($this->selectedInterface)) {
                // Try to find a main interface like 'ether1' by default, otherwise first
                $this->selectedInterface = collect($this->interfaces)->first(fn ($i) => str_contains($i, 'ether')) ?? $this->interfaces[0];
            }
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function poll(): void
    {
        if (! $this->selectedRouter || ! $this->selectedInterface) {
            return;
        }

        try {
            $ctrl = app(MikrotikController::class);
            $data = $ctrl->getLiveTraffic($this->selectedRouter, $this->selectedInterface);
            $this->rxSpeed = (float) ($data['rx-bits-per-second'] ?? 0);
            $this->txSpeed = (float) ($data['tx-bits-per-second'] ?? 0);

            // Dispatch to frontend for JS graph
            $this->dispatch('traffic-updated', rx: $this->rxSpeed, tx: $this->txSpeed);
        } catch (\Exception $e) {
            // silent fail during polling
        }
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();

        return view('livewire.mikrotik.traffic-monitor', compact('routers'))->layout('layouts.app');
    }
}
