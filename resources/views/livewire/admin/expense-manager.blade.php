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
                                </td>
                                <td class="text-muted small">{{ $expense->reference_no ?? '—' }}</td>
                                <td class="small text-muted">{{ $expense->addedBy?->name ?? '—' }}</td>
                                <td class="text-end fw-bold text-danger">৳{{ number_format($expense->amount, 2) }}</td>
                                <td class="text-end pe-4">
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
            setTimeout(() => document.body.removeChild(iframe), 1000);
        }, 500);
    }
</script>
@endpush
