<x-app-layout>
    <x-slot name="header">
        {{ __('Reseller Dashboard') }}
    </x-slot>

    <!-- Reseller Stat Cards Row -->
    <div class="row g-3 mb-4">
        <!-- Wallet Balance -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff; border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body position-relative z-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1 text-white-50" style="font-size: 0.72rem; letter-spacing: 1px;">{{ __('Wallet Balance') }}</h6>
                            <h3 class="fw-bold mb-0">৳{{ number_format($walletBalance, 2) }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-wallet2 fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                        {{ __('Available for withdrawals or voucher generations.') }}
                    </div>
                </div>
                <div class="position-absolute end-0 bottom-0" style="opacity: 0.08; transform: translate(10%, 10%); z-index: 0; pointer-events: none;">
                    <i class="bi bi-wallet2" style="font-size: 5.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Total Customers -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); color: #fff; border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body position-relative z-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1 text-white-50" style="font-size: 0.72rem; letter-spacing: 1px;">{{ __('My Customers') }}</h6>
                            <h3 class="fw-bold mb-0">{{ $totalCustomers }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                        {{ __('Isolated customers registered under your account.') }}
                    </div>
                </div>
                <div class="position-absolute end-0 bottom-0" style="opacity: 0.08; transform: translate(10%, 10%); z-index: 0; pointer-events: none;">
                    <i class="bi bi-people-fill" style="font-size: 5.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Earnings Monthly / Today -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body position-relative z-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1 text-white-50" style="font-size: 0.72rem; letter-spacing: 1px;">{{ __('Monthly Earnings') }}</h6>
                            <h3 class="fw-bold mb-0">৳{{ number_format($earningsMonth, 2) }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top border-white border-opacity-10 d-flex justify-content-between small text-white-50">
                        <span>{{ __('Today:') }} ৳{{ number_format($earningsToday, 2) }}</span>
                        <span>{{ __('Total:') }} ৳{{ number_format($totalEarnings, 2) }}</span>
                    </div>
                </div>
                <div class="position-absolute end-0 bottom-0" style="opacity: 0.08; transform: translate(10%, 10%); z-index: 0; pointer-events: none;">
                    <i class="bi bi-graph-up-arrow" style="font-size: 5.5rem;"></i>
                </div>
            </div>
        </div>

        <!-- Active Vouchers -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border-radius: 12px; transition: transform 0.3s ease;">
                <div class="card-body position-relative z-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase fw-bold mb-1 text-white-50" style="font-size: 0.72rem; letter-spacing: 1px;">{{ __('Active Vouchers') }}</h6>
                            <h3 class="fw-bold mb-0">{{ $activeVouchersCount }}</h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(255,255,255,0.15);">
                            <i class="bi bi-ticket-perforated-fill fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top border-white border-opacity-10 small text-white-50">
                        {{ __('Unused and unexpired vouchers generated by you.') }}
                    </div>
                </div>
                <div class="position-absolute end-0 bottom-0" style="opacity: 0.08; transform: translate(10%, 10%); z-index: 0; pointer-events: none;">
                    <i class="bi bi-ticket-perforated-fill" style="font-size: 5.5rem;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Packages Breakdown Row -->
    <div class="row g-4 mb-4">
        <!-- 7-Day Earnings Trend Chart -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-graph-up text-primary me-2"></i>{{ __('Commission Earnings Trend (Last 7 Days)') }}</h6>
                </div>
                <div class="card-body">
                    <div id="resellerEarningsChart" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>

        <!-- Package Sales Stats -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill text-success me-2"></i>{{ __('Package Distribution') }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($packageSales as $sale)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2.5 px-3">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $sale->package->package ?? __('Custom / Deleted') }}</div>
                                    <small class="text-muted">{{ __('Speed:') }} {{ $sale->package->speed ?? 'N/A' }} | {{ __('Price:') }} ৳{{ number_format($sale->package->price ?? 0, 0) }}</small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">
                                    {{ trans_choice('{1} :count User|[2,*] :count Users', $sale->count, ['count' => $sale->count]) }}
                                </span>
                            </div>
                        @empty
                            <div class="py-4 text-center text-muted small">{{ __('No packages assigned to any customers.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Customers & Quick Actions -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-info me-2"></i>{{ __('Recently Added Customers') }}</h6>
                    <a href="{{ route('reseller.customers.index') }}" class="btn btn-sm btn-outline-primary rounded-3">{{ __('View All') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-3">{{ __('Name / Unique ID') }}</th>
                                    <th>{{ __('Mobile') }}</th>
                                    <th>{{ __('Package') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCustomers as $cust)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $cust->customer_name }}</div>
                                            <small class="text-muted">{{ $cust->customer_unique_id }}</small>
                                        </td>
                                        <td>{{ $cust->mobile }}</td>
                                        <td>{{ $cust->package->package ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $cust->status === 'active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} text-uppercase px-2 py-0.5 text-xs">
                                                {{ __($cust->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">{{ __('No customers registered yet.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-lightning-fill text-warning me-2"></i>{{ __('Quick Actions') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('reseller.customers.create') }}" class="btn btn-primary py-2.5 rounded-3 text-start"><i class="bi bi-person-fill-add me-2 fs-5"></i>{{ __('Register New Customer') }}</a>
                        <a href="{{ route('reseller.vouchers.index') }}" class="btn btn-success py-2.5 rounded-3 text-start"><i class="bi bi-ticket-perforated-fill me-2 fs-5"></i>{{ __('Generate Vouchers') }}</a>
                        <a href="{{ route('reseller.wallet.index') }}" class="btn btn-dark py-2.5 rounded-3 text-start"><i class="bi bi-wallet2 me-2 fs-5"></i>{{ __('View Wallet Ledger') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:navigated', function () {
                initChart();
            });

            document.addEventListener('DOMContentLoaded', function() {
                initChart();
            });

            function initChart() {
                if (window.resellerChart) window.resellerChart.destroy();

                const chartEl = document.querySelector("#resellerEarningsChart");
                if (chartEl) {
                    const days = @json(collect($chartData)->pluck('day'));
                    const amounts = @json(collect($chartData)->pluck('amount'));
                    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

                    const options = {
                        series: [{
                            name: '{{ __('Commission Earned (BDT)') }}',
                            data: amounts
                        }],
                        chart: {
                            type: 'area',
                            height: 250,
                            toolbar: { show: false },
                            sparkline: { enabled: false }
                        },
                        theme: {
                            mode: isDark ? 'dark' : 'light'
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.45,
                                opacityTo: 0.05,
                                stops: [0, 100]
                            }
                        },
                        colors: ['#10b981'],
                        xaxis: {
                            categories: days,
                            labels: {
                                style: { fontSize: '11px', colors: '#94a3b8' }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (val) { return '৳' + val.toFixed(0); },
                                style: { colors: '#94a3b8' }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) { return '৳' + val.toFixed(2); }
                            }
                        }
                    };

                    window.resellerChart = new ApexCharts(chartEl, options);
                    window.resellerChart.render();

                    // Live theme update listener for Reseller Chart
                    if (window.resellerThemeObserver) window.resellerThemeObserver.disconnect();
                    window.resellerThemeObserver = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.attributeName === 'data-bs-theme') {
                                const activeTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
                                if (window.resellerChart) {
                                    window.resellerChart.updateOptions({
                                        theme: { mode: activeTheme }
                                    });
                                }
                            }
                        });
                    });
                    window.resellerThemeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
                }
            }
        </script>
    @endpush

    @push('styles')
        <style>
            [data-bs-theme="dark"] .card {
                border-color: rgba(255, 255, 255, 0.08) !important;
            }
            [data-bs-theme="dark"] .text-dark {
                color: #f1f5f9 !important;
            }
            [data-bs-theme="dark"] .table-light {
                --bs-table-bg: #1e293b;
                --bs-table-color: #f1f5f9;
            }
            [data-bs-theme="dark"] .list-group-item {
                background-color: transparent !important;
                border-color: rgba(255, 255, 255, 0.08) !important;
            }
            [data-bs-theme="dark"] .list-group-item .text-dark {
                color: #f1f5f9 !important;
            }
        </style>
    @endpush
</x-app-layout>
