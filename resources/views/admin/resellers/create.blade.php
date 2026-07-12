<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            <i class="bi bi-person-plus-fill me-2 text-success"></i>{{ __('Create Reseller') }}
        </h2>
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-plus-fill text-success me-2"></i>{{ __('Create New Reseller') }}</h5>
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

            <form action="{{ route('admin.resellers.store') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Basic Information -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-success mb-3 border-bottom pb-2"><i class="bi bi-shield-lock me-1"></i>{{ __('Login Credentials') }}</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small">{{ __('Full Name') }}</label>
                            <input type="text" name="name" class="form-control shadow-xs" value="{{ old('name') }}" required placeholder="e.g. John Doe" style="border-radius: 6px;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-muted small">{{ __('Email Address') }}</label>
                            <input type="email" name="email" class="form-control shadow-xs" value="{{ old('email') }}" required placeholder="e.g. john@example.com" style="border-radius: 6px;">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Password') }}</label>
                                <input type="password" name="password" class="form-control shadow-xs" required placeholder="Minimum 8 characters" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Confirm Password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control shadow-xs" required placeholder="Retype password" style="border-radius: 6px;">
                            </div>
                        </div>
                    </div>

                    <!-- Reseller Configuration -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-success mb-3 border-bottom pb-2"><i class="bi bi-sliders me-1"></i>{{ __('Reseller Configuration') }}</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Company Name (Optional)') }}</label>
                                <input type="text" name="company" class="form-control shadow-xs" value="{{ old('company') }}" placeholder="e.g. Acme ISP Ltd" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Mobile Number') }}</label>
                                <div wire:ignore>
                                    <input type="text" name="mobile" class="form-control shadow-xs" x-data="intlTelInput()" value="{{ old('mobile') }}" required placeholder="e.g. 01751XXXXXX" style="border-radius: 6px;">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Commission Percentage (%)') }}</label>
                                <input type="number" step="0.1" name="commission_percentage" class="form-control shadow-xs" value="{{ old('commission_percentage', '10.0') }}" required min="0" max="100" placeholder="e.g. 15" style="border-radius: 6px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-muted small">{{ __('Status') }}</label>
                                <select name="status" class="form-select shadow-xs" required style="border-radius: 6px;">
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-2">
                            <label class="form-label fw-bold d-block text-dark border-bottom pb-1 mb-2">{{ __('Voucher & Billing Rules') }}</label>
                            
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="allow_fixed_voucher" value="0">
                                <input class="form-check-input" type="checkbox" name="allow_fixed_voucher" value="1" id="allow_fixed_voucher" {{ old('allow_fixed_voucher', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="allow_fixed_voucher" style="font-size: 0.85rem;">{{ __('Allow Custom/Fixed Amount Vouchers') }}</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="allow_partial_activation" value="0">
                                <input class="form-check-input" type="checkbox" name="allow_partial_activation" value="1" id="allow_partial_activation" {{ old('allow_partial_activation', '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="allow_partial_activation" style="font-size: 0.85rem;">{{ __('Allow Active Connection on Partial Payment') }}</label>
                            </div>
                            
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="use_prorated_validity" value="0">
                                <input class="form-check-input" type="checkbox" name="use_prorated_validity" value="1" id="use_prorated_validity" {{ old('use_prorated_validity', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="use_prorated_validity" style="font-size: 0.85rem;">{{ __('Enable Prorated Daily Validity (Fallback)') }}</label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="pay_discounted_upfront" value="0">
                                <input class="form-check-input" type="checkbox" name="pay_discounted_upfront" value="1" id="pay_discounted_upfront" {{ old('pay_discounted_upfront', '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-dark fw-semibold" for="pay_discounted_upfront" style="font-size: 0.85rem;">{{ __('Pay Discounted Price Upfront (Deduct commission upfront)') }}</label>
                            </div>
                        </div>
                    </div>

                    <!-- Assign Admin Packages -->
                    <div class="col-12 mb-4">
                        <h6 class="fw-bold text-success mb-3 border-bottom pb-2"><i class="bi bi-box-seam me-1"></i>{{ __('Assign Admin Packages') }}</h6>
                        <p class="text-muted small mb-3">{{ __('Select which master billing packages this reseller is authorized to resell to their customers.') }}</p>
                        
                        <div class="row g-2">
                            @forelse($packages as $package)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check card p-2.5 border shadow-none bg-light-subtle h-100 rounded-3">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="packages[]" value="{{ $package->id }}" id="packageCheck{{ $package->id }}" {{ is_array(old('packages')) && in_array($package->id, old('packages')) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold text-dark cursor-pointer d-block" for="packageCheck{{ $package->id }}">
                                            {{ $package->package }}
                                            <span class="d-block text-muted small fw-normal">Price: BDT {{ number_format($package->price, 2) }} | Router: {{ $package->router_name ?? 'Global' }}</span>
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-2">{{ __('No admin packages available. Create master packages first.') }}</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Reseller Permissions -->
                    <div class="col-12 mb-4">
                        <h6 class="fw-bold text-success mb-1 border-bottom pb-2">
                            <i class="bi bi-shield-lock-fill text-success me-2"></i>{{ __('Reseller Permissions') }}
                        </h6>
                        <p class="text-muted small mb-3">{{ __('Select which features this reseller can access from their portal. Leave all unchecked to grant no special access.') }}</p>

                        <div class="row g-3">
                            @foreach($resellerPermissions as $groupName => $permissions)
                                <div class="col-md-6 col-xl-4">
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
                                                        id="perm_create_{{ $permName }}"
                                                        {{ in_array($permName, old('permissions', $defaultPermissions)) ? 'checked' : '' }}>
                                                    <label class="form-check-label small text-dark" for="perm_create_{{ $permName }}">
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
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('admin.resellers.index') }}" wire:navigate class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-2">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-sm btn-success rounded-3 px-4 py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i>{{ __('Save Reseller') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
