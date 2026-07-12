<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class InterfaceSetup extends Component
{
    public string $selectedRouter = '';

    public string $activeTab = 'interfaces';

    // VLAN form
    public string $vlan_name = '';

    public int $vlan_id = 1;

    public string $vlan_interface = '';

    public string $vlan_comment = '';

    public ?string $editVlanId = null;

    // Bridge form
    public string $bridge_name = '';

    public string $bridge_comment = '';

    public ?string $editBridgeId = null;

    // Data
    public array $interfaces = [];

    public array $vlans = [];

    public array $bridges = [];

    public array $bridgePorts = [];

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
        $this->resetData();
        if ($this->selectedRouter) {
            $this->loadData();
        }
    }

    public function loadData(): void
    {
        if (! $this->selectedRouter) {
            return;
        }
        $ctrl = app(MikrotikController::class);
        try {
            $this->interfaces = $ctrl->getInterfaces($this->selectedRouter);
            $this->vlans = $ctrl->getVlans($this->selectedRouter);
            $this->bridges = $ctrl->getBridges($this->selectedRouter);
            $this->bridgePorts = $ctrl->getBridgePorts($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function toggleInterface(string $name, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleInterface($this->selectedRouter, $name, $enable);
            flash()->success("Interface '{$name}' ".($enable ? 'enabled' : 'disabled').'.');
            $this->interfaces = app(MikrotikController::class)->getInterfaces($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editVlan(array $vlan): void
    {
        $this->editVlanId = $vlan['.id'] ?? null;
        $this->vlan_name = $vlan['name'] ?? '';
        $this->vlan_id = (int) ($vlan['vlan-id'] ?? 1);
        $this->vlan_interface = $vlan['interface'] ?? '';
        $this->vlan_comment = $vlan['comment'] ?? '';
    }

    public function addVlan(): void
    {
        $this->validate([
            'vlan_name' => 'required|string|max:100',
            'vlan_id' => 'required|integer|min:1|max:4094',
            'vlan_interface' => 'required|string',
        ]);
        try {
            app(MikrotikController::class)->addVlan(
                $this->selectedRouter, $this->vlan_name,
                $this->vlan_id, $this->vlan_interface, $this->vlan_comment ?: null, $this->editVlanId
            );
            flash()->success($this->editVlanId ? 'VLAN updated!' : 'VLAN added!');
            $this->reset(['vlan_name', 'vlan_comment', 'editVlanId']);
            $this->vlan_id = 1;
            $this->vlans = app(MikrotikController::class)->getVlans($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeVlan(string $name): void
    {
        try {
            app(MikrotikController::class)->removeVlan($this->selectedRouter, $name);
            flash()->success('VLAN removed.');
            $this->vlans = app(MikrotikController::class)->getVlans($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function addBridge(): void
    {
        $this->validate(['bridge_name' => 'required|string|max:100']);
        try {
            app(MikrotikController::class)->addBridge($this->selectedRouter, $this->bridge_name, $this->bridge_comment ?: null);
            flash()->success('Bridge added!');
            $this->reset(['bridge_name', 'bridge_comment']);
            $this->bridges = app(MikrotikController::class)->getBridges($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeBridge(string $name): void
    {
        try {
            app(MikrotikController::class)->removeBridge($this->selectedRouter, $name);
            flash()->success('Bridge removed.');
            $this->bridges = app(MikrotikController::class)->getBridges($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    private function resetData(): void
    {
        $this->interfaces = $this->vlans = $this->bridges = $this->bridgePorts = [];
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();

        return view('livewire.mikrotik.interface-setup', compact('routers'))->layout('layouts.app');
    }
}
