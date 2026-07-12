<?php

namespace App\Livewire\Reseller;

use App\Models\ResellerWalletTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class ResellerWalletManagement extends Component
{
    use WithPagination;

    public $type = 'all';

    protected $paginationTheme = 'bootstrap';

    public function updatingType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $reseller = auth()->user()->reseller;
        if (! $reseller) {
            abort(403);
        }

        $transactions = ResellerWalletTransaction::where('reseller_id', $reseller->id)
            ->when($this->type !== 'all', function ($q) {
                $q->where('type', $this->type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate accounting stats for reseller
        $totalCommissionEarned = \App\Models\ResellerCommission::where('reseller_id', $reseller->id)->sum('amount');
        $totalCommissionPaid = \App\Models\ResellerWalletTransaction::where('reseller_id', $reseller->id)
            ->where('description', 'like', 'Commission Payout:%')
            ->sum('amount');
        $totalUpfrontCommission = \App\Models\Voucher::where('reseller_id', $reseller->id)
            ->where('commission_paid_upfront', true)
            ->get()
            ->sum(function($v) use ($reseller) {
                return (float)$v->value * ((float)$reseller->commission_percentage / 100);
            });

        return view('livewire.reseller.wallet-management', [
            'reseller' => $reseller,
            'transactions' => $transactions,
            'totalCommissionEarned' => $totalCommissionEarned,
            'totalCommissionPaid' => $totalCommissionPaid,
            'totalUpfrontCommission' => $totalUpfrontCommission,
        ])->layout('layouts.app');
    }
}
