<div>
    <div class="container-fluid py-4 zoom-in">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1f36;">
                    <i class="bi bi-ticket-perforated me-2 text-success"></i>{{ __('Reseller Vouchers Audit') }}
                </h4>
                <p class="text-muted small mb-0">{{ __('Track and monitor all generated, used, and expired reseller recharge vouchers') }}</p>
            </div>
            <button wire:click="resetFilters" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold shadow-sm">
                <i class="bi bi-arrow-repeat me-1"></i> {{ __('Reset Filters') }}
            </button>
        </div>

        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Search Vouchers') }}</label>
                        <div class="input-group input-group-sm shadow-xs">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input wire:model.live.debounce.400ms="search" type="text" 
                                placeholder="{{ __('Search by voucher code, reseller name, customer...') }}" 
                                class="form-control form-control-sm border-start-0" style="border-radius: 6px;">
                        </div>
                    </div>
                    <div class="col-md-3 col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Status') }}</label>
                        <select wire:model.live="statusFilter" class="form-select form-select-sm shadow-xs" style="border-radius: 6px;">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="unused">{{ __('Unused Only') }}</option>
                            <option value="used">{{ __('Used Only') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-12">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Reseller Filter') }}</label>
                        <select wire:model.live="resellerFilter" class="form-select form-select-sm shadow-xs" style="border-radius: 6px;">
                            <option value="all">{{ __('All Resellers') }}</option>
                            @foreach($resellers as $res)
                                <option value="{{ $res->id }}">{{ $res->user?->name ?? 'N/A' }} ({{ $res->company }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-success text-success">
                            <tr class="fw-semibold">
                                <th class="ps-4 py-3">{{ __('Voucher Code') }}</th>
                                <th>{{ __('Value') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Reseller') }}</th>
                                <th>{{ __('Commission Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Redeemed By') }}</th>
                                <th>{{ __('Expiry Date') }}</th>
                                <th class="pe-4">{{ __('Used At') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $voucher)
                            <tr wire:key="admin-voucher-{{ $voucher->id }}">
                                <td class="ps-4">
                                    <div class="fw-bold font-monospace text-dark" style="font-size: 0.85rem;">{{ $voucher->code }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">৳ {{ number_format($voucher->value, 2) }}</div>
                                </td>
                                <td class="small">
                                    @if($voucher->type === 'package_based')
                                        <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1">
                                            {{ __('Package: :package', ['package' => $voucher->package?->package ?? 'N/A']) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2.5 py-1">
                                            {{ __('Fixed Amount') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $voucher->reseller?->user?->name ?? 'N/A' }}</div>
                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $voucher->reseller?->company }}</div>
                                </td>
                                <td>
                                    @if($voucher->commission_paid_upfront)
                                        <span class="badge bg-info-subtle text-info border border-info px-2.5 py-1">
                                            {{ __('Upfront Discount') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border border-warning px-2.5 py-1">
                                            {{ __('Redeem Commission') }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $isExpired = $voucher->isExpired() && $voucher->status === 'unused';
                                        $statusClass = $isExpired ? 'secondary' : ($voucher->status === 'used' ? 'danger' : 'success');
                                        $statusLabel = $isExpired ? 'Expired' : ($voucher->status === 'used' ? 'Used' : 'Unused');
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }} px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                                        {{ __($statusLabel) }}
                                    </span>
                                </td>
                                <td>
                                    @if($voucher->usedBy)
                                        <div class="fw-bold text-dark">{{ $voucher->usedBy->customer_name }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">A/C: {{ $voucher->usedBy->customer_unique_id }}</div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $voucher->expiry_date->format('d M Y') }}
                                </td>
                                <td class="pe-4 small text-muted">
                                    {{ $voucher->used_at ? $voucher->used_at->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-ticket-slash fs-2 text-success d-block mb-2"></i>
                                    {{ __('No vouchers found.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($vouchers->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $vouchers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
