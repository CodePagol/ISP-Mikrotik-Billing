<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class OvpnSetup extends Component
{
    public string $selectedRouter = '';

    // Form fields
    public bool $enabled = false;

    public int $port = 1194;

    public string $mode = 'ip';

    public int $netmask = 24;

    public string $default_profile = 'default';

    public string $certificate = 'none';

    public bool $require_client_cert = false;

    public array $auth = ['sha1'];

    public array $cipher = ['aes128-cbc', 'aes256-cbc'];

    public string $protocol = 'tcp';

    public string $mac_address = '00:00:00:00:00:00';

    public int $max_mtu = 1500;

    public int $keepalive_timeout = 60;

    // Data lists
    public array $profiles = [];

    public array $certificates = [];

    public function mount(): void
    {
        $first = RouterList::where('action', 'connected')->first();
        if ($first) {
            $this->selectedRouter = $first->router_name;
            $this->loadData();
        }
    }

    public function updatedSelectedRouter(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        try {
            $ctrl = app(MikrotikController::class);

            // Load Config
            $config = $ctrl->getOvpnConfig($this->selectedRouter);
            if ($config) {
                $this->enabled = ($config['enabled'] ?? 'no') === 'yes';
                $this->port = (int) ($config['port'] ?? 1194);
                $this->mode = $config['mode'] ?? 'ip';
                $this->netmask = (int) ($config['netmask'] ?? 24);
                $this->default_profile = $config['default-profile'] ?? 'default';
                $this->certificate = $config['certificate'] ?? 'none';
                $this->require_client_cert = ($config['require-client-certificate'] ?? 'no') === 'yes';

                // Auth & Cipher (can be comma strings)
                $this->auth = is_string($config['auth'] ?? null) ? explode(',', $config['auth']) : (array) ($config['auth'] ?? ['sha1']);
                $this->cipher = is_string($config['cipher'] ?? null) ? explode(',', $config['cipher']) : (array) ($config['cipher'] ?? ['aes128-cbc']);

                // Extras
                $this->protocol = $config['protocol'] ?? 'tcp';
                $this->mac_address = $config['mac-address'] ?? '00:00:00:00:00:00';
                $this->max_mtu = (int) ($config['max-mtu'] ?? 1500);
                $this->keepalive_timeout = (int) ($config['keepalive-timeout'] ?? 60);
            }

            // Load extra data
            $this->profiles = collect($ctrl->getPppProfiles($this->selectedRouter))->pluck('name')->toArray();
            $this->certificates = collect($ctrl->getItems($this->selectedRouter, '/certificate'))->pluck('name')->toArray();

        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function save(): void
    {
        try {
            $ctrl = app(MikrotikController::class);
            $res = $ctrl->updateOvpnConfig($this->selectedRouter, [
                'enabled' => $this->enabled,
                'port' => $this->port,
                'mode' => $this->mode,
                'netmask' => $this->netmask,
                'default_profile' => $this->default_profile,
                'certificate' => $this->certificate,
                'require_client_cert' => $this->require_client_cert,
                'auth' => implode(',', $this->auth),
                'cipher' => implode(',', $this->cipher),
                'protocol' => $this->protocol,
                'mac_address' => $this->mac_address,
                'max_mtu' => $this->max_mtu,
                'keepalive_timeout' => $this->keepalive_timeout,
            ]);

            if ($res === 'success') {
                flash()->success('OpenVPN configuration updated!');
                $this->loadData();
            } else {
                flash()->error($res);
            }
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mikrotik.ovpn-setup', [
            'routers' => RouterList::where('action', 'connected')->get(),
        ]);
    }
}
