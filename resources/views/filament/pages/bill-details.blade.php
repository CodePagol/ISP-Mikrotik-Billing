<x-filament-panels::page>
    <div class="cp-space-y-6">
        @if(!$customer)
            <div class="cp-p-6 cp-bg-amber-500/10 cp-border cp-border-amber-500/20 cp-text-amber-600 dark:cp-text-amber-400 cp-rounded-3xl cp-text-center">
                <svg style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;" class="cp-text-amber-500 cp-mx-auto cp-mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="cp-text-lg cp-font-bold cp-mb-1">Customer Profile Not Found</h3>
                <p class="cp-text-sm">Your PPPoE account is active, but it is not linked to a customer profile. Please contact support to complete your profile.</p>
            </div>
        @elseif(!$billing)
            <div class="cp-p-6 cp-bg-amber-500/10 cp-border cp-border-amber-500/20 cp-text-amber-600 dark:cp-text-amber-400 cp-rounded-3xl cp-text-center">
                <svg style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;" class="cp-text-amber-500 cp-mx-auto cp-mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="cp-text-lg cp-font-bold cp-mb-1">Billing Record Not Found</h3>
                <p class="cp-text-sm">We couldn't locate your billing records. Please contact support to set up your billing profile.</p>
            </div>
        @else
            @php
                $dueAmount   = (float)($billing->due_amount ?? 0);
                $totalAmount = (float)($billing->total_amount ?? 0);
                $paidAmount  = (float)($billing->paid_amount ?? 0);
                $statusActive = ($customer->status ?? '') === 'active';
            @endphp

            {{-- Header Banner --}}
            <div class="cp-relative cp-overflow-hidden cp-rounded-3xl cp-bg-gradient-to-br cp-from-emerald-600 cp-to-teal-600 cp-p-6 cp-text-white cp-shadow-2xl cp-shadow-emerald-900/30">
                <div class="cp-absolute cp-right-0 cp-top-0 cp-opacity-10 cp-pointer-events-none">
                    <svg viewBox="0 0 200 200" width="200" height="200"><circle cx="170" cy="30" r="100" fill="white"/></svg>
                </div>
                <div class="cp-relative">
                    <p class="cp-text-emerald-200 cp-text-sm cp-font-medium cp-mb-1">Account: {{ $customer->customer_unique_id ?? 'N/A' }}</p>
                    <h2 class="cp-text-2xl cp-font-black">Billing Details</h2>
                    <p class="cp-text-emerald-100 cp-text-sm cp-mt-1">Current billing cycle breakdown for <span class="cp-font-bold">{{ $customer->customer_name ?? $pppUser->username }}</span></p>
                </div>
            </div>

            {{-- 3-Column Summary Cards --}}
            <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-3 cp-gap-4">

                <div class="cp-rounded-3xl cp-p-5 cp-text-white cp-shadow-lg cp-flex cp-flex-col cp-gap-1
                    {{ $dueAmount > 0 ? 'cp-bg-gradient-to-br cp-from-rose-500 cp-to-red-600 cp-shadow-rose-500/20' : 'cp-bg-gradient-to-br cp-from-slate-500 cp-to-slate-600' }}">
                    <span class="cp-text-white/75 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider">Outstanding Due</span>
                    <span class="cp-text-3xl cp-font-black">৳{{ number_format($dueAmount, 2) }}</span>
                    @if($dueAmount > 0)
                        <span class="cp-text-white/70 cp-text-xs">Please pay to avoid disconnection</span>
                    @else
                        <span class="cp-text-white/70 cp-text-xs">No outstanding dues ✓</span>
                    @endif
                </div>

                <div class="cp-rounded-3xl cp-p-5 cp-text-white cp-shadow-lg cp-shadow-indigo-500/20 cp-flex cp-flex-col cp-gap-1 cp-bg-gradient-to-br cp-from-indigo-500 cp-to-indigo-600">
                    <span class="cp-text-white/75 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider">Monthly Rent</span>
                    <span class="cp-text-3xl cp-font-black">৳{{ number_format($billing->monthly_rent ?? 0, 2) }}</span>
                    <span class="cp-text-white/70 cp-text-xs">{{ $customer->pppUser?->profile ?? 'N/A' }}</span>
                </div>

                <div class="cp-rounded-3xl cp-p-5 cp-text-white cp-shadow-lg cp-shadow-amber-500/20 cp-flex cp-flex-col cp-gap-1 cp-bg-gradient-to-br cp-from-amber-500 cp-to-orange-500">
                    <span class="cp-text-white/75 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider">Account Expiry</span>
                    <span class="cp-text-xl cp-font-black">
                        {{ $billing->auto_disable_date ? \Carbon\Carbon::parse($billing->auto_disable_date)->format('d M, Y') : 'N/A' }}
                    </span>
                    <span class="cp-text-white/70 cp-text-xs">
                        {{ $billing->auto_disable_date
                            ? 'Month: '.\Carbon\Carbon::parse($billing->auto_disable_date)->format('F Y')
                            : '' }}
                    </span>
                </div>

            </div>

            {{-- Main Grid --}}
            <div class="cp-grid cp-grid-cols-1 lg:cp-grid-cols-2 cp-gap-6">

                {{-- Detailed Billing Table --}}
                <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-overflow-hidden">
                    <div class="cp-px-6 cp-pt-6 cp-pb-4">
                        <h3 class="cp-text-base cp-font-bold cp-text-gray-900 dark:cp-text-white cp-flex cp-items-center cp-gap-2">
                            <span class="cp-p-1.5 cp-bg-indigo-500/10 cp-rounded-lg cp-text-indigo-500">
                                <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            Charge Breakdown
                        </h3>
                    </div>
                    <div class="cp-divide-y cp-divide-gray-100 dark:cp-divide-white/5">
                        @php
                            $rows = [
                                ['label' => 'Monthly Rent',       'value' => $billing->monthly_rent ?? 0,      'type' => 'charge'],
                                ['label' => 'Additional Charge',  'value' => $billing->additional_charge ?? 0, 'type' => 'charge'],
                                ['label' => 'VAT',                'value' => $billing->vat ?? 0,              'type' => 'charge'],
                                ['label' => 'Previous Due',       'value' => $billing->previous_due ?? 0,     'type' => 'due'],
                                ['label' => 'Discount',           'value' => $billing->discount ?? 0,         'type' => 'credit'],
                                ['label' => 'Advance',            'value' => $billing->advance ?? 0,          'type' => 'credit'],
                                ['label' => 'Paid Amount',        'value' => $billing->paid_amount ?? 0,      'type' => 'credit'],
                            ];
                        @endphp
                        @foreach($rows as $row)
                            <div class="cp-flex cp-items-center cp-justify-between cp-px-6 cp-py-3">
                                <span class="cp-text-sm cp-text-gray-600 dark:cp-text-slate-400">{{ $row['label'] }}</span>
                                <span class="cp-text-sm cp-font-bold
                                    {{ $row['type'] === 'credit' ? 'cp-text-emerald-500' : ($row['type'] === 'due' ? 'cp-text-rose-500' : 'cp-text-gray-900 dark:cp-text-white') }}">
                                    {{ $row['type'] === 'credit' ? '-' : '+' }} ৳{{ number_format($row['value'], 2) }}
                                </span>
                            </div>
                        @endforeach
                        <div class="cp-flex cp-items-center cp-justify-between cp-px-6 cp-py-4 cp-bg-rose-50 dark:cp-bg-rose-500/5">
                            <span class="cp-text-sm cp-font-bold cp-text-rose-600 dark:cp-text-rose-400">Total Outstanding Due</span>
                            <span class="cp-text-xl cp-font-black cp-text-rose-600 dark:cp-text-rose-400">৳{{ number_format($dueAmount, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Account & Payment Info --}}
                <div class="cp-space-y-5">

                    {{-- Account Info --}}
                    <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-p-6">
                        <h3 class="cp-text-base cp-font-bold cp-text-gray-900 dark:cp-text-white cp-flex cp-items-center cp-gap-2 cp-mb-4">
                            <span class="cp-p-1.5 cp-bg-purple-500/10 cp-rounded-lg cp-text-purple-500">
                                <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            Account Info
                        </h3>
                        <div class="cp-space-y-3">
                            @php
                                $acctRows = [
                                    ['label' => 'Account No',     'value' => $customer->customer_unique_id ?? 'N/A'],
                                    ['label' => 'PPPoE Username', 'value' => $pppUser->username ?? 'N/A'],
                                    ['label' => 'Package',        'value' => $customer->pppUser?->profile ?? ($customer->package?->package ?? 'N/A')],
                                    ['label' => 'Billing Type',   'value' => ucfirst($billing->billing_type ?? 'Prepaid')],
                                    ['label' => 'Auto Disable',   'value' => $billing->auto_disable ? 'Yes' : 'No'],
                                    ['label' => 'Disable Month',  'value' => $billing->auto_disable_month ?? 'N/A'],
                                ];
                            @endphp
                            @foreach($acctRows as $row)
                                <div class="cp-flex cp-items-center cp-justify-between">
                                    <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-500">{{ $row['label'] }}</span>
                                    <span class="cp-text-xs cp-font-bold cp-text-gray-900 dark:cp-text-white">{{ $row['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Last Payment --}}
                    @if($lastPayment)
                        <div class="cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-p-6">
                            <h3 class="cp-text-base cp-font-bold cp-text-gray-900 dark:cp-text-white cp-flex cp-items-center cp-gap-2 cp-mb-4">
                                <span class="cp-p-1.5 cp-bg-emerald-500/10 cp-rounded-lg cp-text-emerald-500">
                                    <svg class="cp-w-4 cp-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                Last Payment
                            </h3>
                            <div class="cp-space-y-3">
                                @php
                                    $lastRows = [
                                        ['label' => 'Amount',       'value' => '৳'.number_format($lastPayment->collection_amount, 2)],
                                        ['label' => 'Date',         'value' => $lastPayment->collection_date ? \Carbon\Carbon::parse($lastPayment->collection_date)->format('d M, Y') : '—'],
                                        ['label' => 'Method',       'value' => ucfirst($lastPayment->payment_method ?? '—')],
                                        ['label' => 'Transaction',  'value' => $lastPayment->transaction_id ?? '—'],
                                        ['label' => 'Invoice No',   'value' => $lastPayment->invoice_no ?? '—'],
                                    ];
                                @endphp
                                @foreach($lastRows as $row)
                                    <div class="cp-flex cp-items-center cp-justify-between">
                                        <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-500">{{ $row['label'] }}</span>
                                        <span class="cp-text-xs cp-font-bold cp-text-gray-900 dark:cp-text-white">{{ $row['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Pay Now CTA --}}
                    @if($dueAmount > 0)
                        <a href="/pay-bill"
                            class="cp-flex cp-items-center cp-justify-center cp-gap-2 cp-w-full cp-py-4 cp-px-6 cp-bg-gradient-to-r cp-from-indigo-600 cp-to-purple-600 hover:cp-from-indigo-500 hover:cp-to-purple-500 cp-text-white cp-font-bold cp-rounded-2xl cp-shadow-xl cp-shadow-indigo-950/20 cp-transition-all cp-duration-200 hover:cp--translate-y-0.5 active:cp-translate-y-0">
                            <svg class="cp-w-5 cp-h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Pay ৳{{ number_format($dueAmount, 2) }} Now
                        </a>
                    @endif

                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
