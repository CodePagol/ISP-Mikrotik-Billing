<x-filament-panels::page>
    <style>
        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        .btn-shimmer {
            position: relative;
            overflow: hidden;
        }
        .btn-shimmer::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.3) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-25deg);
        }
        .btn-shimmer:hover::after {
            animation: shine 1.5s infinite;
        }
    </style>

    <div class="cp-space-y-6" wire:init="checkLiveStatus">
        
        <!-- Status Notifications -->
        @if(session()->has('error'))
            <div class="cp-p-4 cp-bg-rose-500/10 cp-border cp-border-rose-500/20 cp-text-rose-600 dark:cp-text-rose-400 cp-rounded-2xl cp-text-sm cp-flex cp-items-center cp-gap-3">
                <span class="cp-p-2 cp-bg-rose-500/20 cp-rounded-xl cp-text-rose-500 dark:cp-text-rose-400 cp-flex cp-items-center cp-justify-center">
                    <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
                <span class="cp-font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        @if(session()->has('success'))
            <div class="cp-p-4 cp-bg-emerald-500/10 cp-border cp-border-emerald-500/20 cp-text-emerald-600 dark:cp-text-emerald-400 cp-rounded-2xl cp-text-sm cp-flex cp-items-center cp-gap-3">
                <span class="cp-p-2 cp-bg-emerald-500/20 cp-rounded-xl cp-text-emerald-500 dark:cp-text-emerald-400 cp-flex cp-items-center cp-justify-center">
                    <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
                <span class="cp-font-semibold">{{ session('success') }}</span>
            </div>
        @endif

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
                $dueAmount = $billing->due_amount ?? 0;
                $statusActive = ($customer->status ?? '') === 'active';
            @endphp

            <!-- Top Stat Cards (Inspire Portal Style) -->
            <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-3 cp-gap-4">
                
                <!-- Subscription Card -->
                <div class="cp-bg-gradient-to-br cp-from-indigo-500 cp-to-indigo-600 dark:cp-from-indigo-600 dark:cp-to-indigo-700 cp-text-white cp-shadow-lg cp-shadow-indigo-500/10 cp-rounded-3xl cp-p-5 cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-white/80 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider cp-block">Subscription</span>
                        @if($statusActive)
                            <span class="cp-text-xl cp-font-bold cp-mt-1.5 cp-block">Active Connection</span>
                        @else
                            <span class="cp-text-xl cp-font-bold cp-mt-1.5 cp-block cp-animate-pulse">Connection Blocked</span>
                        @endif
                    </div>
                    <div class="cp-p-3 cp-bg-white/10 cp-rounded-2xl">
                        <svg style="width: 28px; height: 28px; min-width: 28px; min-height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Current Due Card -->
                <div class="{{ $dueAmount > 0 ? 'cp-bg-gradient-to-br cp-from-rose-500 cp-to-red-600 dark:cp-from-rose-600 dark:cp-to-red-700 cp-shadow-rose-500/10' : 'cp-bg-gradient-to-br cp-from-slate-500 cp-to-slate-600 dark:cp-from-slate-600 dark:cp-to-slate-700 cp-shadow-slate-500/10' }} cp-text-white cp-shadow-lg cp-rounded-3xl cp-p-5 cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-white/80 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider cp-block">Current Due Balance</span>
                        <span class="cp-text-2xl cp-font-black cp-mt-1.5 cp-block">৳ {{ number_format($dueAmount, 2) }}</span>
                    </div>
                    <div class="cp-p-3 cp-bg-white/10 cp-rounded-2xl">
                        <svg style="width: 28px; height: 28px; min-width: 28px; min-height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Monthly rent card -->
                <div class="cp-bg-gradient-to-br cp-from-emerald-500 cp-to-teal-600 dark:cp-from-emerald-600 dark:cp-to-teal-700 cp-text-white cp-shadow-lg cp-shadow-emerald-500/10 cp-rounded-3xl cp-p-5 cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-white/80 cp-text-xs cp-font-semibold cp-uppercase cp-tracking-wider cp-block">Monthly Plan Cost</span>
                        <span class="cp-text-2xl cp-font-black cp-mt-1.5 cp-block">৳ {{ number_format($billing->monthly_rent ?? 0, 2) }}</span>
                    </div>
                    <div class="cp-p-3 cp-bg-white/10 cp-rounded-2xl">
                        <svg style="width: 28px; height: 28px; min-width: 28px; min-height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>

            </div>

            <!-- Split Grid (My Info & Secure Checkout) -->
            <div class="cp-grid cp-grid-cols-1 lg:cp-grid-cols-12 cp-gap-6 cp-items-start">
                
                <!-- Left Info Panel (7 Columns) -->
                <div class="lg:cp-col-span-7 cp-p-6 cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl">
                    <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-tracking-wide cp-flex cp-items-center cp-gap-2.5">
                        <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500 dark:cp-text-indigo-400">
                            <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        My Info
                    </h3>
                    <hr class="cp-my-4 cp-border-gray-100 dark:cp-border-white/5">

                    <div class="cp-grid cp-grid-cols-2 md:cp-grid-cols-3 cp-gap-6 cp-text-left">
                        
                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">A/C No</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->customer_unique_id ?? 'N/A' }}</span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Account Status</span>
                            <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-mt-1.5 cp-px-2.5 cp-py-1 cp-rounded-full cp-text-xs cp-font-bold {{ ($customer->status ?? '') === 'active' ? 'cp-bg-emerald-500/10 cp-text-emerald-500' : 'cp-bg-rose-500/10 cp-text-rose-500' }}">
                                <span class="cp-h-1.5 cp-w-1.5 cp-rounded-full {{ ($customer->status ?? '') === 'active' ? 'cp-bg-emerald-500' : 'cp-bg-rose-500' }}"></span>
                                {{ ucfirst($customer->status ?? 'unknown') }}
                            </span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Connection Status</span>
                            @if($connectionStatus === 'checking...')
                                <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-mt-1.5 cp-px-2.5 cp-py-1 cp-rounded-full cp-text-xs cp-font-bold cp-bg-gray-500/10 cp-text-gray-500 cp-animate-pulse">
                                    <span class="cp-h-1.5 cp-w-1.5 cp-rounded-full cp-bg-gray-400 cp-animate-bounce"></span>
                                    Checking...
                                </span>
                            @elseif($connectionStatus === 'online')
                                <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-mt-1.5 cp-px-2.5 cp-py-1 cp-rounded-full cp-text-xs cp-font-bold cp-bg-emerald-500/10 cp-text-emerald-500">
                                    <span class="cp-h-1.5 cp-w-1.5 cp-rounded-full cp-bg-emerald-500 cp-animate-pulse"></span>
                                    Online
                                </span>
                            @elseif($connectionStatus === 'offline')
                                <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-mt-1.5 cp-px-2.5 cp-py-1 cp-rounded-full cp-text-xs cp-font-bold cp-bg-rose-500/10 cp-text-rose-500">
                                    <span class="cp-h-1.5 cp-w-1.5 cp-rounded-full cp-bg-rose-500"></span>
                                    Offline
                                </span>
                            @else
                                <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-mt-1.5 cp-px-2.5 cp-py-1 cp-rounded-full cp-text-xs cp-font-bold cp-bg-amber-500/10 cp-text-amber-500">
                                    <span class="cp-h-1.5 cp-w-1.5 cp-rounded-full cp-bg-amber-500"></span>
                                    Unknown
                                </span>
                            @endif
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Username</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->pppUser?->username ?? 'N/A' }}</span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Full Name</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->customer_name ?? 'N/A' }}</span>
                        </div>

                        @if($customer->parents_name)
                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Father Name</span>
                                <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->parents_name }}</span>
                            </div>
                        @endif

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Register Mobile</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->mobile ?? 'N/A' }}</span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Register Email Id</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->email ?? '.' }}</span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Billing Type</span>
                            <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-mt-1.5 cp-px-2.5 cp-py-1 cp-rounded-full cp-text-xs cp-font-bold cp-bg-blue-500/10 cp-text-blue-500">
                                Prepaid
                            </span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Current Package</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->pppUser?->profile ?? 'N/A' }}</span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Sub Plan</span>
                            <span class="cp-font-bold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->pppUser?->profile ?? 'N/A' }}</span>
                        </div>

                        <div>
                            <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-medium">Account Expiration</span>
                            <span class="cp-font-bold cp-text-indigo-500 dark:cp-text-indigo-400 cp-text-sm cp-mt-1 cp-block">
                                {{ $billing->auto_disable_date ? \Carbon\Carbon::parse($billing->auto_disable_date)->format('d F, Y') : 'N/A' }}
                            </span>
                        </div>

                    </div>
                </div>

                <!-- Right Checkout Panel (5 Columns) -->
                <div class="lg:cp-col-span-5 cp-p-6 cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl">
                    <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-tracking-wide cp-flex cp-items-center cp-gap-2.5">
                        <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500 dark:cp-text-indigo-400">
                            <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </span>
                        Secure Checkout
                    </h3>
                    <hr class="cp-my-4 cp-border-gray-100 dark:cp-border-white/5">

                    @php
                        $bkashActive = siteUrlSettings('payment_bkash_enabled');
                        $nagadActive = siteUrlSettings('payment_nagad_enabled');
                        $sslActive = siteUrlSettings('payment_sslcommerz_enabled');
                    @endphp

                    @if(!$bkashActive && !$nagadActive && !$sslActive)
                        <div class="cp-p-5 cp-bg-amber-500/10 cp-border cp-border-amber-500/20 cp-text-amber-600 dark:cp-text-amber-400 cp-rounded-2xl cp-text-sm cp-text-center">
                            <svg style="width: 36px; height: 36px; min-width: 36px; min-height: 36px;" class="cp-text-amber-500 cp-mx-auto cp-mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span class="cp-font-bold cp-block cp-mb-1">Gateways Offline</span>
                            Online payment gateways are currently offline. Please contact support.
                        </div>
                    @else
                        <form wire:submit.prevent="pay" class="cp-space-y-6">
                            
                            <!-- Amount Input -->
                            <div class="cp-text-left">
                                <label for="payment_amount" class="cp-text-[11px] cp-font-bold cp-tracking-wider cp-text-gray-500 dark:cp-text-slate-400 cp-block cp-mb-2.5 cp-uppercase">Amount to Pay (BDT)</label>
                                <div class="cp-relative cp-rounded-2xl cp-overflow-hidden cp-bg-gray-50 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 focus-within:cp-border-indigo-500 focus-within:cp-ring-2 focus-within:cp-ring-indigo-500/15 cp-transition-all cp-duration-300">
                                    <span class="cp-absolute cp-inset-y-0 cp-left-0 cp-pl-4 cp-flex cp-items-center cp-text-gray-400 dark:cp-text-slate-500 cp-font-bold cp-text-lg cp-select-none">৳</span>
                                    <input type="number" step="0.01" min="1" id="payment_amount" wire:model="amount" 
                                        class="cp-w-full cp-bg-transparent cp-border-0 cp-py-4 cp-pl-9 cp-pr-4 cp-text-gray-900 dark:cp-text-white focus:cp-outline-none focus:cp-ring-0 cp-text-lg cp-font-bold"
                                        placeholder="Enter amount..." required>
                                </div>
                                @error('amount') <span class="cp-text-xs cp-text-rose-500 cp-mt-2 cp-block cp-font-medium">{{ $message }}</span> @enderror

                                <!-- Presets -->
                                <div class="cp-flex cp-flex-wrap cp-gap-2 cp-mt-3">
                                    @if($dueAmount > 0)
                                        <button type="button" wire:click="$set('amount', {{ $dueAmount }})"
                                            class="cp-text-xs cp-font-semibold cp-px-3 cp-py-1.5 cp-rounded-xl cp-border cp-border-rose-500/20 cp-bg-rose-500/5 cp-text-rose-500 hover:cp-bg-rose-500/10 hover:cp-border-rose-500/40 cp-transition-all cp-duration-200">
                                            Pay Full Due (৳{{ number_format($dueAmount, 0) }})
                                        </button>
                                    @endif
                                    @if(($billing->monthly_rent ?? 0) > 0)
                                        <button type="button" wire:click="$set('amount', {{ $billing->monthly_rent }})"
                                            class="cp-text-xs cp-font-semibold cp-px-3 cp-py-1.5 cp-rounded-xl cp-border cp-border-gray-200 dark:cp-border-white/10 cp-bg-gray-50 dark:cp-bg-white/5 cp-text-gray-600 dark:cp-text-slate-300 hover:cp-bg-gray-100 dark:hover:cp-bg-white/10 hover:cp-border-gray-300 dark:hover:cp-border-white/20 cp-transition-all cp-duration-200">
                                            Pay Plan Rent (৳{{ number_format($billing->monthly_rent, 0) }})
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Gateway Selection -->
                            <div class="cp-text-left">
                                <label class="cp-text-[11px] cp-font-bold cp-tracking-wider cp-text-gray-500 dark:cp-text-slate-400 cp-block cp-mb-3 cp-uppercase">Select Payment Gateway</label>
                                <div class="cp-space-y-3">
                                    
                                    @if($bkashActive)
                                        <label class="cp-relative cp-flex cp-items-center cp-justify-between cp-p-4 cp-rounded-2xl cp-border cp-transition-all cp-duration-300 cp-cursor-pointer cp-group {{ $paymentMethod === 'bkash' ? 'cp-border-pink-500 cp-bg-pink-500/5 cp-shadow-[0_0_20px_rgba(236,72,153,0.15)] cp-ring-1 cp-ring-pink-500/50' : 'cp-border-gray-200 dark:cp-border-white/5 cp-bg-gray-50/50 dark:cp-bg-slate-950/20 hover:cp-border-gray-300 dark:hover:cp-border-white/15' }}">
                                            <div class="cp-flex cp-items-center cp-gap-3">
                                                <input type="radio" name="gateway" value="bkash" wire:model.live="paymentMethod" 
                                                    class="cp-text-pink-600 focus:cp-ring-pink-500/20 cp-bg-white dark:cp-bg-slate-950 cp-border-gray-300 dark:cp-border-white/10 cp-h-4.5 cp-w-4.5">
                                                <span class="cp-text-sm cp-font-bold cp-text-gray-900 dark:cp-text-white group-hover:cp-text-pink-500 cp-transition-colors">bKash Checkout</span>
                                            </div>
                                            <div class="cp-px-2.5 cp-py-1 cp-bg-pink-600/10 cp-text-pink-500 cp-text-[10px] cp-tracking-wider cp-font-extrabold cp-rounded-lg cp-uppercase">bKash</div>
                                        </label>
                                    @endif

                                    @if($nagadActive)
                                        <label class="cp-relative cp-flex cp-items-center cp-justify-between cp-p-4 cp-rounded-2xl cp-border cp-transition-all cp-duration-300 cp-cursor-pointer cp-group {{ $paymentMethod === 'nagad' ? 'cp-border-orange-500 cp-bg-orange-500/5 cp-shadow-[0_0_20px_rgba(249,115,22,0.15)] cp-ring-1 cp-ring-orange-500/50' : 'cp-border-gray-200 dark:cp-border-white/5 cp-bg-gray-50/50 dark:cp-bg-slate-950/20 hover:cp-border-gray-300 dark:hover:cp-border-white/15' }}">
                                            <div class="cp-flex cp-items-center cp-gap-3">
                                                <input type="radio" name="gateway" value="nagad" wire:model.live="paymentMethod" 
                                                    class="cp-text-orange-600 focus:cp-ring-orange-500/20 cp-bg-white dark:cp-bg-slate-950 cp-border-gray-300 dark:cp-border-white/10 cp-h-4.5 cp-w-4.5">
                                                <span class="cp-text-sm cp-font-bold cp-text-gray-900 dark:cp-text-white group-hover:cp-text-orange-500 cp-transition-colors">Nagad Pay</span>
                                            </div>
                                            <div class="cp-px-2.5 cp-py-1 cp-bg-orange-600/10 cp-text-orange-500 cp-text-[10px] cp-tracking-wider cp-font-extrabold cp-rounded-lg cp-uppercase">Nagad</div>
                                        </label>
                                    @endif

                                    @if($sslActive)
                                        <label class="cp-relative cp-flex cp-items-center cp-justify-between cp-p-4 cp-rounded-2xl cp-border cp-transition-all cp-duration-300 cp-cursor-pointer cp-group {{ $paymentMethod === 'sslcommerz' ? 'cp-border-blue-500 cp-bg-blue-500/5 cp-shadow-[0_0_20px_rgba(59,130,246,0.15)] cp-ring-1 cp-ring-blue-500/50' : 'cp-border-gray-200 dark:cp-border-white/5 cp-bg-gray-50/50 dark:cp-bg-slate-950/20 hover:cp-border-gray-300 dark:hover:cp-border-white/15' }}">
                                            <div class="cp-flex cp-items-center cp-gap-3">
                                                <input type="radio" name="gateway" value="sslcommerz" wire:model.live="paymentMethod" 
                                                    class="cp-text-blue-600 focus:cp-ring-blue-500/20 cp-bg-white dark:cp-bg-slate-950 cp-border-gray-300 dark:cp-border-white/10 cp-h-4.5 cp-w-4.5">
                                                <span class="cp-text-sm cp-font-bold cp-text-gray-900 dark:cp-text-white group-hover:cp-text-blue-500 cp-transition-colors">SSLCommerz</span>
                                            </div>
                                            <div class="cp-px-2.5 cp-py-1 cp-bg-blue-600/10 cp-text-blue-500 cp-text-[10px] cp-tracking-wider cp-font-extrabold cp-rounded-lg cp-uppercase">SSL</div>
                                        </label>
                                    @endif
                                    
                                    @error('paymentMethod') <span class="cp-text-xs cp-text-rose-500 cp-mt-2 cp-block cp-font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Pay Button -->
                            <button type="submit" wire:loading.attr="disabled"
                                class="btn-shimmer cp-relative cp-w-full cp-bg-gradient-to-r cp-from-indigo-600 cp-to-purple-600 hover:cp-from-indigo-500 hover:cp-to-purple-500 cp-text-white cp-font-bold cp-py-4 cp-px-6 cp-rounded-2xl cp-shadow-xl cp-shadow-indigo-950/25 cp-transition-all cp-duration-200 hover:cp--translate-y-0.5 active:cp-translate-y-0 cp-flex cp-items-center cp-justify-center cp-gap-2 cp-overflow-hidden cp-group">
                                
                                <span wire:loading.remove class="cp-flex cp-items-center cp-gap-2.5">
                                    Proceed to Checkout
                                    <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="group-hover:cp-translate-x-1 cp-transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </span>
                                <span wire:loading class="cp-inline-block cp-w-5 cp-h-5 cp-border-2 cp-border-white/30 cp-border-t-white cp-rounded-full cp-animate-spin"></span>
                                <span wire:loading>Redirecting to secure gateway...</span>
                            </button>

                        </form>
                    @endif
                </div>

            </div>
        @endif

    </div>
</x-filament-panels::page>
