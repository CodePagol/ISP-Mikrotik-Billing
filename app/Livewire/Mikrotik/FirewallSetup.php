<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class FirewallSetup extends Component
{
    public string $selectedRouter = '';

    public string $activeTab = 'filter';

    // Filter rule form
    public string $f_chain = 'forward';

    public string $f_action = 'accept';

    public string $f_protocol = '';

    public string $f_src = '';

    public string $f_dst = '';

    public string $f_comment = '';

    public ?string $editFilterId = null;

    // NAT rule form
    public string $n_chain = 'srcnat';

    public string $n_action = 'masquerade';

    public string $n_out_interface = '';

    public string $n_src_address = '';

    public string $n_comment = '';

    public ?string $editNatId = null;

    // Address List form
    public string $al_list = '';

    public string $al_address = '';

    public string $al_comment = '';

    public ?string $editListId = null;

    // Data
    public array $filterRules = [];

    public array $natRules = [];

    public array $mangleRules = [];

    public array $addressLists = [];

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
            $this->filterRules = $ctrl->getFirewallFilter($this->selectedRouter);
            $this->natRules = $ctrl->getFirewallNat($this->selectedRouter);
            $this->mangleRules = $ctrl->getFirewallMangle($this->selectedRouter);
            $this->addressLists = $ctrl->getAddressLists($this->selectedRouter);
            $this->interfaces = collect($ctrl->getInterfaces($this->selectedRouter))->pluck('name')->filter()->values()->toArray();

        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function editFilter(array $rule): void
    {
        $this->editFilterId = $rule['.id'] ?? null;
        $this->f_chain = $rule['chain'] ?? 'forward';
        $this->f_action = $rule['action'] ?? 'accept';
        $this->f_protocol = $rule['protocol'] ?? '';
        $this->f_src = $rule['src-address'] ?? '';
        $this->f_dst = $rule['dst-address'] ?? '';
        $this->f_comment = $rule['comment'] ?? '';
    }

    public function addFilterRule(): void
    {
        $this->validate(['f_chain' => 'required|string', 'f_action' => 'required|string']);
        try {
            app(MikrotikController::class)->addFirewallFilter($this->selectedRouter, [
                'chain' => $this->f_chain, 'action' => $this->f_action,
                'protocol' => $this->f_protocol, 'src_address' => $this->f_src,
                'dst_address' => $this->f_dst, 'comment' => $this->f_comment,
            ], $this->editFilterId);
            flash()->success($this->editFilterId ? 'Filter rule updated!' : 'Filter rule added!');
            $this->reset(['f_src', 'f_dst', 'f_protocol', 'f_comment', 'editFilterId']);
            $this->filterRules = app(MikrotikController::class)->getFirewallFilter($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function toggleFilter(int $index, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleFirewallFilter($this->selectedRouter, $index, $enable);
            $this->filterRules = app(MikrotikController::class)->getFirewallFilter($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeFilter(int $index): void
    {
        try {
            app(MikrotikController::class)->removeFirewallFilter($this->selectedRouter, $index);
            flash()->success('Filter rule removed.');
            $this->filterRules = app(MikrotikController::class)->getFirewallFilter($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editNat(array $rule): void
    {
        $this->editNatId = $rule['.id'] ?? null;
        $this->n_chain = $rule['chain'] ?? 'srcnat';
        $this->n_action = $rule['action'] ?? 'masquerade';
        $this->n_out_interface = $rule['out-interface'] ?? '';
        $this->n_src_address = $rule['src-address'] ?? '';
        $this->n_comment = $rule['comment'] ?? '';
    }

    public function addNatRule(): void
    {
        $this->validate(['n_chain' => 'required|string', 'n_action' => 'required|string']);
        try {
            app(MikrotikController::class)->addFirewallNat($this->selectedRouter, [
                'chain' => $this->n_chain, 'action' => $this->n_action,
                'out_interface' => $this->n_out_interface, 'src_address' => $this->n_src_address,
                'comment' => $this->n_comment,
            ], $this->editNatId);
            flash()->success($this->editNatId ? 'NAT rule updated!' : 'NAT rule added!');
            $this->reset(['n_out_interface', 'n_src_address', 'n_comment', 'editNatId']);
            $this->natRules = app(MikrotikController::class)->getFirewallNat($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function quickMasquerade(): void
    {
        $this->n_chain = 'srcnat';
        $this->n_action = 'masquerade';
        $this->n_comment = 'Auto-masquerade';
        $this->addNatRule();
    }

    public function toggleNat(int $index, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleFirewallNat($this->selectedRouter, $index, $enable);
            $this->natRules = app(MikrotikController::class)->getFirewallNat($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeNat(int $index): void
    {
        try {
            app(MikrotikController::class)->removeFirewallNat($this->selectedRouter, $index);
            flash()->success('NAT rule removed.');
            $this->natRules = app(MikrotikController::class)->getFirewallNat($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function toggleMangle(int $index, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleFirewallMangle($this->selectedRouter, $index, $enable);
            $this->mangleRules = app(MikrotikController::class)->getFirewallMangle($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeMangle(int $index): void
    {
        try {
            app(MikrotikController::class)->removeFirewallMangle($this->selectedRouter, $index);
            flash()->success('Mangle rule removed.');
            $this->mangleRules = app(MikrotikController::class)->getFirewallMangle($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editAddressList(array $list): void
    {
        $this->editListId = $list['.id'] ?? null;
        $this->al_list = $list['list'] ?? '';
        $this->al_address = $list['address'] ?? '';
        $this->al_comment = $list['comment'] ?? '';
    }

    public function addToAddressList(): void
    {
        $this->validate(['al_list' => 'required|string|max:100', 'al_address' => 'required|string']);
        try {
            app(MikrotikController::class)->addAddressList($this->selectedRouter, $this->al_list, $this->al_address, $this->al_comment ?: null, $this->editListId);
            flash()->success($this->editListId ? 'Address list updated!' : 'Added to address list!');
            $this->reset(['al_address', 'al_comment', 'editListId']);
            $this->addressLists = app(MikrotikController::class)->getAddressLists($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function moveUp(string $type, int $index): void
    {
        $rules = $this->{$type.'Rules'};
        if ($index <= 0) {
            return;
        }
        $id = $rules[$index]['.id'];
        $prevId = $rules[$index - 1]['.id'];
        try {
            app(MikrotikController::class)->moveItem($this->selectedRouter, '/ip firewall '.$type, $id, $prevId);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function moveDown(string $type, int $index): void
    {
        $rules = $this->{$type.'Rules'};
        if ($index >= count($rules) - 1) {
            return;
        }
        $id = $rules[$index]['.id'];
        $nextNextId = $rules[$index + 2]['.id'] ?? null;
        try {
            app(MikrotikController::class)->moveItem($this->selectedRouter, '/ip firewall '.$type, $id, $nextNextId);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeFromAddressList(string $list, string $address): void
    {
        try {
            app(MikrotikController::class)->removeAddressList($this->selectedRouter, $list, $address);
            flash()->success('Removed from address list.');
            $this->addressLists = app(MikrotikController::class)->getAddressLists($this->selectedRouter);

        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    private function resetData(): void
    {
        $this->filterRules = $this->natRules = $this->mangleRules = $this->addressLists = $this->interfaces = [];
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();

        return view('livewire.mikrotik.firewall-setup', compact('routers'))->layout('layouts.app');
    }
}
