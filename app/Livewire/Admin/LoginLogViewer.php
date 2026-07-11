<?php

namespace App\Livewire\Admin;

use App\Models\PPPSecrets;
use App\Models\User;
use App\Models\UserLoginLog;
use Livewire\Component;
use Livewire\WithPagination;

class LoginLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $action = 'all';

    public string $userType = 'all';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAction(): void
    {
        $this->resetPage();
    }

    public function updatingUserType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'action', 'userType']);
    }

    public function render()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $query = UserLoginLog::with([
            'authenticatable' => function ($morphTo) {
                $morphTo->morphWith([
                    User::class => ['reseller'],
                ]);
            },
        ])->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('username', 'like', '%'.$this->search.'%')
                    ->orWhere('ip_address', 'like', '%'.$this->search.'%')
                    ->orWhere('user_agent', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->action !== 'all') {
            $query->where('action', $this->action);
        }

        if ($this->userType !== 'all') {
            if ($this->userType === 'admin') {
                $query->whereHasMorph('authenticatable', [User::class], function ($q) {
                    $q->whereDoesntHave('reseller');
                });
            } elseif ($this->userType === 'reseller') {
                $query->whereHasMorph('authenticatable', [User::class], function ($q) {
                    $q->whereHas('reseller');
                });
            } elseif ($this->userType === 'customer') {
                $query->where('authenticatable_type', PPPSecrets::class);
            }
        }

        $logs = $query->paginate(20);

        return view('livewire.admin.login-log-viewer', compact('logs'))
            ->layout('layouts.app');
    }
}
