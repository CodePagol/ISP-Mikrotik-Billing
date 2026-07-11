<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class VpnSetup extends Component
{
    public string $selectedRouter = '';

    public string $activeTab = 'l2tp';

    // L2TP
    public bool $l2tp_enabled = false;

    public string $l2tp_profile = 'default';

    public string $l2tp_auth = 'mschap2';

    public string $l2tp_ipsec_secret = '';

    // PPTP
    public bool $pptp_enabled = false;

    public string $pptp_profile = 'default';

    public string $pptp_auth = 'mschap2';

    // SSTP
    public bool $sstp_enabled = false;

    public string $sstp_profile = 'default';

    public int $sstp_port = 443;

    // Data
    public array $l2tpStatus = [];

    public array $pptpStatus = [];

    public array $sstpStatus = [];

    public array $activeSessions = [];

    public array $pppProfiles = [];

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
            $this->l2tpStatus = $ctrl->getL2tpStatus($this->selectedRouter);
            $this->pptpStatus = $ctrl->getPptpStatus($this->selectedRouter);
            $this->sstpStatus = $ctrl->getSstpStatus($this->selectedRouter);
            $this->activeSessions = $ctrl->getActivePppSessions($this->selectedRouter);
            $this->pppProfiles = $ctrl->getPppProfiles($this->selectedRouter);

            if (! empty($this->l2tpStatus[0])) {
                $s = $this->l2tpStatus[0];
                $this->l2tp_enabled = ($s['enabled'] ?? 'no') === 'yes';
                $this->l2tp_profile = $s['default-profile'] ?? 'default';
            }
            if (! empty($this->pptpStatus[0])) {
                $s = $this->pptpStatus[0];
                $this->pptp_enabled = ($s['enabled'] ?? 'no') === 'yes';
                $this->pptp_profile = $s['default-profile'] ?? 'default';
            }
            if (! empty($this->sstpStatus[0])) {
                $s = $this->sstpStatus[0];
                $this->sstp_enabled = ($s['enabled'] ?? 'no') === 'yes';
                $this->sstp_profile = $s['default-profile'] ?? 'default';
                $this->sstp_port = (int) ($s['port'] ?? 443);
            }
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function saveL2tp(): void
    {
        try {
            app(MikrotikController::class)->setL2tpServer(
                $this->selectedRouter, $this->l2tp_enabled,
                $this->l2tp_profile, $this->l2tp_auth,
                $this->l2tp_ipsec_secret ?: null
            );
            flash()->success('L2TP settings saved!');
            $this->l2tpStatus = app(MikrotikController::class)->getL2tpStatus($this->selectedRouter);
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function savePptp(): void
    {
        try {
            app(MikrotikController::class)->setPptpServer(
                $this->selectedRouter, $this->pptp_enabled, $this->pptp_profile, $this->pptp_auth
            );
            flash()->success('PPTP settings saved!');
            $this->pptpStatus = app(MikrotikController::class)->getPptpStatus($this->selectedRouter);
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function saveSstp(): void
    {
        try {
            app(MikrotikController::class)->setSstpServer(
                $this->selectedRouter, $this->sstp_enabled, $this->sstp_profile, $this->sstp_port
            );
            flash()->success('SSTP settings saved!');
            $this->sstpStatus = app(MikrotikController::class)->getSstpStatus($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function refreshSessions(): void
    {
        try {
            $this->activeSessions = app(MikrotikController::class)->getActivePppSessions($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    private function resetData(): void
    {
        $this->l2tpStatus = $this->pptpStatus = $this->sstpStatus = $this->activeSessions = $this->pppProfiles = [];
        $this->l2tp_enabled = $this->pptp_enabled = $this->sstp_enabled = false;
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();

        return view('livewire.mikrotik.vpn-setup', compact('routers'))->layout('layouts.app');
    }
}
