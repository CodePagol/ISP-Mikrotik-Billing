<x-app-layout>
<div class="container-fluid py-4">

    {{-- ── Header ── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1a1f36;">
                <i class="bi bi-graph-up-arrow me-2 text-success"></i>{{ __('Profit & Loss Summary') }}
            </h4>
            <p class="text-muted small mb-0">{{ __('Revenue vs Expenses breakdown') }}</p>
        </div>

        {{-- Month/Year Filter --}}
        <form method="GET" action="{{ route('admin.profit-summary') }}" class="d-flex align-items-center gap-2">
            <select name="month" class="form-select form-select-sm rounded-3" style="width:140px;">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" @selected($num == $month)>{{ __($name) }}</option>
                @endforeach
            </select>
            <select name="year" class="form-select form-select-sm rounded-3" style="width:100px;">
                @foreach($years as $y)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold">
                <i class="bi bi-funnel me-1"></i> {{ __('Apply') }}
            </button>
        </form>
    </div>

    {{-- ── KPI Cards ── --}}
    <div class="row g-3 mb-4">

        {{-- Total Revenue --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 profit-kpi-card" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); border: 1px solid #bbf7d0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 95px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Total Revenue') }}</span>
                        <h4 class="fw-extrabold text-dark mb-1" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">৳{{ number_format($totalRevenue, 2) }}</h4>
                        <span class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.2;">
                            {{ __('ISP') }}: ৳{{ number_format($collectionRevenue, 2) }} &middot; {{ __('Hotspot') }}: ৳{{ number_format($hotspotRevenue, 2) }}
                        </span>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba(16, 185, 129, 0.1); color: #059669; width: 40px; height: 40px;">
                        <i class="bi bi-arrow-down-circle-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reseller Commission --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 profit-kpi-card" style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 95px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Commissions') }}</span>
                        <h4 class="fw-extrabold text-dark mb-1" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">৳{{ number_format($resellerCommissions, 2) }}</h4>
                        <span class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.2;">{{ __('Reseller Commissions') }}</span>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 40px; height: 40px;">
                        <i class="bi bi-person-lines-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Expenses --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 profit-kpi-card" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border: 1px solid #fecaca !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 95px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Expenses') }}</span>
                        <h4 class="fw-extrabold text-dark mb-1" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem;">৳{{ number_format($totalExpenses, 2) }}</h4>
                        <span class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.2;">
                            {{ trans_choice('{1} :count category|[2,*] :count categories', $expensesByCategory->count(), ['count' => $expensesByCategory->count()]) }}
                        </span>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba(239, 68, 68, 0.1); color: #dc2626; width: 40px; height: 40px;">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="col-6 col-md-3">
            @php $isProfitable = $netProfit >= 0; @endphp
            <div class="card border-0 shadow-sm rounded-4 profit-kpi-card" 
                 style="background: linear-gradient(135deg, {{ $isProfitable ? '#f0f9ff, #e0f2fe' : '#fff5f5, #ffe3e3' }}); border: 1px solid {{ $isProfitable ? '#bae6fd' : '#ffd1d1' }} !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between" style="min-height: 95px;">
                    <div>
                        <span class="text-uppercase fw-bold text-muted d-block mb-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ __('Net :type', ['type' => $isProfitable ? __('Profit') : __('Loss')]) }}</span>
                        <h4 class="fw-extrabold mb-1" style="font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -0.5px; font-size: 1.4rem; color: {{ $isProfitable ? '#0369a1' : '#be185d' }};">
                            {{ $isProfitable ? '' : '-' }}৳{{ number_format(abs($netProfit), 2) }}
                        </h4>
                        <span class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.2;">{{ __('After all costs') }}</span>
                    </div>
                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center shadow-sm" style="background: rgba({{ $isProfitable ? '14, 165, 233' : '220, 38, 38' }}, 0.1); color: {{ $isProfitable ? '#0284c7' : '#dc2626' }}; width: 40px; height: 40px;">
                        <i class="bi bi-{{ $isProfitable ? 'graph-up-arrow' : 'graph-down-arrow' }} fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── P&L Statement ── --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 pt-4 px-4 pb-2" style="background:transparent;">
                    <h6 class="fw-bold mb-0"><i class="bi bi-list-columns-reverse me-2 text-primary"></i>{{ __('P&L Statement') }}</h6>
                    <p class="text-muted small mb-0">{{ __($months[$month]) }} {{ $year }}</p>
                </div>
                <div class="card-body px-4">

                    {{-- Revenue rows --}}
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="small text-muted">{{ __('ISP Collections') }}</span>
                        <span class="fw-semibold text-success">+ ৳{{ number_format($collectionRevenue, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="small text-muted">{{ __('Hotspot Sales') }}</span>
                        <span class="fw-semibold text-success">+ ৳{{ number_format($hotspotRevenue, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom bg-success-subtle rounded-3 px-2">
                        <span class="small fw-bold">Total Revenue</span>
                        <span class="fw-bold text-success">৳{{ number_format($totalRevenue, 2) }}</span>
                    </div>

                    <div class="mt-3 mb-1 text-muted" style="font-size:.7rem; letter-spacing:.06em;">{{ __('COSTS & EXPENSES') }}</div>

                    {{-- Commission row --}}
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="small text-muted">
                            <i class="bi bi-person-fill-gear text-warning me-1"></i>{{ __('Reseller Commissions') }}
                        </span>
                        <span class="fw-semibold text-danger">− ৳{{ number_format($resellerCommissions, 2) }}</span>
                    </div>

                    {{-- Expense category rows --}}
                    @php
                        $catColors = ['item_purchase'=>'primary','raw_bill'=>'warning','employee_salary'=>'info','miscellaneous'=>'secondary'];
                        $catIcons  = ['item_purchase'=>'bag-fill','raw_bill'=>'lightning-charge-fill','employee_salary'=>'people-fill','miscellaneous'=>'three-dots'];
                    @endphp
                    @foreach($categories as $key => $label)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="small text-muted">
                            <i class="bi bi-{{ $catIcons[$key] ?? 'circle' }} text-{{ $catColors[$key] ?? 'secondary' }} me-1"></i>{{ __($label) }}
                        </span>
                        <span class="fw-semibold text-danger">− ৳{{ number_format($expensesByCategory[$key] ?? 0, 2) }}</span>
                    </div>
                    @endforeach

                    {{-- Total costs --}}
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom bg-danger-subtle rounded-3 px-2 mt-1">
                        <span class="small fw-bold">{{ __('Total Costs') }}</span>
                        <span class="fw-bold text-danger">৳{{ number_format($resellerCommissions + $totalExpenses, 2) }}</span>
                    </div>

                    {{-- Net profit --}}
                    <div class="d-flex justify-content-between align-items-center py-3 mt-2 rounded-4 px-3"
                         style="background:{{ $isProfitable ? 'linear-gradient(135deg,#e0f2fe,#bae6fd)' : 'linear-gradient(135deg,#fce7f3,#fbcfe8)' }};">
                        <span class="fw-bold">{{ __('Net :type', ['type' => $isProfitable ? __('Profit') : __('Loss')]) }}</span>
                        <span class="fw-bold fs-5" style="color:{{ $isProfitable ? '#0369a1' : '#be185d' }};">
                            {{ $isProfitable ? '' : '-' }}৳{{ number_format(abs($netProfit), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Expense Breakdown Doughnut ── --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 pt-4 px-4 pb-2" style="background:transparent;">
                    <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart-fill me-2 text-danger"></i>{{ __('Expense Breakdown') }}</h6>
                    <p class="text-muted small mb-0">{{ __('By category') }}</p>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <canvas id="expenseDonut" width="220" height="220"></canvas>
                    <div class="mt-3 w-100">
                        @foreach($categories as $key => $label)
                        @php $amount = $expensesByCategory[$key] ?? 0; @endphp
                        @if($amount > 0)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block" style="width:10px;height:10px;background:
                                    {{ ['item_purchase'=>'#3b82f6','raw_bill'=>'#f59e0b','employee_salary'=>'#06b6d4','miscellaneous'=>'#94a3b8'][$key] ?? '#ccc' }};"></span>
                                <span class="small text-muted">{{ __($label) }}</span>
                            </div>
                            <span class="small fw-semibold">৳{{ number_format($amount, 2) }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── 12-Month Trend Chart ── --}}
        <div class="col-md-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 pt-4 px-4 pb-2" style="background:transparent;">
                    <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-line-fill me-2 text-info"></i>{{ __('12-Month Trend') }}</h6>
                    <p class="text-muted small mb-0">{{ __('Revenue vs Expenses') }}</p>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Detailed Item & Employee Expenses ── --}}
    <div class="row g-4 mt-4">
        {{-- Item Purchases --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 pt-4 px-4 pb-2 bg-transparent">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-bag-check-fill me-2 text-primary"></i>{{ __('Item Purchases Breakdown') }}
                        </h6>
                        <span class="badge bg-primary-subtle text-primary rounded-pill">
                            {{ trans_choice('{1} :count item|[2,*] :count items', $itemPurchases->count(), ['count' => $itemPurchases->count()]) }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0">{{ __('Detailed list of purchased items for this month') }}</p>
                </div>
                <div class="card-body px-4 pt-2">
                    @if($itemPurchases->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 mb-2 d-block text-secondary"></i>
                            <span class="small">{{ __('No item purchases recorded this month') }}</span>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light sticky-top" style="z-index: 1;">
                                    <tr>
                                        <th class="small py-2 border-0 text-muted">{{ __('Date') }}</th>
                                        <th class="small py-2 border-0 text-muted">{{ __('Item / Title') }}</th>
                                        <th class="small py-2 border-0 text-muted">{{ __('Ref No.') }}</th>
                                        <th class="small py-2 border-0 text-muted text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemPurchases as $item)
                                        <tr>
                                            <td class="small text-muted py-2 border-bottom-0">{{ $item->expense_date->format('d M, Y') }}</td>
                                            <td class="small py-2 border-bottom-0">
                                                <div class="fw-semibold text-dark">{{ $item->title }}</div>
                                                @if($item->description)
                                                    <div class="text-muted" style="font-size: 0.72rem;">{{ Str::limit($item->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td class="small text-muted py-2 border-bottom-0">{{ $item->reference_no ?? '-' }}</td>
                                            <td class="small fw-bold text-dark text-end py-2 border-bottom-0">৳{{ number_format($item->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Employee Salary / Bills --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 pt-4 px-4 pb-2 bg-transparent">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-people-fill me-2 text-info"></i>{{ __('Employee Salary & Bills') }}
                        </h6>
                        <span class="badge bg-info-subtle text-info rounded-pill">
                            {{ trans_choice('{1} :count bill|[2,*] :count bills', $employeeSalaries->count(), ['count' => $employeeSalaries->count()]) }}
                        </span>
                    </div>
                    <p class="text-muted small mb-0">{{ __('Detailed list of employee payments & claims for this month') }}</p>
                </div>
                <div class="card-body px-4 pt-2">
                    @if($employeeSalaries->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 mb-2 d-block text-secondary"></i>
                            <span class="small">{{ __('No employee salary or bills recorded this month') }}</span>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light sticky-top" style="z-index: 1;">
                                    <tr>
                                        <th class="small py-2 border-0 text-muted">{{ __('Date') }}</th>
                                        <th class="small py-2 border-0 text-muted">{{ __('Employee / Purpose') }}</th>
                                        <th class="small py-2 border-0 text-muted">{{ __('Ref No.') }}</th>
                                        <th class="small py-2 border-0 text-muted text-end">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeeSalaries as $bill)
                                        <tr>
                                            <td class="small text-muted py-2 border-bottom-0">{{ $bill->expense_date->format('d M, Y') }}</td>
                                            <td class="small py-2 border-bottom-0">
                                                <div class="fw-semibold text-dark">{{ $bill->title }}</div>
                                                @if($bill->description)
                                                    <div class="text-muted" style="font-size: 0.72rem;">{{ Str::limit($bill->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td class="small text-muted py-2 border-bottom-0">{{ $bill->reference_no ?? '-' }}</td>
                                            <td class="small fw-bold text-dark text-end py-2 border-bottom-0">৳{{ number_format($bill->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Quick link to expense manager ── --}}
    <div class="mt-4 text-center">
        <a wire:navigate.hover href="{{ route('admin.expenses') }}" class="btn btn-outline-danger rounded-3 px-4 fw-semibold">
            <i class="bi bi-wallet2 me-2"></i>{{ __('Manage Expenses') }}
        </a>
    </div>
</div>

@push('scripts')
<script src="/js/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Expense Doughnut ─────────────────────────────────────────────────
    const donutCtx = document.getElementById('expenseDonut');
    if (donutCtx) {
        const expenseData = @json($expensesByCategory);
        const catKeys   = ['item_purchase', 'raw_bill', 'employee_salary', 'miscellaneous'];
        const catLabels = { item_purchase: '{{ __('Item Purchase') }}', raw_bill: '{{ __('Raw Bill') }}', employee_salary: '{{ __('Employee Salary') }}', miscellaneous: '{{ __('Miscellaneous') }}' };
        const catColors = ['#3b82f6', '#f59e0b', '#06b6d4', '#94a3b8'];

        const data   = catKeys.map(k => expenseData[k] ?? 0);
        const hasAny = data.some(v => v > 0);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: catKeys.map(k => catLabels[k]),
                datasets: [{
                    data: hasAny ? data : [1],
                    backgroundColor: hasAny ? catColors : ['#e5e7eb'],
                    borderWidth: 0,
                }]
            },
            options: {
                cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { enabled: hasAny } },
                animation: { animateScale: true }
            }
        });
    }

    // ── 12-Month Trend ────────────────────────────────────────────────────
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const chartData = @json($chartData);
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: chartData.map(d => d.label),
                datasets: [
                    {
                        label: '{{ __('Revenue') }}',
                        data: chartData.map(d => d.revenue),
                        backgroundColor: 'rgba(16,185,129,.75)',
                        borderRadius: 4,
                    },
                    {
                        label: '{{ __('Expenses') }}',
                        data: chartData.map(d => d.expense),
                        backgroundColor: 'rgba(239,68,68,.65)',
                        borderRadius: 4,
                    },
                    {
                        label: '{{ __('Net Profit') }}',
                        data: chartData.map(d => d.profit),
                        type: 'line',
                        borderColor: '#3b82f6',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: { callback: v => '৳' + v.toLocaleString(), font: { size: 11 } }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }
});
</script>
@endpush

<style>
    .profit-kpi-card {
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .profit-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08) !important;
    }
</style>
</x-app-layout>
