<div class="row">
    <!-- Vouchers List Column -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-ticket-perforated-fill text-primary me-2"></i>{{ __('My Vouchers') }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">{{ __('Voucher Code') }}</th>
                                <th>{{ __('Value') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Commission') }}</th>
                                <th class="text-center">{{ __('Expiry Date') }}</th>
                                <th class="text-center">{{ __('Status') }}</th>
                                <th>{{ __('Redeemed By') }}</th>
                                <th class="text-end pe-3">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $voucher)
                                <tr>
                                    <td class="fw-bold text-dark ps-3"><code>{{ $voucher->code }}</code></td>
                                    <td class="fw-semibold text-success">৳{{ number_format($voucher->value, 2) }}</td>
                                    <td>
                                        @if($voucher->type === 'package_based')
                                            <span class="badge bg-primary-subtle text-primary">{{ __('Package: :package', ['package' => $voucher->package->package ?? 'N/A']) }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Fixed Balance') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($voucher->commission_paid_upfront)
                                            <span class="badge bg-info-subtle text-info">{{ __('Upfront Discount') }}</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">{{ __('Redeem Commission') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center small">{{ $voucher->expiry_date->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        @if($voucher->status === 'unused')
                                            @if($voucher->isExpired())
                                                <span class="badge bg-danger-subtle text-danger">{{ __('Expired') }}</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">{{ __('Unused') }}</span>
                                            @endif
                                        @elseif($voucher->status === 'cancelled')
                                            <span class="badge bg-warning-subtle text-warning">{{ __('Cancelled') }}</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ __('Used') }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">
                                        @if($voucher->status === 'used' && $voucher->usedBy)
                                            <div class="text-dark fw-semibold">{{ $voucher->usedBy->customer_name }}</div>
                                            <div>{{ __('Redeemed:') }} {{ $voucher->used_at ? $voucher->used_at->format('Y-m-d H:i') : 'N/A' }}</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        @if($voucher->status === 'unused' && !$voucher->isExpired())
                                            <button onclick="confirm('{{ __('Are you sure you want to cancel this voucher and refund BDT :value to your wallet?', ['value' => number_format($voucher->value, 2)]) }}') || event.stopImmediatePropagation()" wire:click="cancelVoucher({{ $voucher->id }})" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem; font-weight: 600;">
                                                <i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">{{ __('No vouchers generated yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($vouchers->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $vouchers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Voucher Generation Column -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill text-success me-2"></i>{{ __('Generate Vouchers') }}</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="generate">
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Number of Vouchers') }}</label>
                        <input type="number" wire:model="count" class="form-control" required min="1" max="100">
                        <small class="text-muted">{{ __('Batch generate up to 100 vouchers at once.') }}</small>
                        @error('count') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Voucher Type') }}</label>
                        <select wire:model.live="type" class="form-select" required>
                            @if(auth()->user()->reseller && auth()->user()->reseller->getSetting('allow_fixed_voucher', true))
                                <option value="fixed_amount">{{ __('Fixed Amount (Balance Credit)') }}</option>
                            @endif
                            <option value="package_based">{{ __('Package-Based (Direct Recharge)') }}</option>
                        </select>
                        @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- Fixed Amount Group -->
                    @if($type === 'fixed_amount')
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Voucher Value (BDT)') }}</label>
                            <input type="number" step="0.01" wire:model="value" class="form-control" placeholder="0.00" required>
                            @error('value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <!-- Package Selection Group -->
                    @if($type === 'package_based')
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Select Billing Package') }}</label>
                            <select wire:model="package_id" class="form-select" required>
                                <option value="">{{ __('-- Choose Package --') }}</option>
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}">
                                        {{ $pkg->package }} ({{ __('Price:') }} BDT {{ number_format($pkg->price, 0) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('Voucher value will equal the price of the selected package.') }}</small>
                            @error('package_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('Voucher Expiry Date') }}</label>
                        <input type="date" wire:model="expiry_date" class="form-control" required>
                        @error('expiry_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success rounded-3 fw-bold">{{ __('Generate Batch') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
