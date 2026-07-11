<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            <i class="bi bi-people-fill me-2 text-success"></i>{{ __('Reseller Management') }}
        </h2>
    </x-slot>

    {{-- Month/Year Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2 bg-success-subtle text-success">
                    <i class="bi bi-funnel fs-5"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ __('Filter Statistics') }}</h6>
                    <small class="text-muted">
                        @if($month === 'all')
                            {{ __('Showing all-time collections and profit') }}
                        @else
                            {{ __('Showing statistics for') }} {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                        @endif
                    </small>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.resellers.index') }}" class="d-flex align-items-center gap-2">
                <select name="month" id="month-select" class="form-select form-select-sm rounded-3" style="width:140px;">
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" @selected((string)$num === (string)$month)>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="year" id="year-select" class="form-select form-select-sm rounded-3" style="width:100px;" @disabled($month === 'all')>
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold">
                    {{ __('Apply Filter') }}
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleYearSelect() {
            const monthSelect = document.getElementById('month-select');
            const yearSelect = document.getElementById('year-select');
            if (monthSelect && yearSelect) {
                yearSelect.disabled = (monthSelect.value === 'all');
            }
        }
        document.addEventListener('DOMContentLoaded', toggleYearSelect);
        document.addEventListener('livewire:navigated', toggleYearSelect);
        document.addEventListener('DOMContentLoaded', function() {
            const monthSelect = document.getElementById('month-select');
            if (monthSelect) monthSelect.addEventListener('change', toggleYearSelect);
        });
        document.addEventListener('livewire:navigated', function() {
            const monthSelect = document.getElementById('month-select');
            if (monthSelect) monthSelect.addEventListener('change', toggleYearSelect);
        });
    </script>

    {{-- ── Reseller Module KPI Summary ── --}}
    <div class="row g-3 mb-4">
        {{-- Total Collections --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 reseller-kpi-card" style="background: linear-gradient(135deg, #f0f9ff, #e0f2fe); border: 1px solid #bae6fd !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 85px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Total Collections') }}</span>
                        <h4 class="fw-extrabold text-dark mb-0" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">৳{{ number_format($resellers->sum('total_collected'), 2) }}</h4>
                    </div>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.1); color: #0284c7; width: 40px; height: 40px;">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Reseller Profit --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 reseller-kpi-card" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 85px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Resellers Profit') }}</span>
                        <h4 class="fw-extrabold text-dark mb-0" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">৳{{ number_format($resellers->sum('total_profit'), 2) }}</h4>
                    </div>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(40, 167, 69, 0.15); color: #28a745; width: 40px; height: 40px;">
                        <i class="bi bi-graph-up-arrow fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Paid Out (Cash) --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 reseller-kpi-card" style="background: linear-gradient(135deg, #faf5ff, #f3e8ff); border: 1px solid #d8b4fe !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 85px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Total Paid Out (Cash)') }}</span>
                        <h4 class="fw-extrabold text-dark mb-0" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">৳{{ number_format($resellers->sum('total_payout'), 2) }}</h4>
                    </div>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed; width: 40px; height: 40px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Resellers --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 reseller-kpi-card" style="background: linear-gradient(135deg, #f0fdfa, #ccfbf1); border: 1px solid #99f6e4 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 85px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Active Resellers') }}</span>
                        <h4 class="fw-extrabold text-dark mb-0" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">{{ $resellers->where('status', 'active')->count() }}</h4>
                    </div>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(13, 148, 136, 0.1); color: #0d9488; width: 40px; height: 40px;">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Suspended Resellers --}}
        <div class="col-xl col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 reseller-kpi-card" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #fecaca !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 85px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Suspended') }}</span>
                        <h4 class="fw-extrabold text-dark mb-0" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">{{ $resellers->where('status', 'suspended')->count() }}</h4>
                    </div>
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(220, 38, 38, 0.1); color: #dc2626; width: 40px; height: 40px;">
                        <i class="bi bi-person-x-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-badge-fill text-success me-2"></i>{{ __('All Resellers') }}</h5>
            <a href="{{ route('admin.resellers.create') }}" wire:navigate class="btn btn-sm btn-success rounded-3"><i class="bi bi-plus-lg me-1"></i>{{ __('Create Reseller') }}</a>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-success text-success">
                        <tr>
                            <th class="ps-4 py-3">{{ __('Name') }}</th>
                            <th>{{ __('Email / Phone') }}</th>
                            <th>{{ __('Company') }}</th>
                            <th class="text-center">{{ __('Commission') }}</th>
                            <th class="text-end">
                                @if($month === 'all')
                                    {{ __('Total Collection') }}
                                @else
                                    {{ __('Collection') }} ({{ $months[$month] }})
                                @endif
                            </th>
                            <th class="text-end">
                                @if($month === 'all')
                                    {{ __('Total Profit') }}
                                @else
                                    {{ __('Profit') }} ({{ $months[$month] }})
                                @endif
                            </th>
                            <th class="text-end">{{ __('Wallet Balance') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center pe-4">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resellers as $reseller)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $reseller->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">ID: #{{ $reseller->id }}</small>
                                </td>
                                <td>
                                    <div>{{ $reseller->user->email ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $reseller->phone ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $reseller->company ?? 'N/A' }}</td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success border border-success px-2 py-1">{{ $reseller->commission_percentage }}%</span></td>
                                <td class="text-end text-muted">৳{{ number_format($reseller->totalCollections(), 2) }}</td>
                                <td class="text-end fw-bold text-success">৳{{ number_format($reseller->totalProfit(), 2) }}</td>
                                <td class="text-end fw-bold text-success">৳{{ number_format($reseller->balance, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $reseller->status === 'active' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' }} px-2.5 py-1 text-uppercase" style="font-size: 0.72rem;">
                                        {{ __($reseller->status) }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Adjust Balance Button -->
                                        <button type="button" class="btn btn-xs btn-outline-success p-1" style="border-radius: 4px;" data-bs-toggle="modal" data-bs-target="#adjustModal{{ $reseller->id }}" title="{{ __('Adjust Wallet') }}">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                        <!-- View Ledger Button -->
                                        <button type="button" class="btn btn-xs btn-outline-info p-1 view-transactions-btn" style="border-radius: 4px;" data-reseller-id="{{ $reseller->id }}" title="{{ __('View Ledger') }}">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                        <!-- Edit -->
                                        <a href="{{ route('admin.resellers.edit', $reseller->id) }}" wire:navigate class="btn btn-xs btn-outline-primary p-1" style="border-radius: 4px;" title="{{ __('Edit Reseller') }}"><i class="bi bi-pencil-square"></i></a>
                                        <!-- Delete -->
                                        <form action="{{ route('admin.resellers.destroy', $reseller->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this reseller?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger p-1" style="border-radius: 4px;" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>

                                    <!-- Adjust Balance Modal -->
                                    <div class="modal fade text-start" id="adjustModal{{ $reseller->id }}" tabindex="-1" aria-labelledby="adjustModalLabel{{ $reseller->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form action="{{ route('admin.resellers.adjust-balance', $reseller->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content border-0 shadow-lg rounded-3 bg-white text-dark">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold text-success" id="adjustModalLabel{{ $reseller->id }}">{{ __('Adjust Wallet:') }} {{ $reseller->user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-muted mb-1">{{ __('Adjustment Type') }}</label>
                                                            <select name="type" class="form-select" required>
                                                                <option value="credit">{{ __('Credit (Add Balance)') }}</option>
                                                                <option value="debit">{{ __('Debit (Deduct Balance)') }}</option>
                                                                <option value="payout">{{ __('Commission Payout (Deduct Balance)') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-muted mb-1">{{ __('Amount (BDT)') }}</label>
                                                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required min="0.01">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-muted mb-1">{{ __('Description / Reason') }}</label>
                                                            <textarea name="description" class="form-control" rows="3" placeholder="{{ __('e.g. Received cash payment from reseller / Withdrawal request') }}" required></textarea>
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
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">{{ __('No resellers found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transactions Modal -->
    <div class="modal fade" id="transactionsModal" tabindex="-1" aria-labelledby="transactionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-3 text-start bg-white text-dark">
                <div class="modal-header border-0 bg-light py-3 px-4">
                    <h5 class="modal-title fw-bold text-success" id="transactionsModalLabel">
                        <i class="bi bi-wallet2 me-2"></i>{{ __('Wallet Ledger:') }} <span id="modalResellerName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Filters -->
                    <div class="row g-2 mb-4 align-items-end bg-light p-3 rounded-3 border">
                        <div class="col-sm-3">
                            <label class="form-label small fw-bold text-muted mb-1">{{ __('Start Date') }}</label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-3">
                            <label class="form-label small fw-bold text-muted mb-1">{{ __('End Date') }}</label>
                            <input type="date" id="filterEndDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label small fw-bold text-muted mb-1">{{ __('Transaction Type') }}</label>
                            <select id="filterTxType" class="form-select form-select-sm">
                                <option value="all">{{ __('All Transactions') }}</option>
                                <option value="payout_only">{{ __('Commission Payouts Only') }}</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" id="btnApplyFilter" class="btn btn-sm btn-success w-100 fw-semibold">
                                <i class="bi bi-funnel-fill me-1"></i>{{ __('Filter') }}
                            </button>
                        </div>
                    </div>

                    <!-- Loader -->
                    <div id="transactionsLoader" class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Table container -->
                    <div id="transactionsTableContainer" class="d-none">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0" style="font-size: 0.85rem;">
                                <thead class="table-success text-success text-center">
                                    <tr class="fw-semibold">
                                        <th class="py-2">{{ __('Date') }}</th>
                                        <th class="py-2 text-start">{{ __('Description') }}</th>
                                        <th class="py-2">{{ __('Reference') }}</th>
                                        <th class="py-2 text-end">{{ __('Amount') }}</th>
                                        <th class="py-2 text-center">{{ __('Type') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsList">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-3 bg-light">
                    <button type="button" class="btn btn-sm btn-light rounded-3 px-4 py-2" data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        if (typeof window.transactionsInitialized === 'undefined') {
            window.transactionsInitialized = true;
            
            document.addEventListener('click', function (event) {
                const btn = event.target.closest('.view-transactions-btn');
                if (btn) {
                    const activeResellerId = btn.getAttribute('data-reseller-id');
                    
                    // Reset filters
                    const filterStartDate = document.getElementById('filterStartDate');
                    const filterEndDate = document.getElementById('filterEndDate');
                    const filterTxType = document.getElementById('filterTxType');
                    if (filterStartDate) filterStartDate.value = '';
                    if (filterEndDate) filterEndDate.value = '';
                    if (filterTxType) filterTxType.value = 'all';

                    const modalEl = document.getElementById('transactionsModal');
                    if (modalEl) {
                        modalEl.setAttribute('data-active-reseller-id', activeResellerId);
                        
                        const transactionsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        transactionsModal.show();
                        loadTransactionsList(activeResellerId);
                    }
                }
            });

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('#btnApplyFilter');
                if (btn) {
                    const modalEl = document.getElementById('transactionsModal');
                    if (modalEl) {
                        const activeResellerId = modalEl.getAttribute('data-active-reseller-id');
                        if (activeResellerId) {
                            loadTransactionsList(activeResellerId);
                        }
                    }
                }
            });
        }

        function loadTransactionsList(resellerId) {
            const startDateEl = document.getElementById('filterStartDate');
            const endDateEl = document.getElementById('filterEndDate');
            const txTypeEl = document.getElementById('filterTxType');
            
            const startDate = startDateEl ? startDateEl.value : '';
            const endDate = endDateEl ? endDateEl.value : '';
            const txType = txTypeEl ? txTypeEl.value : 'all';
            
            const loader = document.getElementById('transactionsLoader');
            const container = document.getElementById('transactionsTableContainer');
            if (loader) loader.classList.remove('d-none');
            if (container) container.classList.add('d-none');

            let url = `/admin/resellers/${resellerId}/transactions`;
            const params = [];
            if (startDate) params.push(`start_date=${startDate}`);
            if (endDate) params.push(`end_date=${endDate}`);
            if (txType) params.push(`tx_type=${txType}`);
            if (params.length > 0) url += `?${params.join('&')}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const modalTitle = document.getElementById('modalResellerName');
                    if (modalTitle) modalTitle.textContent = data.reseller_name;
                    
                    const tbody = document.getElementById('transactionsList');
                    if (tbody) {
                        tbody.innerHTML = '';
                        if (data.transactions.length === 0) {
                            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small">No transactions found for the selected range.</td></tr>`;
                        } else {
                            data.transactions.forEach(trx => {
                                const typeClass = trx.type === 'credit' ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger';
                                const refBadge = trx.reference_type !== '-' ? `<span class="badge bg-secondary-subtle text-secondary text-xs">${trx.reference_type} (#${trx.reference_id})</span>` : '-';
                                
                                const row = `
                                    <tr>
                                        <td class="small text-muted text-center">${trx.date}</td>
                                        <td class="small text-dark fw-semibold text-start">${trx.description}</td>
                                        <td class="text-center">${refBadge}</td>
                                        <td class="text-end fw-bold">৳${trx.amount}</td>
                                        <td class="text-center">
                                            <span class="badge ${typeClass} px-2 py-0.5 text-uppercase text-xs" style="font-size:0.7rem;">
                                                ${trx.type}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                                tbody.insertAdjacentHTML('beforeend', row);
                            });
                        }
                    }

                    if (loader) loader.classList.add('d-none');
                    if (container) container.classList.remove('d-none');
                })
                .catch(err => {
                    console.error(err);
                    if (loader) loader.classList.add('d-none');
                    const tbody = document.getElementById('transactionsList');
                    if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger small">Failed to load transactions.</td></tr>`;
                    if (container) container.classList.remove('d-none');
                });
        }

        document.addEventListener('livewire:navigated', function () {
            // Remove any leftover modal backdrops from the body when navigating
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    </script>

<style>
    .reseller-kpi-card {
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reseller-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08) !important;
    }
</style>
</x-app-layout>
