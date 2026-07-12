<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class RadiusSetup extends Component
{
    public string $selectedRouter = '';

    public string $r_address = '';

    public string $r_secret = '';

    public string $r_service = 'ppp';

    public int $r_auth_port = 1812;

    public int $r_acct_port = 1813;

    public int $r_timeout = 3000;

    public string $r_comment = '';

    public ?string $editRadiusId = null;

    public array $radiusServers = [];

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['mikrotik-setup'])) {
            abort(403);
        }
        $first = RouterList::where('action', 'connected')->first();
        if ($first) {
            $this->selectedRouter = $first->router_name;
            $this->loadData();
        }
    }

    public function updatedSelectedRouter(): void
    {
        $this->radiusServers = [];
        if ($this->selectedRouter) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        if (! $this->selectedRouter) {
            return;
        }
        try {
            $this->radiusServers = app(MikrotikController::class)->getRadiusServers($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function editServer(array $s): void
    {
        $this->editRadiusId = $s['.id'] ?? null;
        $this->r_address = $s['address'] ?? '';
        $this->r_secret = $s['secret'] ?? '';
        $this->r_service = $s['service'] ?? 'ppp';
        $this->r_auth_port = (int) ($s['authentication-port'] ?? 1812);
        $this->r_acct_port = (int) ($s['accounting-port'] ?? 1813);
        $this->r_timeout = isset($s['timeout']) ? (int) $s['timeout'] : 3000;
        $this->r_comment = $s['comment'] ?? '';
    }

    public function addServer(): void
    {
        $this->validate([
            'r_address' => 'required|ip',
            'r_secret' => 'required|string|max:100',
            'r_service' => 'required|string',
            'r_auth_port' => 'required|integer|min:1|max:65535',
            'r_acct_port' => 'required|integer|min:1|max:65535',
        ]);
        try {
            app(MikrotikController::class)->addRadiusServer($this->selectedRouter, [
                'address' => $this->r_address, 'secret' => $this->r_secret,
                'service' => $this->r_service, 'auth_port' => $this->r_auth_port,
                'acct_port' => $this->r_acct_port, 'timeout' => $this->r_timeout,
                'comment' => $this->r_comment,
            ], $this->editRadiusId);
            flash()->success($this->editRadiusId ? 'RADIUS server updated!' : 'RADIUS server added!');
            $this->reset(['r_address', 'r_secret', 'r_comment', 'editRadiusId']);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeServer(string $address): void
    {
        try {
            app(MikrotikController::class)->removeRadiusServer($this->selectedRouter, $address);
            flash()->success('RADIUS server removed.');
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function toggleServer(string $address, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleRadiusServer($this->selectedRouter, $address, $enable);
            flash()->success('RADIUS server '.($enable ? 'enabled' : 'disabled').'.');
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();

        return view('livewire.mikrotik.radius-setup', compact('routers'))->layout('layouts.app');
    }
}
