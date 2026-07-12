<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class PppoeSetup extends Component
{
    public string $selectedRouter = '';

    public string $activeTab = 'servers';

    public string $srv_service_name = 'pppoe-server';

    public string $srv_interface = '';

    public int $srv_max_mtu = 1480;

    public int $srv_max_mru = 1480;

    public string $srv_mrru = 'disabled';

    public int $srv_keepalive = 10;

    public array $srv_authentication = ['mschap2', 'pap', 'chap', 'mschap1'];

    public string $srv_default_profile = 'default';

    public $editServerId = null;

    // Profiles form
    public string $prof_name = '';

    public string $prof_rate_limit = '';

    public string $prof_local_address = '';

    public string $prof_remote_address = '';

    public string $prof_comment = '';

    public ?string $editProfileId = null;

    // PPP Secret form
    public string $sec_name = '';

    public string $sec_password = '';

    public string $sec_profile = 'default';

    public string $sec_service = 'pppoe';

    public string $sec_comment = '';

    public ?string $editSecretId = null;

    // Data
    public array $pppoeServers = [];

    public array $pppProfiles = [];

    public array $pppSecrets = [];

    public array $activeSessions = [];

    public array $interfaces = [];

    public array $ipPools = [];

    public string $sec_local_address = '';

    public string $sec_remote_address = '';

    public string $sec_caller_id = '';

    // OVPN Server Form
    public string $ovpn_name = 'ovpn-server1';

    public bool $ovpn_enabled = false;

    public int $ovpn_port = 1194;

    public string $ovpn_mode = 'ip';

    public string $ovpn_protocol = 'tcp';

    public int $ovpn_netmask = 24;

    public string $ovpn_mac_address = '00:00:00:00:00:00';

    public int $ovpn_max_mtu = 1500;

    public int $ovpn_keepalive_timeout = 60;

    public string $ovpn_default_profile = 'default';

    public string $ovpn_certificate = 'none';

    public bool $ovpn_require_client_cert = false;

    public string $ovpn_tls_version = 'any';

    public int $ovpn_key_renegotiate_sec = 3600;

    public array $ovpn_redirect_gateway = ['disabled'];

    public string $ovpn_user_auth_method = 'pap';

    public array $ovpn_auth = ['sha1'];

    public array $ovpn_cipher = ['aes128-cbc', 'aes256-cbc'];

    public array $certificates = [];

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
        $this->loadData();
    }

    public function loadData(): void
    {
        if (! $this->selectedRouter) {
            return;
        }
        $ctrl = app(MikrotikController::class);
        try {
            $this->pppoeServers = $ctrl->getPppoeServers($this->selectedRouter);
            $this->pppProfiles = $ctrl->getPppProfiles($this->selectedRouter);
            $this->pppSecrets = $ctrl->getPppSecrets($this->selectedRouter);
            $this->activeSessions = $ctrl->getActivePppSessions($this->selectedRouter);
            $this->interfaces = collect($ctrl->getInterfaces($this->selectedRouter))->pluck('name')->filter()->values()->toArray();
            $this->ipPools = collect($ctrl->getIpPools($this->selectedRouter))->pluck('name')->filter()->values()->toArray();
            $this->certificates = collect($ctrl->getItems($this->selectedRouter, '/certificate'))->pluck('name')->toArray();

            if ($this->activeTab === 'ovpn') {
                $this->loadOvpn();
            }

            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function editPppoeServer(array $p): void
    {
        $this->editServerId = $p['.id'] ?? null;
        $this->srv_interface = $p['interface'] ?? '';
        $this->srv_service_name = $p['service-name'] ?? 'pppoe-server';
        $this->srv_max_mtu = (int) ($p['max-mtu'] ?? 1480);
        $this->srv_max_mru = (int) ($p['max-mru'] ?? 1480);
        $this->srv_mrru = $p['mrru'] ?? 'disabled';
        $this->srv_keepalive = (int) ($p['keepalive-timeout'] ?? 10);
        $this->srv_authentication = isset($p['authentication']) ? explode(',', $p['authentication']) : ['mschap2'];
        $this->srv_default_profile = $p['default-profile'] ?? 'default';
    }

    public function addPppoeServer(): void
    {
        $this->validate([
            'srv_interface' => 'required|string',
            'srv_service_name' => 'required|string|max:100',
        ]);
        try {
            $res = app(MikrotikController::class)->addPppoeServer($this->selectedRouter, [
                'interface' => $this->srv_interface,
                'service_name' => $this->srv_service_name,
                'max_mtu' => $this->srv_max_mtu,
                'max_mru' => $this->srv_max_mru,
                'mrru' => $this->srv_mrru,
                'keepalive' => $this->srv_keepalive,
                'authentication' => implode(',', $this->srv_authentication),
                'default_profile' => $this->srv_default_profile,
            ], $this->editServerId);

            if ($res && (is_string($res) && (str_contains(strtolower($res), 'error') || str_contains(strtolower($res), 'failure')))) {
                flash()->error('MikroTik Error: '.$res);

                return;
            }

            flash()->success($this->editServerId ? 'PPPoE Server updated!' : 'PPPoE Server added!');
            $this->reset(['srv_interface', 'srv_service_name', 'srv_mrru', 'srv_authentication', 'srv_default_profile', 'editServerId']);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removePppoeServer(string $name): void
    {
        try {
            $res = app(MikrotikController::class)->removePppoeServer($this->selectedRouter, $name);
            flash()->success('Server removal command sent.');
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    // --- Profile CRUD ---

    public function addProfile(): void
    {
        $this->validate([
            'prof_name' => 'required|string|max:100',
            'prof_rate_limit' => 'required|string|max:100',
        ]);

        try {
            $controller = app(MikrotikController::class);
            if ($this->editProfileId) {
                $res = $controller->updateProfileOnRouters(
                    $this->editProfileId,
                    $this->prof_name,
                    $this->prof_rate_limit,
                    $this->prof_local_address,
                    $this->prof_remote_address,
                    $this->selectedRouter,
                    $this->prof_comment
                );
            } else {
                $res = $controller->pushProfileToRouters(
                    $this->prof_name,
                    $this->prof_rate_limit,
                    $this->prof_local_address,
                    $this->prof_remote_address,
                    $this->selectedRouter,
                    $this->prof_comment
                );
            }

            // Check if returned result has errors
            $hasError = false;
            if (is_array($res)) {
                foreach ($res as $router => $output) {
                    // Check standard controller response status
                    if (isset($output['status']) && $output['status'] === false) {
                        $errorMsg = $output['message'] ?? 'Unknown Error';
                        if (isset($output['errors']['ssh'])) {
                            $errorMsg .= ' (SSH: '.$output['errors']['ssh'].')';
                        }
                        if (isset($output['errors']['api'])) {
                            $errorMsg .= ' (API: '.$output['errors']['api'].')';
                        }
                        flash()->error("Router $router: $errorMsg");
                        $hasError = true;
                    }
                    // Check for raw string errors if any
                    elseif (is_string($output) && (str_contains(strtolower($output), 'error') || str_contains(strtolower($output), 'failure'))) {
                        flash()->error("Router $router: $output");
                        $hasError = true;
                    }
                }
            } elseif (is_string($res) && (str_contains(strtolower($res), 'error') || str_contains(strtolower($res), 'failure'))) {
                flash()->error($res);
                $hasError = true;
            }

            if (! $hasError) {
                flash()->success($this->editProfileId ? 'PPP Profile updated!' : 'PPP Profile added!');
                $this->reset(['prof_name', 'prof_rate_limit', 'prof_local_address', 'prof_remote_address', 'prof_comment', 'editProfileId']);
                $this->loadData();
            }
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editProfile(array $p): void
    {
        $this->editProfileId = $p['name'] ?? null;
        $this->prof_name = $p['name'] ?? '';
        $this->prof_rate_limit = $p['rate-limit'] ?? '';
        $this->prof_local_address = $p['local-address'] ?? '';
        $this->prof_remote_address = $p['remote-address'] ?? '';
        $this->prof_comment = $p['comment'] ?? '';
        $this->activeTab = 'profiles';
    }

    public function removeProfile(string $name): void
    {
        try {
            $res = app(MikrotikController::class)->deleteProfileFromRouters($name, $this->selectedRouter);
            flash()->success('Profile removal command sent.');
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    // --- Secret CRUD ---

    public function addSecret(): void
    {
        $this->validate([
            'sec_name' => 'required|string|max:100',
            'sec_password' => 'required|string|max:100',
            'sec_profile' => 'required|string',
        ]);
        try {
            $res = app(MikrotikController::class)->addPppSecret($this->selectedRouter, [
                'name' => $this->sec_name,
                'password' => $this->sec_password,
                'profile' => $this->sec_profile,
                'service' => $this->sec_service,
                'comment' => $this->sec_comment,
                'local_address' => $this->sec_local_address,
                'remote_address' => $this->sec_remote_address,
                'caller_id' => $this->sec_caller_id,
            ], $this->editSecretId);

            if ($res && is_string($res) && (str_contains(strtolower($res), 'error') || str_contains(strtolower($res), 'failure'))) {
                flash()->error('MikroTik Error: '.$res);

                return;
            }

            flash()->success($this->editSecretId ? 'PPP Secret updated!' : 'PPP Secret added!');
            $this->reset(['sec_name', 'sec_password', 'sec_profile', 'sec_comment', 'editSecretId', 'sec_service', 'sec_local_address', 'sec_remote_address', 'sec_caller_id']);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editSecret(array $s): void
    {
        $this->editSecretId = $s['.id'] ?? null;
        $this->sec_name = $s['name'] ?? '';
        $this->sec_password = $s['password'] ?? '';
        $this->sec_profile = $s['profile'] ?? 'default';
        $this->sec_service = $s['service'] ?? 'pppoe';
        $this->sec_comment = $s['comment'] ?? '';
        $this->sec_local_address = $s['local-address'] ?? '';
        $this->sec_remote_address = $s['remote-address'] ?? '';
        $this->sec_caller_id = $s['caller-id'] ?? '';
    }

    public function removeSecret(string $name): void
    {
        try {
            app(MikrotikController::class)->deletePppSecret($this->selectedRouter, $name);
            flash()->success('PPP Secret removed!');
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function loadOvpn(): void
    {
        try {
            $config = app(MikrotikController::class)->getOvpnConfig($this->selectedRouter);
            if ($config && count($config) > 0) {
                $this->ovpn_name = $config['name'] ?? 'ovpn-server1';
                $this->ovpn_enabled = ($config['enabled'] ?? 'no') === 'yes';
                $this->ovpn_port = (int) ($config['port'] ?? 1194);
                $this->ovpn_mode = $config['mode'] ?? 'ip';
                $this->ovpn_protocol = $config['protocol'] ?? 'tcp';
                $this->ovpn_netmask = (int) ($config['netmask'] ?? 24);
                $this->ovpn_mac_address = $config['mac-address'] ?? '00:00:00:00:00:00';
                $this->ovpn_max_mtu = (int) ($config['max-mtu'] ?? 1500);
                $this->ovpn_keepalive_timeout = (int) ($config['keepalive-timeout'] ?? 60);
                $this->ovpn_default_profile = $config['default-profile'] ?? 'default';
                $this->ovpn_certificate = $config['certificate'] ?? 'none';
                $this->ovpn_require_client_cert = ($config['require-client-certificate'] ?? 'no') === 'yes';
                $this->ovpn_tls_version = $config['tls-version'] ?? 'any';
                $this->ovpn_key_renegotiate_sec = (int) ($config['key-renegotiation-interval'] ?? 3600);
                $this->ovpn_redirect_gateway = is_string($config['redirect-gateway'] ?? null)
                    ? explode(',', $config['redirect-gateway'])
                    : (array) ($config['redirect-gateway'] ?? ['disabled']);
                $this->ovpn_user_auth_method = $config['user-auth-method'] ?? 'pap';

                $this->ovpn_auth = is_string($config['auth'] ?? null) ? explode(',', $config['auth']) : (array) ($config['auth'] ?? ['sha1']);
                $this->ovpn_cipher = is_string($config['cipher'] ?? null) ? explode(',', $config['cipher']) : (array) ($config['cipher'] ?? ['aes128-cbc']);
            }
        } catch (\Exception $e) {
            flash()->error('OVPN Load: '.$e->getMessage());
        }
    }

    public function saveOvpn(): void
    {
        if (! $this->selectedRouter) {
            flash()->error('No router selected!');

            return;
        }

        try {
            // Ensure we have at least one auth/cipher to avoid command errors
            $auth = array_filter($this->ovpn_auth);
            $cipher = array_filter($this->ovpn_cipher);

            $res = app(MikrotikController::class)->updateOvpnConfig($this->selectedRouter, [
                'enabled' => $this->ovpn_enabled,
                'port' => $this->ovpn_port,
                'mode' => $this->ovpn_mode,
                'protocol' => $this->ovpn_protocol,
                'netmask' => $this->ovpn_netmask,
                'mac_address' => $this->ovpn_mac_address,
                'max_mtu' => $this->ovpn_max_mtu,
                'keepalive_timeout' => $this->ovpn_keepalive_timeout,
                'default_profile' => $this->ovpn_default_profile,
                'certificate' => $this->ovpn_certificate,
                'require_client_cert' => $this->ovpn_require_client_cert,
                'tls_version' => $this->ovpn_tls_version,
                'key_renegotiate_sec' => $this->ovpn_key_renegotiate_sec,
                'redirect_gateway' => implode(',', array_filter($this->ovpn_redirect_gateway)),
                'user_auth_method' => $this->ovpn_user_auth_method,
                'auth' => ! empty($auth) ? implode(',', $auth) : 'sha1',
                'cipher' => ! empty($cipher) ? implode(',', $cipher) : 'aes128-cbc',
            ]);

            if ($res === 'success') {
                flash()->success('OpenVPN configuration updated successfully!');
                $this->loadOvpn(); // Refresh fields from router
            } else {
                flash()->error('Router Error: '.$res);
            }
        } catch (\Exception $e) {
            flash()->error('System Error: '.$e->getMessage());
        }
    }

    public function refreshSessions(): void
    {
        $this->loadData();
        flash()->success('Active sessions refreshed.');
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->get();

        return view('livewire.mikrotik.pppoe-setup', compact('routers'))->layout('layouts.app');
    }
}
