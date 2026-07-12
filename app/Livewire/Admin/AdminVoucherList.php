<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Voucher;

class AdminVoucherList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $resellerFilter = 'all';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingResellerFilter(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'resellerFilter']);
    }

    public function render()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $query = Voucher::with(['reseller.user', 'usedBy', 'package'])->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('reseller.user', function ($qu) {
                      $qu->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('usedBy', function ($qc) {
                      $qc->where('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_unique_id', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->resellerFilter !== 'all') {
            $query->where('reseller_id', $this->resellerFilter);
        }

        $vouchers = $query->paginate(20);

        $resellers = \App\Models\Reseller::with('user')->get();

        return view('livewire.admin.admin-voucher-list', compact('vouchers', 'resellers'))
            ->layout('layouts.app');
    }
}
