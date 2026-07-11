<?php

namespace App\Livewire\Portal;

use App\Http\Controllers\MikrotikController;
use Livewire\Component;

class PortalTrafficMonitor extends Component
{
    public string $selectedRouter = '';

    public string $selectedInterface = '';

    // UI Data
    public float $rxSpeed = 0;

    public float $txSpeed = 0;

    public float $lastPollTime = 0;

    public float $lastResolveTime = 0;

    public function mount(): void
    {
        $user = auth()->guard('ppp')->user();
        if ($user) {
            $this->selectedRouter = $user->router_name ?? '';
            $resolved = $this->resolveInterfaceName($this->selectedRouter, $user->username);
            if ($resolved) {
                $this->selectedInterface = $resolved;
            } else {
                // For PPPoE fallback, interface is usually <pppoe-USERNAME>
                $this->selectedInterface = '<pppoe-'.$user->username.'>';
            }
            $this->lastResolveTime = microtime(true);
        }
    }

    protected function resolveInterfaceName(string $routerName, string $username): ?string
    {
        if (!$routerName || !$username) {
            return null;
        }

        try {
            $ctrl = app(MikrotikController::class);
            $interfaces = $ctrl->getInterfaces($routerName);
            
            $lowerUser = strtolower($username);
            
            // Loop through all interfaces to find a match
            foreach ($interfaces as $iface) {
                $name = $iface['name'] ?? '';
                $lowerName = strtolower($name);
                
                if ($lowerName === "<pppoe-{$lowerUser}>" || 
                    $lowerName === "pppoe-{$lowerUser}" ||
                    $lowerName === "<l2tp-{$lowerUser}>" ||
                    $lowerName === "<sstp-{$lowerUser}>" ||
                    $lowerName === "<pptp-{$lowerUser}>" ||
                    $lowerName === "<ovpn-{$lowerUser}>" ||
                    $lowerName === $lowerUser
                ) {
                    return $name;
                }
            }
            
            // Fallback: look for any name containing the username inside brackets or containing username
            foreach ($interfaces as $iface) {
                $name = $iface['name'] ?? '';
                $lowerName = strtolower($name);
                if (str_contains($lowerName, $lowerUser)) {
                    return $name;
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
        
        return null;
    }

    public function poll(): void
    {
        // Throttle to prevent request stacking if server/mikrotik is slow
        if (! $this->selectedRouter || ! $this->selectedInterface) {
            return;
        }

        // Ensure at least 1.5s passed since last backend poll to prevent overlapping
        if (microtime(true) - $this->lastPollTime < 1.5) {
            return;
        }
        $this->lastPollTime = microtime(true);

        try {
            // Ensure session is still valid for guard 'ppp'
            if (! auth()->guard('ppp')->check()) {
                $this->redirect(route('login'));

                return;
            }

            $user = auth()->guard('ppp')->user();
            $ctrl = app(MikrotikController::class);

            // If the speeds are 0, try to resolve the interface name dynamically every 15 seconds
            if ($this->rxSpeed == 0 && $this->txSpeed == 0 && (microtime(true) - $this->lastResolveTime > 15)) {
                $this->lastResolveTime = microtime(true);
                $resolved = $this->resolveInterfaceName($this->selectedRouter, $user->username);
                if ($resolved && $resolved !== $this->selectedInterface) {
                    $this->selectedInterface = $resolved;
                }
            }

            $data = $ctrl->getLiveTraffic($this->selectedRouter, $this->selectedInterface);

            // Only update and dispatch if we got valid data
            if (isset($data['rx-bits-per-second']) || isset($data['tx-bits-per-second'])) {
                $this->rxSpeed = (float) ($data['rx-bits-per-second'] ?? 0);
                $this->txSpeed = (float) ($data['tx-bits-per-second'] ?? 0);
                $this->dispatch('traffic-updated', rx: $this->rxSpeed, tx: $this->txSpeed);
            }
        } catch (\Throwable $e) {
            // Silently log or ignore to prevent 500 error modal from disturbing the user
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.portal.portal-traffic-monitor');
    }
}
