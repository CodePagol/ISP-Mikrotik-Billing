<div>
    <div class="container-fluid py-4">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1f36;">
                    <i class="bi bi-wallet2 me-2 text-danger"></i>{{ __('Expense Management') }}
                </h4>
                <p class="text-muted small mb-0">{{ __('Track all ISP operating costs') }}</p>
            </div>
            <button wire:click="openCreate"
                class="btn btn-danger rounded-3 px-4 fw-semibold shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> {{ __('Add Expense') }}
            </button>
        </div>

        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Category') }}</label>
                        <select wire:model.live="filterCategory" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Month') }}</label>
                        <select wire:model.live="filterMonth" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All Months') }}</option>
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}">{{ __($name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Year') }}</label>
                        <select wire:model.live="filterYear" class="form-select form-select-sm rounded-3">
                            <option value="">{{ __('All Years') }}</option>
                            @foreach($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            @foreach($categories as $key => $label)
                                @if(isset($totals[$key]) && $totals[$key] > 0)
                                    @php
                                        $colorMap = ['item_purchase'=>'primary','raw_bill'=>'warning','employee_salary'=>'info','reseller_payout'=>'purple','miscellaneous'=>'secondary'];
                                        $c = $colorMap[$key] ?? 'secondary';
                                        $style = $c === 'purple' ? 'background:#f3e8ff;color:#7c3aed;border-color:#c4b5fd;' : '';
                                    @endphp
                                    <span class="badge rounded-pill px-3 py-2 small fw-semibold
                                        {{ $c !== 'purple' ? "bg-{$c}-subtle text-{$c} border border-{$c}-subtle" : '' }}"
                                        style="{{ $style }}">
                                        {{ __($label) }}: ৳{{ number_format($totals[$key], 2) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grand Total Banner --}}
        @if($grandTotal > 0)
        <div class="alert rounded-4 border-0 mb-4 py-3 px-4 d-flex align-items-center"
             style="background: linear-gradient(135deg,#fff0f0,#ffe5e5); border-left: 4px solid #ef4444 !important;">
            <i class="bi bi-exclamation-circle-fill text-danger fs-5 me-3"></i>
            <div>
                <span class="fw-semibold text-dark">{{ __('Total Expenses (current filter):') }}</span>
                <span class="fw-bold text-danger fs-5 ms-2">৳{{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
        @endif

        {{-- Expense Table --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8f9fc;">
                            <tr class="small text-muted fw-semibold">
                                <th class="ps-4 py-3">#</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Title / Linked To') }}</th>
                                <th>{{ __('Reference') }}</th>
                                <th>{{ __('Added By') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th class="text-end pe-4">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr wire:key="exp-{{ $expense->id }}">
                                <td class="ps-4 text-muted small">{{ $loop->iteration + ($expenses->currentPage()-1) * $expenses->perPage() }}</td>
                                <td class="small fw-semibold">{{ $expense->expense_date->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $colorMap = ['item_purchase'=>'primary','raw_bill'=>'warning','employee_salary'=>'info','reseller_payout'=>'purple','miscellaneous'=>'secondary'];
                                        $c = $colorMap[$expense->category] ?? 'secondary';
                                        $style = $c === 'purple' ? 'background:#f3e8ff;color:#7c3aed;border:1px solid #c4b5fd;' : '';
                                    @endphp
                                    <span class="badge rounded-pill small px-2
                                        {{ $c !== 'purple' ? "bg-{$c}-subtle text-{$c} border border-{$c}-subtle" : '' }}"
                                        style="{{ $style }}">
                                        {{ __($expense->category_label) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold small text-dark">{{ $expense->title }}</div>
                                    @if($expense->linkedUser)
                                        <div class="text-muted" style="font-size:0.72rem;">
                                            <i class="bi bi-person-fill me-1 text-info"></i>{{ $expense->linkedUser->name }}
                                            @if($expense->linkedUser->mobile) · {{ $expense->linkedUser->mobile }}@endif
                                        </div>
                                    @elseif($expense->linkedReseller)
                                        <div class="text-muted" style="font-size:0.72rem;">
                                            <i class="bi bi-shop me-1" style="color:#7c3aed;"></i>{{ $expense->linkedReseller->user?->name ?? 'Reseller' }}
                                            @if($expense->linkedReseller->company) ({{ $expense->linkedReseller->company }})@endif
                                        </div>
                                    @elseif($expense->description)
                                        <div class="text-muted" style="font-size:0.75rem;">{{ Str::limit($expense->description, 55) }}</div>
                                    @endif

                                    @if($expense->category === 'item_purchase')
                                        @php
                                            $invSummary = $expense->inventory_summary;
                                        @endphp
                                        <div class="mt-1">
                                            @if($invSummary['tracked'])
                                                <button type="button" wire:click="viewExpenseInventory({{ $expense->id }})"
                                                    class="btn btn-xs rounded-pill px-2 py-0 border text-dark bg-white shadow-2xs d-inline-flex align-items-center gap-1"
                                                    style="font-size: 0.70rem;" title="{{ __('View inventory details and deployment history') }}">
                                                    <i class="bi bi-box-seam text-primary"></i>
                                                    <span class="fw-semibold text-success">{{ $invSummary['in_stock'] }} In Stock</span>
                                                    @if($invSummary['deployed'] > 0)
                                                        &bull; <span class="text-primary">{{ $invSummary['deployed'] }} Deployed</span>
                                                    @endif
                                                    @if($invSummary['damaged'] > 0)
                                                        &bull; <span class="text-danger fw-bold">{{ $invSummary['damaged'] }} Damaged</span>
                                                    @endif
                                                </button>
                                            @else
                                                <button type="button" wire:click="openTrackInventory({{ $expense->id }})"
                                                    class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 d-inline-flex align-items-center gap-1"
                                                    style="font-size: 0.70rem;" title="{{ __('Add items from this expense into inventory stock') }}">
                                                    <i class="bi bi-plus-circle"></i> {{ __('Track in Inventory') }}
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $expense->reference_no ?? '—' }}</td>
                                <td class="small text-muted">{{ $expense->addedBy?->name ?? '—' }}</td>
                                <td class="text-end fw-bold text-danger">৳{{ number_format($expense->amount, 2) }}</td>
                                <td class="text-end pe-4">
                                    @if($expense->category === 'item_purchase')
                                        <button wire:click="viewExpenseInventory({{ $expense->id }})"
                                            class="btn btn-sm btn-outline-info rounded-3 me-1 py-1 px-2" title="{{ __('View Inventory Lifecycle') }}">
                                            <i class="bi bi-boxes"></i>
                                        </button>
                                    @endif
                                    <button wire:click="triggerPrint({{ $expense->id }})"
                                        class="btn btn-sm btn-outline-success rounded-3 me-1 py-1 px-2" title="{{ __('Print Voucher') }}">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <button wire:click="openEdit({{ $expense->id }})"
                                        class="btn btn-sm btn-outline-primary rounded-3 me-1 py-1 px-2" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button wire:click="delete({{ $expense->id }})"
                                        wire:confirm="{{ __('Delete this expense?') }}"
                                        class="btn btn-sm btn-outline-danger rounded-3 py-1 px-2" title="{{ __('Delete') }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    {{ __('No expenses found for the selected filters.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($expenses->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $expenses->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Add / Edit Modal ─────────────────────────────────────── --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-{{ $editId ? 'pencil-square text-primary' : 'plus-circle text-danger' }} me-2"></i>
                        {{ $editId ? __('Edit Expense') : __('Add New Expense') }}
                    </h5>
                    <button wire:click="$set('showModal', false)" class="btn-close" type="button"></button>
                </div>
                <div class="modal-body px-4 pt-3">
                    <div class="row g-3">
                        {{-- Category --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('Category') }} <span class="text-danger">*</span></label>
                            <select wire:model.live="category" class="form-select rounded-3 @error('category') is-invalid @enderror">
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ __($label) }}</option>
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Date --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('Expense Date') }} <span class="text-danger">*</span></label>
                            <input wire:model="expense_date" type="date" class="form-control rounded-3 @error('expense_date') is-invalid @enderror">
                            @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- ── Item Purchase: Direct Inventory Tracking ── --}}
                        @if($category === 'item_purchase' && !$editId)
                        <div class="col-12">
                            <div class="card border rounded-3 p-3 bg-light border-primary border-opacity-25 shadow-2xs">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold text-primary small d-flex align-items-center">
                                            <i class="bi bi-box-seam me-2 fs-6"></i>
                                            {{ __('Direct Inventory Stock Tracking') }}
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ __('Automatically add purchased equipment directly into Inventory & Stock Catalog.') }}
                                        </div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="autoTrackInvCheck" wire:model.live="autoTrackInventory">
                                    </div>
                                </div>

                                @if($autoTrackInventory)
                                <div class="row g-2 mt-2 pt-2 border-top">
                                    {{-- Inventory Category --}}
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">{{ __('Inventory Category') }} <span class="text-danger">*</span></label>
                                        <select wire:model.live="invCategoryId" class="form-select form-select-sm rounded-3">
                                            @foreach($inventoryCategories as $icat)
                                                <option value="{{ $icat->id }}">{{ $icat->name }} ({{ ucfirst($icat->tracking_type) }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Tracking Method --}}
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">{{ __('Tracking Method') }}</label>
                                        <select wire:model.live="invTrackingType" class="form-select form-select-sm rounded-3">
                                            <option value="serialized">{{ __('Serialized (Routers, ONUs, Switches)') }}</option>
                                            <option value="bulk">{{ __('Bulk Stock (Quantity/Meters - Cables, RJ45)') }}</option>
                                        </select>
                                    </div>

                                    {{-- Brand & Model --}}
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">{{ __('Brand / Manufacturer') }}</label>
                                        <input type="text" wire:model="invBrand" class="form-control form-control-sm rounded-3" placeholder="{{ __('e.g. TP-Link, BDCOM, Cisco') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">{{ __('Model No') }}</label>
                                        <input type="text" wire:model="invModel" class="form-control form-control-sm rounded-3" placeholder="{{ __('e.g. Archer C6, EPON V2') }}">
                                    </div>

                                    {{-- Quantity & Serials --}}
                                    @if($invTrackingType === 'serialized')
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <label class="form-label small fw-semibold mb-0">
                                                    {{ __('Serial Numbers (Optional - One per line or comma-separated)') }}
                                                </label>
                                                <span class="badge bg-primary rounded-pill px-2" style="font-size: 10px;">
                                                    {{ $invQuantity }} {{ __('Item(s)') }}
                                                </span>
                                            </div>
                                            <textarea wire:model.live.debounce.300ms="invSerials" rows="3" class="form-control form-control-sm font-monospace rounded-3" placeholder="S/N-001&#10;S/N-002&#10;S/N-003"></textarea>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-muted" style="font-size: 10px;">{{ __('Leave blank or enter quantity below to add without serial numbers now.') }}</small>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="small text-muted" style="font-size: 10px;">{{ __('Total Qty:') }}</span>
                                                    <input type="number" wire:model.live="invQuantity" min="1" class="form-control form-control-sm py-0 px-2 text-center rounded-2" style="width: 65px; height: 24px;">
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold mb-1">{{ __('Stock Quantity') }} <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0.01" wire:model="invQuantity" class="form-control form-control-sm rounded-3" placeholder="100">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold mb-1">{{ __('Supplier / Vendor') }}</label>
                                            <input type="text" wire:model="invSupplier" class="form-control form-control-sm rounded-3" placeholder="{{ __('e.g. Star Tech, Multiplan') }}">
                                        </div>
                                    @endif

                                    {{-- Storage Location & Warranty --}}
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">{{ __('Initial Storage Location') }}</label>
                                        <input type="text" wire:model="invLocation" class="form-control form-control-sm rounded-3" placeholder="Main Store">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold mb-1">{{ __('Warranty (Months)') }}</label>
                                        <input type="number" min="0" wire:model="invWarrantyMonths" class="form-control form-control-sm rounded-3" placeholder="12">
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- ── Employee Salary: live-search user ── --}}
                        @if($category === 'employee_salary')
                        <div class="col-12">
                            <div class="alert alert-info border-0 rounded-3 py-2 px-3 small mb-0">
                                <i class="bi bi-person-badge me-1"></i>
                                {{ __('Search and select an employee from the system user list below.') }}
                            </div>
                        </div>
                        <div class="col-12 position-relative">
                            <label class="form-label small fw-semibold">
                                {{ __('Employee (User)') }} <span class="text-muted fw-normal">— {{ __('Optional') }}</span>
                            </label>
                            @if($linkedUserId)
                                {{-- Selected badge --}}
                                <div class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-info-subtle">
                                    <i class="bi bi-person-check-fill text-info fs-5"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small text-dark">{{ $userSearch }}</div>
                                    </div>
                                    <button wire:click="clearLinkedUser" type="button" class="btn btn-sm btn-outline-secondary rounded-3 py-0 px-2">
                                        <i class="bi bi-x"></i> {{ __('Change') }}
                                    </button>
                                </div>
                            @else
                                <input wire:model.live.debounce.300ms="userSearch"
                                    type="text"
                                    placeholder="{{ __('Type name, email or mobile to search…') }}"
                                    class="form-control rounded-3"
                                    autocomplete="off">

                                @if($userSuggestions->isNotEmpty())
                                <div class="position-absolute start-0 end-0 shadow-lg rounded-3 bg-white border mt-1 overflow-hidden"
                                     style="z-index:9999; top:100%;">
                                    @foreach($userSuggestions as $user)
                                    <button wire:click="selectUser({{ $user->id }})"
                                        type="button"
                                        class="d-flex align-items-center gap-3 w-100 px-3 py-2 border-0 bg-transparent text-start hover-bg-light"
                                        style="cursor:pointer; transition:background .15s;"
                                        onmouseover="this.style.background='#f0f9ff'"
                                        onmouseout="this.style.background='transparent'">
                                        <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:34px;height:34px;">
                                            <i class="bi bi-person-fill text-info"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold small text-dark">{{ $user->name }}</div>
                                            <div class="text-muted" style="font-size:.72rem;">
                                                {{ $user->email }}
                                                @if($user->mobile) · {{ $user->mobile }}@endif
                                            </div>
                                        </div>
                                    </button>
                                    @if(!$loop->last)<hr class="my-0">@endif
                                    @endforeach
                                </div>
                                @endif
                            @endif
                        </div>
                        @endif

                        {{-- ── Reseller Commission Payout: select reseller ── --}}
                        @if($category === 'reseller_payout')
                        <div class="col-12">
                            <div class="alert border-0 rounded-3 py-2 px-3 small mb-0"
                                 style="background:#f3e8ff;color:#6d28d9;">
                                <i class="bi bi-info-circle me-1"></i>
                                {!! __('Selecting a reseller will automatically <strong>debit</strong> the payout amount from their wallet balance.') !!}
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">{{ __('Reseller') }} <span class="text-danger">*</span></label>
                            <select wire:model.live="linkedResellerId"
                                class="form-select rounded-3 @error('linkedResellerId') is-invalid @enderror">
                                <option value="">{{ __('— Select Reseller —') }}</option>
                                @foreach($resellers as $r)
                                    <option value="{{ $r->id }}">
                                        {{ $r->user?->name ?? 'Reseller #'.$r->id }}
                                        @if($r->company) ({{ $r->company }})@endif
                                        — {{ __('Balance:') }} ৳{{ number_format($r->balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('linkedResellerId')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            {{-- Show selected reseller balance warning --}}
                            @if($linkedResellerId)
                                @php $selReseller = $resellers->firstWhere('id', $linkedResellerId); @endphp
                                @if($selReseller && $amount && (float)$amount > (float)$selReseller->balance)
                                <div class="alert alert-warning rounded-3 py-2 px-3 small mt-2 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    {!! __('Amount <strong>৳:amount</strong> exceeds reseller balance <strong>৳:balance</strong>. Save will fail.', ['amount' => number_format((float)$amount, 2), 'balance' => number_format($selReseller->balance, 2)]) !!}
                                </div>
                                @elseif($selReseller)
                                <div class="text-muted small mt-1">
                                    <i class="bi bi-wallet2 me-1"></i>
                                    {{ __('Current wallet balance:') }} <strong class="text-success">৳{{ number_format($selReseller->balance, 2) }}</strong>
                                    @if($amount)
                                        → {{ __('After payout:') }} <strong class="text-danger">৳{{ number_format(max(0,(float)$selReseller->balance - (float)$amount), 2) }}</strong>
                                    @endif
                                </div>
                                @endif
                            @endif
                        </div>
                        @endif

                        {{-- Title --}}
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">{{ __('Title') }} <span class="text-danger">*</span></label>
                            <input wire:model="title" type="text" placeholder="{{ __('e.g. Office electricity bill') }}"
                                class="form-control rounded-3 @error('title') is-invalid @enderror">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Amount --}}
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">{{ __('Amount') }} (৳) <span class="text-danger">*</span></label>
                            <input wire:model.live="amount" type="number" step="0.01" min="0" placeholder="0.00"
                                class="form-control rounded-3 @error('amount') is-invalid @enderror">
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Reference --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">{{ __('Reference No') }}</label>
                            <input wire:model="reference_no" type="text" placeholder="{{ __('Auto-generated if blank') }}"
                                class="form-control rounded-3">
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label small fw-semibold">{{ __('Description') }}</label>
                            <textarea wire:model="description" rows="2" placeholder="{{ __('Additional notes…') }}"
                                class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button wire:click="$set('showModal', false)" class="btn btn-light rounded-3 px-4">{{ __('Cancel') }}</button>
                    <button wire:click="save" class="btn btn-danger rounded-3 px-4 fw-semibold">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        {{ $editId ? __('Update Expense') : __('Save Expense') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Print Preview Modal ─────────────────────────────────── --}}
    @if($printExpense)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.6);">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius:12px; overflow:hidden;">

                <div class="modal-header border-0 px-4 py-3" style="background:#f8f9fc;">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-receipt me-2 text-success"></i>
                        {{ __('Expense Voucher —') }} <span class="text-muted">#{{ $printExpense->reference_no }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <button onclick="doPrintExpense()" class="btn btn-success rounded-3 px-4 fw-semibold">
                            <i class="bi bi-printer me-1"></i> {{ __('Print') }}
                        </button>
                        <button wire:click="closePrint" class="btn btn-light rounded-3 px-3">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-0" style="background:#e9ecef;">
                    <div class="d-flex justify-content-center py-4 px-3">

                        <div id="expense-print-section" class="col-md-12 p-0" style='min-height: 297mm; width: 210mm;'>
                            <div class="container-fluid h-100 p-0">
                                <div class="position-relative h-100">
                                    <div class="invoice style1 type3 p-1 h-100">
                                        <div class="position-absolute w-100 top-0 start-0" style="z-index: 0;">
                                            <svg width="100%" height="151" viewBox="0 0 850 151"
                                                preserveAspectRatio="none" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M850 0.889398H0V150.889H184.505C216.239 150.889 246.673 141.531 269.113 124.872L359.112 58.0565C381.553 41.3977 411.987 32.0391 443.721 32.0391H850V0.889398Z"
                                                    fill="#4ce8a7" fill-opacity="0.1"></path>
                                            </svg>
                                        </div>
                                        <div class="p-2 h-100 position-relative" style="z-index: 1;">
                                            <div class="row">
                                                <div class="col ps-0 d-flex align-items-start">
                                                    <img class="img-fluid w-75" src="{{ site_image(siteUrlSettings('site_logo')) }}" alt="Logo">
                                                </div>
                                                <div class="col d-flex justify-content-end align-items-center">
                                                    <div class="fw-bold fs-3 text-uppercase">{{ __('Debit Voucher') }}</div>
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-5 d-flex align-items-center">
                                                    <img class="img-fluid w-100" src="{{ asset('images/arrow_bg.svg') }}" alt="">
                                                </div>
                                                <div class="col-7 invoice_info_list">
                                                    <p class="m-0 z-1 position-relative pe-3">{{ __('Voucher No:') }}
                                                        <b class="text-dark fw-bold">#{{ $printExpense->reference_no }}</b>
                                                    </p>
                                                    <p class="m-0 z-1 position-relative">{{ __('Date:') }} <b class="text-dark fw-bold">{{ $printExpense->expense_date->format('d-M-Y') }}</b></p>
                                                    <div class="invoice_info_list_bg accent_bg_10"></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div><b class="text-dark">{{ __('Paid To:') }}</b></div>
                                                    <div>
                                                        {{ $printExpense->title }} <br>
                                                        @if($printExpense->linkedUser)
                                                            {{ __('Employee:') }} {{ $printExpense->linkedUser->name }} <br>
                                                            @if($printExpense->linkedUser->mobile)
                                                                {{ __('Mobile:') }} {{ $printExpense->linkedUser->mobile }} <br>
                                                            @endif
                                                        @elseif($printExpense->linkedReseller)
                                                            {{ __('Reseller:') }} {{ $printExpense->linkedReseller->user?->name ?? '—' }} <br>
                                                            @if($printExpense->linkedReseller->company)
                                                                {{ __('Company:') }} {{ $printExpense->linkedReseller->company }} <br>
                                                            @endif
                                                        @elseif($printExpense->description)
                                                            {{ $printExpense->description }} <br>
                                                        @endif
                                                        {{ __('Prepared By:') }} {{ $printExpense->addedBy?->name ?? '—' }} <br>
                                                    </div>
                                                </div>
                                                <div class="col text-end">
                                                    <div><b class="text-dark">{{ __('Pay To:') }}</b></div>
                                                    <div>
                                                        {{ siteUrlSettings('site_title') }} <br>
                                                        {{ siteUrlSettings('site_address') }} <br>
                                                        {{ siteUrlSettings('site_phone') }} <br>
                                                        {{ siteUrlSettings('site_email') }} <br>
                                                        {{ config('app.url') }} <br>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-1">
                                                <table class="table mb-1">
                                                    <thead class='table-success'>
                                                        <tr>
                                                            <th>{{ __('Description') }}</th>
                                                            <th>{{ __('Category') }}</th>
                                                            <th class='text-center' colspan="2">{{ __('Expense Info') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                {{ __('Title:') }} {{ $printExpense->title }} <br>
                                                                @if($printExpense->description)
                                                                    {{ __('Note:') }} {{ $printExpense->description }} <br>
                                                                @endif
                                                                {{ __('Voucher No:') }} {{ $printExpense->reference_no }} <br>
                                                                {{ __('Prepared By:') }} {{ $printExpense->addedBy?->name ?? '—' }} <br>
                                                                {{ __('Status :') }} <span class='badge rounded-pill ms-2 badge-subtle-success'>{{ __('Active') }}</span>
                                                            </td>
                                                            <td>
                                                                {{ __($printExpense->category_label) }} <br>
                                                                @if($printExpense->linkedUser)
                                                                    <span class='badge rounded-pill badge-subtle-info'>{{ $printExpense->linkedUser->name }}</span>
                                                                @elseif($printExpense->linkedReseller)
                                                                    <span class='badge rounded-pill badge-subtle-warning'>{{ $printExpense->linkedReseller->user?->name ?? 'Reseller' }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                {{ __('Subtotal:') }} <br>
                                                                {{ __('Total:') }} <br>
                                                            </td>
                                                            <td class="text-end pe-5">
                                                                {{ number_format($printExpense->amount, 2) }}
                                                                {{ siteUrlSettings('site_currency') }}<br>
                                                                {{ number_format($printExpense->amount, 2) }}
                                                                {{ siteUrlSettings('site_currency') }}<br>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div class='col'>
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <p class="mb-1 mt-3"><b class="text-dark">{{ __('Expense info:') }}</b></p>
                                                            <p class="ms-3">
                                                                <span class="text-end">{{ $printExpense->expense_date->format('d-M-Y') }} -> {{ number_format($printExpense->amount, 2) }} {{ siteUrlSettings('site_currency') }}</span><br>
                                                            </p>
                                                        </div>
                                                        <div class="col-5">
                                                            <table class='table'>
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="text-dark fw-bold py-1">{{ __('Subtotal') }}</td>
                                                                        <td class="text-dark fw-bold text-end py-1 pe-4">
                                                                            {{ number_format($printExpense->amount, 2) }}
                                                                            {{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="fw-bold py-1">{{ __('Discount') }}</td>
                                                                        <td class="fw-bold text-end py-1 pe-4">
                                                                            -0.00 {{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-dark fw-bold py-1">{{ __('Grand Total') }}</td>
                                                                        <td class="text-dark fw-bold text-end py-1 pe-4">
                                                                            {{ number_format($printExpense->amount, 2) }}{{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="fw-bold py-1">{{ __('Paid Amount') }}</td>
                                                                        <td class="fw-bold text-end py-1 pe-4">
                                                                            {{ number_format($printExpense->amount, 2) }}{{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-dark fw-bold py-1">{{ __('Balance Due') }}</td>
                                                                        <td class="text-dark fw-bold text-end py-1 pe-4">
                                                                            0.00{{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($printExpense->category === 'item_purchase' && $printExpense->inventoryItems->isNotEmpty())
                                                <div class="row px-4 pt-2 mb-3">
                                                    <div class="col-12">
                                                        <div class="text-dark fw-bold mb-1 d-flex align-items-center small">
                                                            <i class="bi bi-box-seam text-primary me-2"></i>
                                                            {{ __('Purchased Inventory Equipment Linked with this Voucher:') }}
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered small mb-0" style="font-size: 11px;">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th style="width: 30px;">#</th>
                                                                        <th>{{ __('Item / Model') }}</th>
                                                                        <th>{{ __('Category') }}</th>
                                                                        <th>{{ __('Serial Number / MAC') }}</th>
                                                                        <th>{{ __('Qty') }}</th>
                                                                        <th>{{ __('Current Status') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($printExpense->inventoryItems as $pIdx => $pItem)
                                                                        <tr>
                                                                            <td>{{ $pIdx + 1 }}</td>
                                                                            <td class="fw-bold">{{ $pItem->title }}</td>
                                                                            <td>{{ $pItem->category?->name ?? '—' }}</td>
                                                                            <td>
                                                                                @if($pItem->serial_number)
                                                                                    <code>{{ $pItem->serial_number }}</code>
                                                                                @elseif($pItem->mac_address)
                                                                                    <small>{{ $pItem->mac_address }}</small>
                                                                                @else
                                                                                    <span class="text-muted">—</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ $pItem->quantity }} {{ $pItem->category?->unit ?? 'pcs' }}</td>
                                                                            <td>
                                                                                <span class="badge badge-subtle-{{ $pItem->status_color }} rounded-pill px-2.5 py-1">{{ $pItem->status_label }}</span>
                                                                                @if($pItem->current_location_name)
                                                                                    <small class="text-muted ms-1">({{ $pItem->current_location_name }})</small>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="row p-4 pt-0">
                                                <div class="text-dark fw-bold">{{ __('Terms & Conditions:') }}</div>
                                                <ul>
                                                    <li>{{ __('This is an official expense voucher document.') }}</li>
                                                    <li>{{ __('All payments are processed under corporate approval.') }}</li>
                                                    <li>{{ __('Supported receipts must be archived with finance.') }}</li>
                                                    <li>{{ __('Disputes must be raised within 7 days of the voucher date.') }}</li>
                                                    <li>{{ __('Unauthorized alterations to this document are strictly prohibited.') }}</li>
                                                </ul>
                                            </div>

                                            <div class="pt-5 text-center">
                                                {{ __('***This is a computer generated voucher. No signature required***') }}
                                            </div>
                                        </div>
                                        <div class="position-absolute w-100 bottom-0 start-0" style="z-index: 0;">
                                            <svg width="100%" height="151" viewBox="0 0 850 151"
                                                preserveAspectRatio="none" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M0 150.889H850V0.889408H665.496C633.762 0.889408 603.327 10.2481 580.887 26.9081L490.888 93.7224C468.447 110.381 438.014 119.74 406.279 119.74H0V150.889Z"
                                                    fill="#4ce8a7" fill-opacity="0.1"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-0 px-4 py-3" style="background:#f8f9fc;">
                    <button wire:click="closePrint" class="btn btn-light rounded-3 px-4">{{ __('Close') }}</button>
                    <button onclick="doPrintExpense()" class="btn btn-success rounded-3 px-5 fw-semibold">
                        <i class="bi bi-printer me-2"></i>{{ __('Print Voucher') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ── 1. View Expense Inventory Lifecycle Modal ────────────────────── --}}
    @if($showExpenseInventoryModal && $inventoryModalExpense)
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.55);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 text-xs">
                                <i class="bi bi-box-seam me-1"></i> {{ __('Purchased Equipment Lifecycle') }}
                            </span>
                            <span class="text-muted small">Ref: {{ $inventoryModalExpense->reference_no }}</span>
                        </div>
                        <h5 class="modal-title fw-bold mt-1 text-dark">{{ $inventoryModalExpense->title }}</h5>
                        <div class="text-muted small">
                            <span>{{ __('Purchase Date') }}: <strong>{{ $inventoryModalExpense->expense_date->format('d M Y') }}</strong></span> &bull;
                            <span>{{ __('Expense Amount') }}: <strong class="text-danger">৳{{ number_format($inventoryModalExpense->amount, 2) }}</strong></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeExpenseInventory"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    {{-- Summary Metric Counters --}}
                    @php
                        $totQty = $expenseInventoryItems->sum('quantity');
                        $inStock = $expenseInventoryItems->where('status', 'in_stock')->sum('quantity');
                        $deployed = $expenseInventoryItems->where('status', 'deployed')->sum('quantity');
                        $damaged = $expenseInventoryItems->where('status', 'damaged')->sum('quantity');
                        $underRepair = $expenseInventoryItems->where('status', 'under_repair')->sum('quantity');
                    @endphp
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-light border text-center">
                                <div class="text-muted text-xs text-uppercase fw-semibold mb-1">{{ __('Total Units') }}</div>
                                <h4 class="fw-bold mb-0 text-dark">{{ $totQty }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-success-subtle border border-success-subtle text-center">
                                <div class="text-success text-xs text-uppercase fw-semibold mb-1">{{ __('In Stock (Available)') }}</div>
                                <h4 class="fw-bold mb-0 text-success">{{ $inStock }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-primary-subtle border border-primary-subtle text-center">
                                <div class="text-primary text-xs text-uppercase fw-semibold mb-1">{{ __('Deployed (To Customers)') }}</div>
                                <h4 class="fw-bold mb-0 text-primary">{{ $deployed }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-danger-subtle border border-danger-subtle text-center">
                                <div class="text-danger text-xs text-uppercase fw-semibold mb-1">{{ __('Damaged / Nosto') }}</div>
                                <h4 class="fw-bold mb-0 text-danger">{{ $damaged + $underRepair }}</h4>
                            </div>
                        </div>
                    </div>

                    {{-- Items Details Table --}}
                    @if($expenseInventoryItems->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-box-seam fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted">{{ __('No inventory stock items have been linked to this expense yet.') }}</h6>
                            <button type="button" wire:click="openTrackInventory({{ $inventoryModalExpense->id }})" class="btn btn-primary rounded-3 px-4 mt-2">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Track in Inventory Stock Now') }}
                            </button>
                        </div>
                    @else
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0 text-sm">
                                <thead class="table-light">
                                    <tr class="text-muted text-xs text-uppercase">
                                        <th class="ps-3 py-2.5">#</th>
                                        <th>{{ __('Item / Identifiers') }}</th>
                                        <th>{{ __('Stock Status') }}</th>
                                        <th>{{ __('Where Used / Assigned Customer') }}</th>
                                        <th>{{ __('Withdraw Date / Tech') }}</th>
                                        <th>{{ __('Condition / Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenseInventoryItems as $idx => $invItem)
                                        @php
                                            $activeAssign = $invItem->assignments->first();
                                        @endphp
                                        <tr>
                                            <td class="ps-3 text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $invItem->title }}</div>
                                                <div class="text-muted text-xs">
                                                    @if($invItem->serial_number)
                                                        <span class="badge bg-light text-dark border">SN: <code>{{ $invItem->serial_number }}</code></span>
                                                    @endif
                                                    @if($invItem->mac_address)
                                                        <span class="ms-1 text-muted">MAC: <code>{{ $invItem->mac_address }}</code></span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $stBadge = [
                                                        'in_stock'     => 'badge-subtle-success',
                                                        'deployed'     => 'badge-subtle-primary',
                                                        'damaged'      => 'badge-subtle-danger',
                                                        'under_repair' => 'badge-subtle-warning',
                                                    ][$invItem->status] ?? 'badge-subtle-secondary';
                                                @endphp
                                                <span class="badge {{ $stBadge }} rounded-pill px-2.5 py-1 text-xs fw-semibold">
                                                    {{ $invItem->status_label }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($invItem->status === 'deployed')
                                                    @if($invItem->customer)
                                                        <div class="fw-semibold text-primary">
                                                            <i class="bi bi-person-fill me-1"></i>{{ $invItem->customer->customer_name }}
                                                            <span class="text-muted">({{ $invItem->customer->customer_unique_id }})</span>
                                                        </div>
                                                        <div class="text-muted text-xs">{{ $invItem->customer->address }}</div>
                                                    @else
                                                        <div class="fw-semibold text-dark"><i class="bi bi-geo-alt me-1 text-danger"></i>{{ $invItem->current_location_name }}</div>
                                                    @endif
                                                @elseif($invItem->status === 'in_stock')
                                                    <span class="text-muted"><i class="bi bi-building me-1"></i>{{ $invItem->current_location_name }}</span>
                                                @elseif($invItem->status === 'damaged')
                                                    <span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ __('Damaged / Faulty') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($activeAssign)
                                                    <div><i class="bi bi-calendar-event me-1 text-muted"></i>{{ $activeAssign->withdraw_date?->format('d M Y') ?? '—' }}</div>
                                                    @if($activeAssign->technician)
                                                        <div class="text-muted text-xs"><i class="bi bi-wrench me-1"></i>{{ $activeAssign->technician->name }}</div>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($invItem->status === 'damaged')
                                                    <div class="text-danger small">
                                                        <strong>{{ $invItem->damage_type ?? 'Nosto' }}:</strong> {{ Str::limit($invItem->notes, 60) }}
                                                    </div>
                                                @else
                                                    <div class="text-muted small">{{ Str::limit($invItem->notes, 60) ?: '—' }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="modal-footer border-0 px-4 py-3" style="background:#f8f9fc;">
                    <a href="{{ route('admin.inventory.items') }}" class="btn btn-outline-primary rounded-3 px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('Open Inventory Manager') }}
                    </a>
                    <button type="button" wire:click="closeExpenseInventory" class="btn btn-light rounded-3 px-4">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── 2. Track in Inventory Stock Modal ────────────────────────────── --}}
    @if($showTrackModal)
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.55);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>{{ __('Add Expense Items to Inventory Stock') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Create inventory records from this purchase to track customer deployment and damage.') }}</p>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeTrackModal"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">{{ __('Item Category') }} <span class="text-danger">*</span></label>
                            <select wire:model.live="trackCategoryId" class="form-select rounded-3">
                                @foreach($inventoryCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->unit }})</option>
                                @endforeach
                            </select>
                            @error('trackCategoryId') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">{{ __('Tracking Type') }} <span class="text-danger">*</span></label>
                            <select wire:model.live="trackTrackingType" class="form-select rounded-3">
                                <option value="serialized">{{ __('Serialized (ONU, Router, Switch with unique SN)') }}</option>
                                <option value="bulk">{{ __('Bulk Stock (Cable meters, Connectors, Patch cords)') }}</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">{{ __('Quantity / Units') }} <span class="text-danger">*</span></label>
                            <input type="number" step="any" wire:model="trackQuantity" class="form-control rounded-3" placeholder="e.g. 10">
                            @error('trackQuantity') <span class="text-danger text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">{{ __('Brand / Make') }}</label>
                            <input type="text" wire:model="trackBrand" class="form-control rounded-3" placeholder="e.g. BDCOM, TP-Link">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">{{ __('Model No') }}</label>
                            <input type="text" wire:model="trackModel" class="form-control rounded-3" placeholder="e.g. GPON ONU 1000R">
                        </div>

                        @if($trackTrackingType === 'serialized')
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small fw-semibold text-dark mb-0">{{ __('Serial Numbers (Paste or Scan)') }}</label>
                                <span class="text-muted text-xs">{{ __('One serial number per line (Optional — if blank, auto-generates unit slots)') }}</span>
                            </div>
                            <textarea wire:model="trackSerialNumbers" rows="4" class="form-control font-monospace rounded-3"
                                placeholder="SN123456789&#10;SN123456790&#10;SN123456791"></textarea>
                        </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">{{ __('Supplier / Vendor') }}</label>
                            <input type="text" wire:model="trackSupplier" class="form-control rounded-3" placeholder="e.g. Smart Technologies Ltd">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">{{ __('Warranty (Months)') }}</label>
                            <input type="number" wire:model="trackWarrantyMonths" class="form-control rounded-3" placeholder="e.g. 12 or 24">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 py-3" style="background:#f8f9fc;">
                    <button type="button" wire:click="closeTrackModal" class="btn btn-light rounded-3 px-4">{{ __('Cancel') }}</button>
                    <button type="button" wire:click="submitTrackInventory" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> {{ __('Save to Inventory Stock') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    function doPrintExpense() {
        const printElement = document.getElementById('expense-print-section');
        if (!printElement) {
            console.error('[ExpensePrint] #expense-print-section not found');
            return;
        }

        const iframe = document.createElement('iframe');
        iframe.style.position = 'fixed';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.visibility = 'hidden';
        iframe.style.border = 'none';
        iframe.style.zIndex = '-1';
        document.body.appendChild(iframe);

        let cssLinks = Array.from(document.querySelectorAll('link[rel=stylesheet]'))
            .map(link => `<link rel="stylesheet" href="${link.href}">`)
            .join('');

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write('<!DOCTYPE html><html><head><title>Expense Voucher</title>');
        doc.write(cssLinks);
        doc.write('<style>body { background-color: white !important; margin: 0; padding: 0; } * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }</style>');
        doc.write('</head><body>');
        doc.write(printElement.outerHTML);
        doc.write('</body></html>');
        doc.close();

        setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => {
                if (iframe && typeof iframe.remove === 'function') {
                    iframe.remove();
                } else if (iframe && iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
            }, 1000);
        }, 500);
    }
</script>
@endpush
