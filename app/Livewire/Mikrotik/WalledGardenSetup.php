<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\PermittedUrl;
use App\Models\RouterList;
use App\Models\MainSiteData;
use Livewire\Component;

class WalledGardenSetup extends Component
{
    public string $selectedRouter = '';

    public string $url_or_ip = '';

    public string $type = 'url';

    public string $comment = '';

    // Setup fields
    public string $portal_ip = '';

    public string $expired_speed = '128k/128k';

    public string $setup_type = 'direct';

    public string $redirection_domain = '';

    // Dynamic profiles list and selections
    public array $routerProfiles = [];

    public string $expired_profile_name = 'Expired';

    public string $custom_profile_name = '';

    public bool $showCustomProfileInput = false;

    public function mount(): void
    {
        $first = RouterList::where('action', 'connected')->first();
        if ($first) {
            $this->selectedRouter = (string) $first->id;
        }
        $this->portal_ip = request()->server('SERVER_ADDR') ?? '';
        $this->redirection_domain = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');
        $this->loadRouterProfiles();
    }

    public function updatedSelectedRouter($value)
    {
        $this->loadRouterProfiles();
    }

    public function updatedExpiredProfileName($value)
    {
        if ($value === 'custom_new_profile') {
            $this->showCustomProfileInput = true;
            $this->custom_profile_name = '';
        } else {
            $this->showCustomProfileInput = false;
        }
    }

    public function loadRouterProfiles()
    {
        $this->routerProfiles = [];
        if (! $this->selectedRouter) {
            return;
        }

        $router = RouterList::find($this->selectedRouter);
        if (! $router || $router->action !== 'connected') {
            return;
        }

        try {
            $ctrl = app(MikrotikController::class);
            $profiles = $ctrl->singleWrite($router->router_name, '/ppp/profile/print');
            if (is_array($profiles)) {
                foreach ($profiles as $profile) {
                    if (isset($profile['name'])) {
                        $this->routerProfiles[] = $profile['name'];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to load profiles for walled garden setup: " . $e->getMessage());
        }

        // Add saved profile setting to the selection if not in list
        $saved = siteUrlSettings('expired_profile_name') ?? 'Expired';
        if (! in_array($saved, $this->routerProfiles)) {
            $this->routerProfiles[] = $saved;
        }
        $this->expired_profile_name = $saved;
        $this->showCustomProfileInput = false;
    }

    public function addPermitted()
    {
        $this->validate([
            'selectedRouter' => 'required',
            'url_or_ip' => 'required',
            'type' => 'required|in:url,ip',
        ]);

        $router = RouterList::find($this->selectedRouter);
        if (! $router) {
            flash()->error('Router not found.');

            return;
        }

        try {
            $ctrl = app(MikrotikController::class);
            $routerName = $router->router_name;

            // 1. Add to Hotspot Walled Garden
            try {
                if ($this->type == 'url') {
                    $ctrl->singleWrite($routerName, "/ip hotspot walled-garden add dst-host={$this->url_or_ip} comment=\"{$this->comment}\"");
                } else {
                    $ctrl->singleWrite($routerName, "/ip hotspot walled-garden ip add dst-address={$this->url_or_ip} action=accept comment=\"{$this->comment}\"");
                }
            } catch (\Exception $e) {
                // Ignore if it already exists or if hotspot package is missing
                if (! str_contains($e->getMessage(), 'already has') && ! str_contains($e->getMessage(), 'already exists') && ! str_contains($e->getMessage(), 'no such item')) {
                    throw $e;
                }
            }

            // 2. Add to Firewall Address List for PPPoE redirection bypass
            try {
                $ctrl->singleWrite($routerName, "/ip firewall address-list add list=PERMITTED_URLS address={$this->url_or_ip} comment=\"{$this->comment}\"");
            } catch (\Exception $e) {
                // Ignore if it already exists
                if (! str_contains($e->getMessage(), 'already has') && ! str_contains($e->getMessage(), 'already exists')) {
                    throw $e;
                }
            }

            PermittedUrl::create([
                'router_id' => $router->id,
                'url_or_ip' => $this->url_or_ip,
                'type' => $this->type,
                'comment' => $this->comment,
            ]);

            flash()->success('Permitted URL/IP added and synced to Mikrotik router.');
            $this->reset(['url_or_ip', 'type', 'comment']);

        } catch (\Exception $e) {
            flash()->error('Mikrotik sync error: '.$e->getMessage());
        }
    }

    public function deletePermitted($id)
    {
        $permitted = PermittedUrl::find($id);
        if (! $permitted) {
            return;
        }

        $router = RouterList::find($permitted->router_id);
        if ($router) {
            try {
                $ctrl = app(MikrotikController::class);
                $routerName = $router->router_name;

                // Remove from Hotspot Walled Garden
                try {
                    if ($permitted->type == 'url') {
                        $ctrl->singleWrite($routerName, "/ip hotspot walled-garden remove [/ip hotspot walled-garden find dst-host=\"{$permitted->url_or_ip}\"]");
                    } else {
                        $ctrl->singleWrite($routerName, "/ip hotspot walled-garden ip remove [/ip hotspot walled-garden ip find dst-address=\"{$permitted->url_or_ip}\"]");
                    }
                } catch (\Exception $e) {
                    // Ignore "no such item" errors on delete
                }

                // Remove from Firewall Address List
                try {
                    $ctrl->singleWrite($routerName, "/ip firewall address-list remove [/ip firewall address-list find address=\"{$permitted->url_or_ip}\" list=PERMITTED_URLS]");
                } catch (\Exception $e) {
                    // Ignore "no such item" errors on delete
                }

            } catch (\Exception $e) {
                flash()->error('Failed to remove from Mikrotik router: '.$e->getMessage());

                return;
            }
        }

        $permitted->delete();
        flash()->success('Permitted URL/IP removed.');
    }

    public function runRouterSetup()
    {
        $rules = [
            'selectedRouter' => 'required',
            'expired_speed' => 'required',
            'setup_type' => 'required|in:direct,proxy',
        ];

        if ($this->setup_type === 'direct') {
            $rules['portal_ip'] = 'required|ip';
        } else {
            $rules['redirection_domain'] = 'required|string|max:255';
        }

        $this->validate($rules);

        $router = RouterList::find($this->selectedRouter);
        if (! $router) {
            flash()->error('Router not found.');

            return;
        }

        $expiredProfile = $this->expired_profile_name;
        if ($expiredProfile === 'custom_new_profile') {
            $this->validate([
                'custom_profile_name' => 'required|min:3|max:50',
            ]);
            $expiredProfile = trim($this->custom_profile_name);
        }

        try {
            $ctrl = app(MikrotikController::class);
            $routerName = $router->router_name;

            // 1. Create/Update PPP Profile
            try {
                // Try to add, catch if exists
                $ctrl->singleWrite($routerName, "/ppp profile add name=\"{$expiredProfile}\" rate-limit=\"{$this->expired_speed}\" address-list=EXPIRED_USERS comment=\"Managed by ISP Billing\"");
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'already has') || str_contains($e->getMessage(), 'already exists')) {
                    $ctrl->singleWrite($routerName, "/ppp profile set [find name=\"{$expiredProfile}\"] rate-limit=\"{$this->expired_speed}\" address-list=EXPIRED_USERS");
                } else {
                    throw $e;
                }
            }

            // 2. Create/Update Hotspot User Profile
            try {
                $ctrl->singleWrite($routerName, "/ip hotspot user profile add name=\"{$expiredProfile}\" address-list=EXPIRED_USERS rate-limit=\"{$this->expired_speed}\" comment=\"Managed by ISP Billing\"");
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'already has') || str_contains($e->getMessage(), 'already exists')) {
                    $ctrl->singleWrite($routerName, "/ip hotspot user profile set [find name=\"{$expiredProfile}\"] address-list=EXPIRED_USERS rate-limit=\"{$this->expired_speed}\"");
                } elseif (str_contains($e->getMessage(), 'no such item')) {
                    // Hotspot package likely missing, ignore
                } else {
                    throw $e;
                }
            }

            // 3. Configure Redirection NAT/Proxy Rules
            // First, remove old redirect rule to avoid duplicates
            try {
                $ctrl->singleWrite($routerName, '/ip firewall nat remove [find comment="Redirect Expired Users"]');
            } catch (\Exception $e) {
            }

            if ($this->setup_type === 'direct') {
                // Direct IP Redirect NAT
                $ctrl->singleWrite($routerName, "/ip firewall nat add chain=dstnat src-address-list=EXPIRED_USERS dst-address-list=!PERMITTED_URLS protocol=tcp dst-port=80 action=dst-nat to-addresses=\"{$this->portal_ip}\" to-ports=80 comment=\"Redirect Expired Users\"");
            } else {
                // Web Proxy Redirect
                // Clean the user input domain (remove http://, https:// and trailing slashes)
                $domain = preg_replace('/^https?:\/\//', '', trim($this->redirection_domain));
                $domain = rtrim($domain, '/');

                // Enable IP proxy (try proxy, fallback to web-proxy)
                try {
                    $ctrl->singleWrite($routerName, "/ip proxy set enabled=yes port=8080");
                } catch (\Exception $proxyErr) {
                    $ctrl->singleWrite($routerName, "/ip web-proxy set enabled=yes port=8080");
                }

                // Clean existing proxy access rules by comment
                try {
                    $ctrl->singleWrite($routerName, '/ip proxy access remove [find comment="Redirect Expired Users"]');
                } catch (\Exception $e) {
                    try {
                        $ctrl->singleWrite($routerName, '/ip web-proxy access remove [find comment="Redirect Expired Users"]');
                    } catch (\Exception $e2) {
                    }
                }

                // Add proxy access deny and redirect rule
                try {
                    $ctrl->singleWrite($routerName, "/ip proxy access add dst-host=\"!*{$domain}*\" action=deny redirect-to=\"http://{$domain}/warning\" comment=\"Redirect Expired Users\"");
                } catch (\Exception $proxyAddErr) {
                    $ctrl->singleWrite($routerName, "/ip web-proxy access add dst-host=\"!*{$domain}*\" action=deny redirect-to=\"http://{$domain}/warning\" comment=\"Redirect Expired Users\"");
                }

                // Redirect HTTP traffic of EXPIRED_USERS to local proxy port 8080
                $ctrl->singleWrite($routerName, "/ip firewall nat add chain=dstnat src-address-list=EXPIRED_USERS dst-address-list=!PERMITTED_URLS protocol=tcp dst-port=80 action=redirect to-ports=8080 comment=\"Redirect Expired Users\"");
            }

            // Save settings to DB
            MainSiteData::setValue('expired_profile_name', $expiredProfile);

            // Reload profiles list
            $this->loadRouterProfiles();

            flash()->success('Router redirection setup completed successfully!');

        } catch (\Exception $e) {
            flash()->error('Router setup failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        $urls = [];
        if ($this->selectedRouter) {
            $urls = PermittedUrl::where('router_id', $this->selectedRouter)->get();
        }

        return view('livewire.mikrotik.walled-garden-setup', [
            'routers' => RouterList::where('action', 'connected')->get(),
            'urls' => $urls,
        ])->layout('layouts.app', ['title' => 'Walled Garden Setup']);
    }
}
