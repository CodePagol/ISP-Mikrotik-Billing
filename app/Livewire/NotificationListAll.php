<?php

namespace App\Livewire;

use App\Models\NotificationLogs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class NotificationListAll extends Component
{
    use WithoutUrlPagination, WithPagination;

    public $search = '';
    public $statusFilter = ''; // 'read', 'unread'
    public $titleFilter = '';
    public $typeFilter = '';
    public $readByFilter = '';

    protected $queryString = ['search', 'statusFilter', 'titleFilter', 'typeFilter', 'readByFilter'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTitleFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingReadByFilter()
    {
        $this->resetPage();
    }

    public function markAsRead($id)
    {
        NotificationLogs::where('id', $id)->update([
            'read_by' => Auth::user()->name,
            'read_at' => Carbon::now(),
        ]);

        flash()->success('Notification marked as read');
    }

    public function markAllAsRead()
    {
        NotificationLogs::whereNull('read_by')->update([
            'read_by' => Auth::user()->name,
            'read_at' => Carbon::now(),
        ]);

        flash()->success('All notifications marked as read');
    }

    public function render()
    {
        $query = NotificationLogs::latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter === 'unread') {
            $query->whereNull('read_by');
        } elseif ($this->statusFilter === 'read') {
            $query->whereNotNull('read_by');
        }

        if ($this->titleFilter) {
            $query->where('title', $this->titleFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->readByFilter) {
            $query->where('read_by', $this->readByFilter);
        }

        $notifications = $query->paginate(15);

        // Pluck distinct options for dropdown filters
        $titles = NotificationLogs::whereNotNull('title')->where('title', '!=', '')->distinct()->orderBy('title')->pluck('title');
        $types = NotificationLogs::whereNotNull('type')->where('type', '!=', '')->distinct()->orderBy('type')->pluck('type');
        $readByUsers = NotificationLogs::whereNotNull('read_by')->where('read_by', '!=', '')->distinct()->orderBy('read_by')->pluck('read_by');

        return view('livewire.notification-list-all', [
            'notifications' => $notifications,
            'titles' => $titles,
            'types' => $types,
            'readByUsers' => $readByUsers,
        ])->layout('layouts.app');
    }
}
