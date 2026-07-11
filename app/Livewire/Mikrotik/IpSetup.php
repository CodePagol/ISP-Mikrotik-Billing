<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class IpSetup extends Component
{
    public string $selectedRouter = '';

    public string $activeTab = 'addresses';

    // IP Address form
    public string $addr_address = '';

    public string $addr_interface = '';

    public string $addr_comment = '';

    public ?string $editAddressId = null;

    // IP Pool form
    public string $pool_name = '';

    public string $pool_ranges = '';

    public string $pool_next_pool = '';

    public string $pool_comment = '';

    public ?string $editPoolId = null;

    // DHCP Server form
    public string $dhcp_name = '';

    public string $dhcp_interface = '';

    public string $dhcp_pool = 'static-only';

    public string $dhcp_lease = '00:10:00';

    public string $dhcp_comment = '';

    public ?string $editDhcpId = null;

    // DHCP Network form
    public string $net_address = '';

    public string $net_gateway = '';

    public string $net_dns = '';

    public string $net_comment = '';

    public ?string $editNetId = null;

    public string $net_pool = '';

    // Search and Pagination
    public string $searchDhcp = '';

    // Data
    public array $ipAddresses = [];

    public array $ipPools = [];

    public array $dhcpServers = [];

    public array $dhcpNetworks = [];

    public array $interfaces = [];

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
            $this->ipAddresses = $ctrl->getIpAddresses($this->selectedRouter);
            $this->ipPools = $ctrl->getIpPools($this->selectedRouter);
            $this->dhcpServers = $ctrl->getDhcpServers($this->selectedRouter);
            $this->dhcpNetworks = $ctrl->getDhcpNetworks($this->selectedRouter);
            $this->interfaces = collect($ctrl->getInterfaces($this->selectedRouter))->pluck('name')->filter()->values()->toArray();

        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function editAddress(array $addr): void
    {
        $this->editAddressId = $addr['.id'] ?? null;
        $this->addr_address = $addr['address'] ?? '';
        $this->addr_interface = $addr['interface'] ?? '';
        $this->addr_comment = $addr['comment'] ?? '';
    }

    public function addAddress(): void
    {
        $this->validate([
            'addr_address' => 'required|regex:/^\d+\.\d+\.\d+\.\d+\/\d+$/',
            'addr_interface' => 'required|string',
        ]);
        try {
            app(MikrotikController::class)->addIpAddress($this->selectedRouter, $this->addr_address, $this->addr_interface, $this->addr_comment ?: null, $this->editAddressId);
            flash()->success($this->editAddressId ? 'IP Address updated!' : 'IP Address added!');
            $this->reset(['addr_address', 'addr_interface', 'addr_comment', 'editAddressId']);
            $this->ipAddresses = app(MikrotikController::class)->getIpAddresses($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeAddress(string $address): void
    {
        try {
            app(MikrotikController::class)->removeIpAddress($this->selectedRouter, $address);
            flash()->success('IP Address removed.');
            $this->ipAddresses = app(MikrotikController::class)->getIpAddresses($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function addPool(): void
    {
        $this->validate([
            'pool_name' => 'required|string|max:100',
            'pool_ranges' => 'required|string|max:255',
        ]);
        try {
            $ctrl = app(MikrotikController::class);
            $ctrl->addIpPool($this->selectedRouter, $this->pool_name, $this->pool_ranges, $this->pool_next_pool ?: null, $this->editPoolId, $this->pool_comment);
            flash()->success($this->editPoolId ? 'Pool updated!' : 'Pool added!');
            $this->reset(['pool_name', 'pool_ranges', 'pool_next_pool', 'pool_comment', 'editPoolId']);
            $this->ipPools = $ctrl->getIpPools($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editPool(array $pool): void
    {
        $this->editPoolId = $pool['.id'] ?? null;
        $this->pool_name = $pool['name'] ?? '';
        $this->pool_ranges = $pool['ranges'] ?? '';
        $this->pool_next_pool = $pool['next-pool'] ?? '';
        $this->pool_comment = $pool['comment'] ?? '';
    }

    public function removePool(string $name): void
    {
        try {
            app(MikrotikController::class)->removeIpPool($this->selectedRouter, $name);
            flash()->success('Pool removed.');
            $this->ipPools = app(MikrotikController::class)->getIpPools($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    // DHCP Server Methods
    public function editDhcpServer(array $srv): void
    {
        $this->editDhcpId = $srv['.id'] ?? null;
        $this->dhcp_name = $srv['name'] ?? '';
        $this->dhcp_interface = $srv['interface'] ?? '';
        $this->dhcp_pool = $srv['address-pool'] ?? 'static-only';
        $this->dhcp_lease = $srv['lease-time'] ?? '00:10:00';
        $this->dhcp_comment = $srv['comment'] ?? '';
    }

    public function addDhcpServer(): void
    {
        $this->validate([
            'dhcp_name' => 'required|string|max:100',
            'dhcp_interface' => 'required|string',
        ]);
        try {
            app(MikrotikController::class)->addDhcpServer($this->selectedRouter, [
                'name' => $this->dhcp_name,
                'interface' => $this->dhcp_interface,
                'address_pool' => $this->dhcp_pool,
                'lease_time' => $this->dhcp_lease,
                'comment' => $this->dhcp_comment,
            ], $this->editDhcpId);
            flash()->success($this->editDhcpId ? 'DHCP Server updated!' : 'DHCP Server added!');
            $this->reset(['dhcp_name', 'dhcp_comment', 'editDhcpId']);
            $this->dhcpServers = app(MikrotikController::class)->getDhcpServers($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeDhcpServer(string $name): void
    {
        try {
            app(MikrotikController::class)->removeDhcpServer($this->selectedRouter, $name);
            flash()->success('DHCP Server removed.');
            $this->dhcpServers = app(MikrotikController::class)->getDhcpServers($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function toggleDhcpServer(string $name, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleDhcpServer($this->selectedRouter, $name, $enable);
            flash()->success('DHCP Server '.($enable ? 'enabled' : 'disabled'));
            $this->dhcpServers = app(MikrotikController::class)->getDhcpServers($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    // DHCP Network Methods
    public function editDhcpNetwork(array $net): void
    {
        $this->editNetId = $net['.id'] ?? null;
        $this->net_address = $net['address'] ?? '';
        $this->net_gateway = $net['gateway'] ?? '';
        $this->net_dns = $net['dns-server'] ?? '';
        $this->net_comment = $net['comment'] ?? '';
        $this->net_pool = ''; // Reset pool selector on edit
    }

    public function updatedNetPool(): void
    {
        if (! $this->net_pool) {
            return;
        }

        $pool = collect($this->ipPools)->firstWhere('name', $this->net_pool);
        if ($pool && ! empty($pool['ranges'])) {
            // Very basic logic to guess network from first range
            $range = explode('-', $pool['ranges'])[0];
            $parts = explode('.', $range);
            if (count($parts) === 4) {
                $this->net_address = "{$parts[0]}.{$parts[1]}.{$parts[2]}.0/24";
                $this->net_gateway = "{$parts[0]}.{$parts[1]}.{$parts[2]}.1";
            }
        }
    }

    public function addDhcpNetwork(): void
    {
        $this->validate([
            'net_address' => 'required|regex:/^\d+\.\d+\.\d+\.\d+\/\d+$/',
        ]);
        try {
            app(MikrotikController::class)->addDhcpNetwork($this->selectedRouter, [
                'address' => $this->net_address,
                'gateway' => $this->net_gateway,
                'dns_server' => $this->net_dns,
                'comment' => $this->net_comment,
            ], $this->editNetId);
            flash()->success($this->editNetId ? 'DHCP Network updated!' : 'DHCP Network added!');
            $this->reset(['net_address', 'net_gateway', 'net_dns', 'net_comment', 'net_pool', 'editNetId']);
            $this->dhcpNetworks = app(MikrotikController::class)->getDhcpNetworks($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeDhcpNetwork(string $address): void
    {
        try {
            app(MikrotikController::class)->removeDhcpNetwork($this->selectedRouter, $address);
            flash()->success('DHCP Network removed.');
            $this->dhcpNetworks = app(MikrotikController::class)->getDhcpNetworks($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    private function resetData(): void
    {
        $this->ipAddresses = $this->ipPools = $this->dhcpServers = $this->dhcpNetworks = $this->interfaces = [];
    }

    public function render()
    {
        return view('livewire.mikrotik.ip-setup', [
            'routers' => RouterList::where('action', 'connected')->orderBy('router_name')->get(),
        ])->layout('layouts.app');
    }
}
