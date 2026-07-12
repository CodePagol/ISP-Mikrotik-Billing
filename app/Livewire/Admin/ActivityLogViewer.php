<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogViewer extends Component
{
    use WithPagination;

    public string $search = '';
    public string $logName = 'all';
    public string $event = 'all';

    public ?int $selectedLogId = null;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingLogName(): void { $this->resetPage(); }
    public function updatingEvent(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'logName', 'event', 'selectedLogId']);
    }

    public function showDetails(int $id): void
    {
        $this->selectedLogId = $id;
    }

    public function closeDetails(): void
    {
        $this->selectedLogId = null;
    }

    public function render()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('properties', 'like', '%' . $this->search . '%')
                  ->orWhereHas('causer', function ($qc) {
                      $qc->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->logName !== 'all') {
            $query->where('log_name', $this->logName);
        }

        if ($this->event !== 'all') {
            $query->where('event', $this->event);
        }

        $logs = $query->paginate(20);

        // Fetch distinct log names and events for filters
        $logNames = Activity::distinct()->pluck('log_name')->filter()->toArray();
        $events = Activity::distinct()->pluck('event')->filter()->toArray();

        $selectedLog = $this->selectedLogId ? Activity::find($this->selectedLogId) : null;

        return view('livewire.admin.activity-log-viewer', compact('logs', 'logNames', 'events', 'selectedLog'))
            ->layout('layouts.app');
    }
}
