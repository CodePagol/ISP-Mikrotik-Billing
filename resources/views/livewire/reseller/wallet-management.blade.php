<div class="container-fluid py-2">
    <!-- Accounting Statistics Summary -->
    <div class="row g-3 mb-4">
        <!-- Wallet Balance Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 text-white" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-white-50 small d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Wallet Balance (Remaining)') }}</span>
                        <h3 class="fw-bold mb-1">৳{{ number_format($reseller->balance, 2) }}</h3>
                    </div>
                    <div class="mt-2 text-white-50 small" style="font-size: 0.72rem;">
                        {{ __('Reseller ID: #:id', ['id' => $reseller->id]) }}
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Commission Earned Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Total Comm. Earned (Redeemed)') }}</span>
                        <h3 class="fw-bold text-dark mb-1">৳{{ number_format($totalCommissionEarned, 2) }}</h3>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size: 0.72rem;">
                        {{ __('Credited to Wallet balance') }}
                    </div>
                </div>
            </div>
        </div>
        <!-- Upfront Commission Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Total Comm. Upfront') }}</span>
                        <h3 class="fw-bold text-dark mb-1">৳{{ number_format($totalUpfrontCommission, 2) }}</h3>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size: 0.72rem;">
                        {{ __('Retained as discount upfront') }}
                    </div>
                </div>
            </div>
        </div>
        <!-- Commission Paid Out Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Total Paid Out (Cash)') }}</span>
                        <h3 class="fw-bold text-dark mb-1">৳{{ number_format($totalCommissionPaid, 2) }}</h3>
                    </div>
                    <div class="mt-2 text-muted small" style="font-size: 0.72rem;">
                        {{ __('Withdrawn in cash from admin') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Column -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>{{ __('Transaction Ledger') }}</h5>
                    
                    <!-- Filter -->
                    <select wire:model.live="type" class="form-select form-select-sm" style="max-width: 180px;">
                        <option value="all">{{ __('All Transactions') }}</option>
                        <option value="credit">{{ __('Credit Only') }}</option>
                        <option value="debit">{{ __('Debit Only') }}</option>
                    </select>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr class="small text-muted fw-semibold">
                                    <th class="ps-4">{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Reference Type') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                    <th class="text-center" style="width: 120px;">{{ __('Type') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $trx)
                                    <tr>
                                        <td class="small text-muted ps-4">{{ $trx->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td class="text-dark small fw-semibold">{{ __($trx->description) }}</td>
                                        <td>
                                            @if($trx->reference_type)
                                                <span class="badge bg-secondary-subtle text-secondary text-xs">{{ __($trx->reference_type) }} (#{{ $trx->reference_id }})</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">৳{{ number_format($trx->amount, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $trx->type === 'credit' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2.5 py-1 text-uppercase text-xs" style="font-size: 0.7rem;">
                                                {{ __($trx->type) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">{{ __('No wallet transactions found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($transactions->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
