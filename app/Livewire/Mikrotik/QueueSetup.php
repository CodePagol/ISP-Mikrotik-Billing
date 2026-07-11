<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Livewire\Component;

class QueueSetup extends Component
{
    public string $selectedRouter = '';

    public string $activeTab = 'simple';

    // Simple Queue form
    public string $sq_name = '';

    public string $sq_target = '';

    public string $sq_max_limit = '10M/10M';

    public string $sq_comment = '';

    public ?string $editSimpleId = null;

    // Queue Tree form
    public string $qt_name = '';

    public string $qt_parent = 'global';

    public string $qt_max_limit = '10M';

    public string $qt_limit_at = '';

    public int $qt_priority = 8;

    public string $qt_comment = '';

    public ?string $editTreeId = null;

    // Data
    public array $simpleQueues = [];

    public array $queueTree = [];

    public array $queueTypes = [];

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
            $this->simpleQueues = $ctrl->getSimpleQueues($this->selectedRouter);
            $this->queueTree = $ctrl->getQueueTree($this->selectedRouter);
            $this->queueTypes = $ctrl->getQueueTypes($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error('Load error: '.$e->getMessage());
        }
    }

    public function editSimpleQueue(array $sq): void
    {
        $this->editSimpleId = $sq['.id'] ?? null;
        $this->sq_name = $sq['name'] ?? '';
        $this->sq_target = $sq['target'] ?? '';
        $this->sq_max_limit = $sq['max-limit'] ?? '10M/10M';
        $this->sq_comment = $sq['comment'] ?? '';
    }

    public function addSimpleQueue(): void
    {
        $this->validate([
            'sq_name' => 'required|string|max:100',
            'sq_target' => 'required|string',
            'sq_max_limit' => ['required', 'string', 'regex:/^\d+[KMGkmg]?\/\d+[KMGkmg]?$/'],
        ], ['sq_max_limit.regex' => 'Format must be like 10M/10M or 512k/1M']);
        try {
            app(MikrotikController::class)->addSimpleQueue($this->selectedRouter, [
                'name' => $this->sq_name, 'target' => $this->sq_target,
                'max_limit' => $this->sq_max_limit, 'comment' => $this->sq_comment,
            ], $this->editSimpleId);
            flash()->success($this->editSimpleId ? 'Simple Queue updated!' : 'Simple Queue added!');
            $this->reset(['sq_name', 'sq_target', 'sq_comment', 'editSimpleId']);
            $this->simpleQueues = app(MikrotikController::class)->getSimpleQueues($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeSimpleQueue(string $name): void
    {
        try {
            app(MikrotikController::class)->removeSimpleQueue($this->selectedRouter, $name);
            flash()->success('Queue removed.');
            $this->simpleQueues = app(MikrotikController::class)->getSimpleQueues($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function toggleSimpleQueue(string $name, bool $enable): void
    {
        try {
            app(MikrotikController::class)->toggleSimpleQueue($this->selectedRouter, $name, $enable);
            flash()->success('Queue '.($enable ? 'enabled' : 'disabled').'.');
            $this->simpleQueues = app(MikrotikController::class)->getSimpleQueues($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function editQueueTree(array $qt): void
    {
        $this->editTreeId = $qt['.id'] ?? null;
        $this->qt_name = $qt['name'] ?? '';
        $this->qt_parent = $qt['parent'] ?? 'global';
        $this->qt_max_limit = $qt['max-limit'] ?? '10M';
        $this->qt_limit_at = $qt['limit-at'] ?? '';
        $this->qt_priority = (int) ($qt['priority'] ?? 8);
        $this->qt_comment = $qt['comment'] ?? '';
    }

    public function addQueueTree(): void
    {
        $this->validate([
            'qt_name' => 'required|string|max:100',
            'qt_parent' => 'required|string',
            'qt_max_limit' => 'required|string',
            'qt_priority' => 'required|integer|min:1|max:8',
        ]);
        try {
            app(MikrotikController::class)->addQueueTree($this->selectedRouter, [
                'name' => $this->qt_name, 'parent' => $this->qt_parent,
                'max_limit' => $this->qt_max_limit, 'limit_at' => $this->qt_limit_at,
                'priority' => $this->qt_priority, 'comment' => $this->qt_comment,
            ], $this->editTreeId);
            flash()->success($this->editTreeId ? 'Queue Tree entry updated!' : 'Queue Tree entry added!');
            $this->reset(['qt_name', 'qt_limit_at', 'qt_comment', 'editTreeId']);
            $this->queueTree = app(MikrotikController::class)->getQueueTree($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function removeQueueTree(string $name): void
    {
        try {
            app(MikrotikController::class)->removeQueueTree($this->selectedRouter, $name);
            flash()->success('Queue Tree entry removed.');
            $this->queueTree = app(MikrotikController::class)->getQueueTree($this->selectedRouter);
            $this->dispatch('reinit-datatables');
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function moveUp(string $type, int $index): void
    {
        $list = $type === 'simple' ? $this->simpleQueues : $this->queueTree;
        if ($index <= 0) {
            return;
        }
        $id = $list[$index]['.id'];
        $prevId = $list[$index - 1]['.id'];
        try {
            app(MikrotikController::class)->moveItem($this->selectedRouter, '/queue '.$type, $id, $prevId);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function moveDown(string $type, int $index): void
    {
        $list = $type === 'simple' ? $this->simpleQueues : $this->queueTree;
        if ($index >= count($list) - 1) {
            return;
        }
        $id = $list[$index]['.id'];
        $nextNextId = $list[$index + 2]['.id'] ?? null;
        try {
            app(MikrotikController::class)->moveItem($this->selectedRouter, '/queue '.$type, $id, $nextNextId);
            $this->loadData();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    private function resetData(): void
    {
        $this->simpleQueues = $this->queueTree = $this->queueTypes = [];
    }

    public function render()
    {
        $routers = RouterList::where('action', 'connected')->orderBy('router_name')->get();

        return view('livewire.mikrotik.queue-setup', compact('routers'))->layout('layouts.app');
    }
}
