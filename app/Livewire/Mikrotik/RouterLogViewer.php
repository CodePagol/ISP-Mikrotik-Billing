<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\MainSiteData;
use App\Models\MikrotikLog;
use App\Models\RouterList;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class RouterLogViewer extends Component
{
    use WithPagination;

    public string $selectedRouter = '';

    public string $filterTopic = '';

    public string $filterBuffer = '';

    public string $searchMessage = '';

    public bool $logServerEnabled = false;

    public int $liveCount = 0;

    public bool $autoRefresh = true;

    protected $queryString = [
        'selectedRouter' => ['except' => ''],
        'filterTopic' => ['except' => ''],
        'filterBuffer' => ['except' => ''],
        'searchMessage' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->logServerEnabled = (bool) MainSiteData::getValue('log_server_enabled', false);

        // Default to first connected router
        $this->selectedRouter = RouterList::where('action', 'connected')
            ->first()?->router_name ?? '';
    }

    /** Fetch fresh live logs from the router. */
    public function pollLogs(): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        try {
            $ctrl = app(MikrotikController::class);
            $logs = $ctrl->getRouterLogs($this->selectedRouter, 200);

            if ($this->logServerEnabled && ! empty($logs)) {
                // Store in DB, Livewire component will naturally re-render the table
                $ctrl->storeRouterLogs($this->selectedRouter, $logs);
            } else {
                // If not storing in DB, dispatch to AlpineJS to render live terminal
                $this->liveCount = count($logs);
                $this->dispatch('logs-refreshed', logs: $logs);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function clearOldLogs(): void
    {
        $days = (int) MainSiteData::getValue('log_retention_days');
        app(MikrotikController::class)->pruneOldLogs($days, $this->selectedRouter ?: null);
        $this->dispatch('notify', message: "Logs older than {$days} days cleared.");
    }

    public function updatedSelectedRouter(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTopic(): void
    {
        $this->resetPage();
    }

    public function routers(): Collection
    {
        return RouterList::where('action', 'connected')->pluck('router_name');
    }

    public function render()
    {
        $query = MikrotikLog::query()
            ->when($this->selectedRouter, fn ($q) => $q->where('router_name', $this->selectedRouter))
            ->when($this->filterTopic, fn ($q) => $q->where('topics', 'like', "%{$this->filterTopic}%"))
            ->when($this->filterBuffer, fn ($q) => $q->where('buffer', $this->filterBuffer))
            ->when($this->searchMessage, fn ($q) => $q->where('message', 'like', "%{$this->searchMessage}%"))
            ->latest()
            ->paginate(50);

        return view('livewire.mikrotik.router-log-viewer', [
            'logs' => $query,
            'routers' => $this->routers(),
        ])->layout('layouts.app');
    }
}
