<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            <i class="bi bi-pencil-square me-2 text-success"></i>{{ __('Edit Reseller') }}
        </h2>
    </x-slot>

    <div class="row g-3">
        <!-- Edit Form Column -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-success me-2"></i>{{ __('Edit Reseller Profile') }}</h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.resellers.update', $reseller->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Full Name') }}</label>
                                <input type="text" name="name" class="form-control shadow-xs" value="{{ old('name', $reseller->user->name) }}" required style="border-radius: 6px;">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Email Address') }}</label>
                                <input type="email" name="email" class="form-control shadow-xs" value="{{ old('email', $reseller->user->email) }}" required style="border-radius: 6px;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Mobile Number') }}</label>
                                <div wire:ignore>
                                    <input type="text" name="mobile" class="form-control shadow-xs" x-data="intlTelInput()" value="{{ old('mobile', $reseller->phone) }}" required style="border-radius: 6px;">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Company Name (Optional)') }}</label>
                                <input type="text" name="company" class="form-control shadow-xs" value="{{ old('company', $reseller->company) }}" style="border-radius: 6px;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Commission Percentage (%)') }}</label>
                                <input type="number" step="0.1" name="commission_percentage" class="form-control shadow-xs" value="{{ old('commission_percentage', $reseller->commission_percentage) }}" required min="0" max="100" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Status') }}</label>
                                <select name="status" class="form-select shadow-xs" required style="border-radius: 6px;">
                                    <option value="active" {{ old('status', $reseller->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="suspended" {{ old('status', $reseller->status) === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-2">
                            <label class="form-label fw-bold d-block text-dark border-bottom pb-1 mb-2">{{ __('Voucher & Billing Rules') }}</label>
                            
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="allow_fixed_voucher" value="0">
                                <input class="form-check-input" type="checkbox" name="allow_fixed_voucher" value="1" id="allow_fixed_voucher" {{ old('allow_fixed_voucher', $reseller->getSetting('allow_fixed_voucher', true) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="allow_fixed_voucher" style="font-size: 0.85rem;">{{ __('Allow Custom/Fixed Amount Vouchers') }}</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="allow_partial_activation" value="0">
                                <input class="form-check-input" type="checkbox" name="allow_partial_activation" value="1" id="allow_partial_activation" {{ old('allow_partial_activation', $reseller->getSetting('allow_partial_activation', false) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="allow_partial_activation" style="font-size: 0.85rem;">{{ __('Allow Active Connection on Partial Payment') }}</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="use_prorated_validity" value="0">
                                <input class="form-check-input" type="checkbox" name="use_prorated_validity" value="1" id="use_prorated_validity" {{ old('use_prorated_validity', $reseller->getSetting('use_prorated_validity', true) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="use_prorated_validity" style="font-size: 0.85rem;">{{ __('Enable Prorated Daily Validity (Fallback)') }}</label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="pay_discounted_upfront" value="0">
                                <input class="form-check-input" type="checkbox" name="pay_discounted_upfront" value="1" id="pay_discounted_upfront" {{ old('pay_discounted_upfront', $reseller->getSetting('pay_discounted_upfront', false) ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="pay_discounted_upfront" style="font-size: 0.85rem;">{{ __('Pay Discounted Price Upfront (Deduct commission upfront)') }}</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">{{ __('Update Password (Leave blank to keep current)') }}</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <input type="password" name="password" class="form-control shadow-xs" placeholder="{{ __('New password') }}" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-6">
                                    <input type="password" name="password_confirmation" class="form-control shadow-xs" placeholder="{{ __('Confirm new password') }}" style="border-radius: 6px;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-success mb-3 border-bottom pb-2"><i class="bi bi-box-seam me-1"></i>{{ __('Assign Admin Packages') }}</h6>
                            <div class="row g-2">
                                @forelse($packages as $package)
                                    <div class="col-md-6">
                                        <div class="form-check card p-2 border shadow-none bg-light-subtle h-100 rounded-3">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="packages[]" value="{{ $package->id }}" id="packageCheck{{ $package->id }}" {{ in_array($package->id, $assignedPackageIds) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold text-dark cursor-pointer" for="packageCheck{{ $package->id }}">
                                                {{ $package->package }}
                                                <span class="d-block text-muted small fw-normal">Price: BDT {{ number_format($package->price, 2) }} | Router: {{ $package->router_name ?? 'Global' }}</span>
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted text-center py-2">{{ __('No packages available to assign.') }}</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Reseller Permissions -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-success mb-1 border-bottom pb-2">
                                <i class="bi bi-shield-lock-fill text-success me-2"></i>{{ __('Reseller Permissions') }}
                            </h6>
                            <p class="text-muted small mb-3">{{ __('Grant or revoke which features this reseller can access from their portal.') }}</p>

                            <div class="row g-3">
                                @foreach($resellerPermissions as $groupName => $permissions)
                                    <div class="col-md-6">
                                        <div class="card border rounded-3 h-100" style="background: #f8faff;">
                                            <div class="card-header border-0 pb-1 pt-3 px-3" style="background: transparent;">
                                                <span class="fw-bold text-success" style="font-size: 0.85rem;">
                                                    @if($groupName === 'Customer Management')
                                                        <i class="bi bi-people me-1"></i>
                                                    @elseif($groupName === 'Customer Status Control')
                                                        <i class="bi bi-toggle2-on me-1"></i>
                                                    @elseif($groupName === 'Billing & Payments')
                                                        <i class="bi bi-cash-coin me-1"></i>
                                                    @elseif($groupName === 'Reports & Lists')
                                                        <i class="bi bi-bar-chart-line me-1"></i>
                                                    @elseif($groupName === 'Setup & Access')
                                                        <i class="bi bi-gear-fill me-1"></i>
                                                    @else
                                                        <i class="bi bi-box2 me-1"></i>
                                                    @endif
                                                    {{ $groupName }}
                                                </span>
                                            </div>
                                            <div class="card-body pt-2 px-3">
                                                @foreach($permissions as $permName => $permLabel)
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $permName }}"
                                                            id="perm_edit_{{ $permName }}"
                                                            {{ in_array($permName, old('permissions', $userPermissions)) ? 'checked' : '' }}>
                                                        <label class="form-check-label small text-dark" for="perm_edit_{{ $permName }}">
                                                            {{ $permLabel }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-success rounded-3" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(cb => cb.checked = true)">
                                    <i class="bi bi-check-all me-1"></i>{{ __('Select All') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" onclick="document.querySelectorAll('[name=\'permissions[]\']').forEach(cb => cb.checked = false)">
                                    <i class="bi bi-x-circle me-1"></i>{{ __('Clear All') }}
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3">
                            <a href="{{ route('admin.resellers.index') }}" wire:navigate class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-2">{{ __('Cancel') }}</a>
                            <button type="submit" class="btn btn-sm btn-success rounded-3 px-4 py-2 fw-semibold">
                                <i class="bi bi-save me-1"></i>{{ __('Update Reseller') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Wallet Info Column -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a); color: white;">
                <div class="card-body">
                    <h6 class="text-uppercase fw-bold mb-2 text-white-50" style="font-size: 0.75rem; letter-spacing: 1px;">{{ __('Wallet Status') }}</h6>
                    <h2 class="fw-bold mb-1">৳{{ number_format($reseller->balance, 2) }}</h2>
                    <p class="small text-white-50 mb-0">{{ __('Reseller ID:') }} #{{ $reseller->id }}</p>
                    
                    <div class="mt-4 pt-3 border-top border-white-50 border-opacity-25 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success rounded-3 w-100 py-2" data-bs-toggle="modal" data-bs-target="#adjustModal">
                            <i class="bi bi-cash-coin me-1"></i>{{ __('Adjust Balance') }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Adjust Balance Modal -->
            <div class="modal fade text-start" id="adjustModal" tabindex="-1" aria-labelledby="adjustModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('admin.resellers.adjust-balance', $reseller->id) }}" method="POST">
                        @csrf
                        <div class="modal-content border-0 shadow-lg rounded-3 bg-white text-dark">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-success" id="adjustModalLabel">{{ __('Adjust Wallet:') }} {{ $reseller->user->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('Adjustment Type') }}</label>
                                    <select name="type" class="form-select shadow-xs" required style="border-radius: 6px;">
                                        <option value="credit">{{ __('Credit (Add Balance)') }}</option>
                                        <option value="debit">{{ __('Debit (Deduct Balance)') }}</option>
                                        <option value="payout">{{ __('Commission Payout (Deduct Balance)') }}</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('Amount (BDT)') }}</label>
                                    <input type="number" step="0.01" name="amount" class="form-control shadow-xs" required min="0.01" style="border-radius: 6px;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-muted mb-1">{{ __('Description / Reason') }}</label>
                                    <textarea name="description" class="form-control shadow-xs" rows="3" required style="border-radius: 6px;"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-sm btn-light rounded-3 px-3 py-1.5" data-bs-dismiss="modal">{{ __('Close') }}</button>
                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-4 py-1.5 fw-semibold">{{ __('Apply Adjustment') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Accounting Statistics Summary -->
            <div class="row g-2 mb-4">
                <!-- Total Earned Commission -->
                <div class="col-md-6 col-12">
                    <div class="card border shadow-none rounded-3 bg-light-subtle">
                        <div class="card-body p-3">
                            <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size:0.62rem; letter-spacing: 0.5px;">{{ __('Total Comm. Redeemed') }}</span>
                            <h5 class="fw-bold text-dark mb-0">৳{{ number_format($totalCommissionEarned, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <!-- Upfront Commission Retained -->
                <div class="col-md-6 col-12">
                    <div class="card border shadow-none rounded-3 bg-light-subtle">
                        <div class="card-body p-3">
                            <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size:0.62rem; letter-spacing: 0.5px;">{{ __('Total Comm. Upfront') }}</span>
                            <h5 class="fw-bold text-dark mb-0">৳{{ number_format($totalUpfrontCommission, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <!-- Total Commission Paid Out -->
                <div class="col-md-6 col-12">
                    <div class="card border shadow-none rounded-3 bg-light-subtle">
                        <div class="card-body p-3">
                            <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size:0.62rem; letter-spacing: 0.5px;">{{ __('Total Comm. Paid Out') }}</span>
                            <h5 class="fw-bold text-dark mb-0">৳{{ number_format($totalCommissionPaid, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <!-- Current Remaining Wallet Balance -->
                <div class="col-md-6 col-12">
                    <div class="card border shadow-none rounded-3 bg-light-subtle">
                        <div class="card-body p-3">
                            <span class="text-uppercase fw-bold text-muted small d-block mb-1" style="font-size:0.62rem; letter-spacing: 0.5px;">{{ __('Remaining Balance') }}</span>
                            <h5 class="fw-bold text-success mb-0">৳{{ number_format($reseller->balance, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Logs -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-success me-2"></i>{{ __('Recent Activity Logs') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-3" style="max-height: 250px; overflow-y: auto;">
                        @forelse($logs as $log)
                            <div class="list-group-item py-2 px-3 border-bottom">
                                <div class="small fw-semibold text-dark">{{ $log->description }}</div>
                                <div class="d-flex justify-content-between mt-1 text-muted" style="font-size: 0.7rem;">
                                    <span>By: {{ $log->causer->name ?? 'System' }}</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-3 text-center text-muted small">{{ __('No activity logs recorded.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Reseller Wallet Transaction History -->
        <div class="col-12 mt-2 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-wallet2 text-success me-2"></i>{{ __('Wallet Transaction Ledger (Last 50 Transactions)') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover" style="font-size: 0.85rem;">
                            <thead class="table-success text-success text-center">
                                <tr class="fw-semibold">
                                    <th class="ps-4 py-2">{{ __('Date') }}</th>
                                    <th class="text-start py-2">{{ __('Description') }}</th>
                                    <th class="py-2">{{ __('Reference Type') }}</th>
                                    <th class="text-end py-2">{{ __('Amount') }}</th>
                                    <th class="text-center py-2" style="width: 120px;">{{ __('Type') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($walletTransactions as $trx)
                                    <tr>
                                        <td class="small text-muted ps-4 text-center">{{ $trx->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td class="text-dark small fw-semibold text-start">{{ $trx->description }}</td>
                                        <td class="text-center">
                                            @if($trx->reference_type)
                                                <span class="badge bg-secondary-subtle text-secondary text-xs">{{ $trx->reference_type }} (#{{ $trx->reference_id }})</span>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold">৳{{ number_format($trx->amount, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $trx->type === 'credit' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' }} px-2 py-1 text-uppercase" style="font-size: 0.72rem;">
                                                {{ $trx->type }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">{{ __('No wallet transactions found for this reseller.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
