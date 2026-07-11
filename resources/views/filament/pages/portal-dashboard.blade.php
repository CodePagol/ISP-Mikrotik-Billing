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
        .portal-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .portal-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
        }
    </style>

    <div class="cp-space-y-6" wire:init="checkLiveStatus">
        @if(!$customer)
            <div class="cp-p-6 cp-bg-amber-500/10 cp-border cp-border-amber-500/20 cp-text-amber-600 dark:cp-text-amber-400 cp-rounded-3xl cp-text-center">
                <svg style="width: 48px; height: 48px; min-width: 48px; min-height: 48px;" class="cp-text-amber-500 cp-mx-auto cp-mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="cp-text-lg cp-font-bold cp-mb-1">Customer Profile Not Found</h3>
                <p class="cp-text-sm">Your PPPoE account is active, but it is not linked to a customer profile. Please contact support to complete your profile.</p>
            </div>
        @else
            {{-- Review system banner alerts --}}
            @if(session()->has('review_success'))
                <div class="cp-p-4 cp-bg-emerald-500/10 cp-border cp-border-emerald-500/20 cp-text-emerald-600 dark:cp-text-emerald-400 cp-rounded-3xl cp-text-sm cp-mb-4 cp-flex cp-items-center cp-gap-2">
                    <svg class="cp-w-5 cp-h-5 cp-text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('review_success') }}
                </div>
            @endif
            @if(session()->has('review_error'))
                <div class="cp-p-4 cp-bg-rose-500/10 cp-border cp-border-rose-500/20 cp-text-rose-600 dark:cp-text-rose-400 cp-rounded-3xl cp-text-sm cp-mb-4 cp-flex cp-items-center cp-gap-2">
                    <svg class="cp-w-5 cp-h-5 cp-text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ session('review_error') }}
                </div>
            @endif

            {{-- Persistent Review Prompt on Dashboard --}}
            @if(!$existingReview)
                <div class="cp-bg-indigo-50 dark:cp-bg-indigo-950/20 cp-border cp-border-indigo-100 dark:cp-border-indigo-900/30 cp-rounded-3xl cp-p-5 cp-mb-6 cp-flex cp-flex-col sm:cp-flex-row cp-items-start sm:cp-items-center cp-justify-between cp-gap-4">
                    <div class="cp-flex cp-items-center cp-gap-3">
                        <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500">
                            <svg class="cp-w-6 cp-h-6 cp-text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </span>
                        <div>
                            <h4 class="cp-text-sm cp-font-bold cp-text-gray-900 dark:cp-text-white">Help Us Improve!</h4>
                            <p class="cp-text-xs cp-text-gray-500 dark:cp-text-slate-400">Share your experience and give us a review to improve our service.</p>
                        </div>
                    </div>
                    <button wire:click="triggerReviewModal" class="cp-px-4 cp-py-2 cp-bg-indigo-600 hover:cp-bg-indigo-500 cp-text-white cp-text-xs cp-font-bold cp-rounded-2xl cp-shadow-md cp-transition-colors">
                        Submit Review
                    </button>
                </div>
            @elseif($existingReview && $existingReview->edit_count < 2)
                <div class="cp-bg-amber-50 dark:cp-bg-amber-950/20 cp-border cp-border-amber-100 dark:cp-border-amber-900/30 cp-rounded-3xl cp-p-5 cp-mb-6 cp-flex cp-flex-col sm:cp-flex-row cp-items-start sm:cp-items-center cp-justify-between cp-gap-4">
                    <div class="cp-flex cp-items-center cp-gap-3">
                        <span class="cp-p-2 cp-bg-amber-500/10 cp-rounded-xl cp-text-amber-500">
                            <svg class="cp-w-6 cp-h-6 cp-text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </span>
                        <div>
                            <h4 class="cp-text-sm cp-font-bold cp-text-gray-900 dark:cp-text-white">Your Feedback is Valuable</h4>
                            <p class="cp-text-xs cp-text-gray-500 dark:cp-text-slate-400">You have already submitted a review. You can edit it up to 2 times (Remaining edits: {{ 2 - $existingReview->edit_count }}).</p>
                        </div>
                    </div>
                    <button wire:click="triggerReviewModal" class="cp-px-4 cp-py-2 cp-bg-amber-600 hover:cp-bg-amber-500 cp-text-white cp-text-xs cp-font-bold cp-rounded-2xl cp-shadow-md cp-transition-colors">
                        Edit Review
                    </button>
                </div>
            @endif

            <!-- Greeting & Quick Status Banner -->
            <div class="cp-bg-gradient-to-r cp-from-indigo-600 cp-via-purple-600 cp-to-pink-600 dark:cp-from-indigo-700 dark:cp-via-purple-700 dark:cp-to-pink-700 cp-text-white cp-shadow-xl cp-rounded-3xl cp-p-6 cp-flex cp-flex-col md:cp-flex-row cp-items-start md:cp-items-center cp-justify-between cp-gap-4">
                <div>
                    <h2 class="cp-text-2xl cp-font-black cp-tracking-tight">Welcome, {{ $customer->customer_name }}!</h2>
                    <p class="cp-text-white/80 cp-text-sm cp-mt-1">Manage your connection, view detailed billing records, and settle invoices online securely.</p>
                </div>
                <div class="cp-flex cp-wrap cp-items-center cp-gap-3">
                    <span class="cp-px-4 cp-py-2 cp-bg-white/10 cp-rounded-2xl cp-text-sm cp-font-bold cp-backdrop-blur-md cp-border cp-border-white/10">
                        A/C: {{ $customer->customer_unique_id }}
                    </span>

                    <!-- Live Connection Badge -->
                    @if($connectionStatus === 'checking...')
                        <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-px-4 cp-py-2 cp-bg-white/10 cp-rounded-2xl cp-text-sm cp-font-bold cp-text-white cp-animate-pulse cp-backdrop-blur-md cp-border cp-border-white/10">
                            <span class="cp-h-2 cp-w-2 cp-rounded-full cp-bg-gray-300 cp-animate-ping"></span>
                            Line: Checking...
                        </span>
                    @elseif($connectionStatus === 'online')
                        <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-px-4 cp-py-2 cp-bg-emerald-500/20 cp-border cp-border-emerald-400/30 cp-rounded-2xl cp-text-sm cp-font-bold cp-text-white cp-backdrop-blur-md">
                            <span class="cp-h-2 cp-w-2 cp-rounded-full cp-bg-emerald-400 cp-animate-pulse"></span>
                            Line: Online
                        </span>
                    @elseif($connectionStatus === 'offline')
                        <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-px-4 cp-py-2 cp-bg-rose-500/20 cp-border cp-border-rose-400/30 cp-rounded-2xl cp-text-sm cp-font-bold cp-text-white cp-backdrop-blur-md">
                            <span class="cp-h-2 cp-w-2 cp-rounded-full cp-bg-rose-400"></span>
                            Line: Offline
                        </span>
                    @else
                        <span class="cp-inline-flex cp-items-center cp-gap-1.5 cp-px-4 cp-py-2 cp-bg-amber-500/20 cp-border cp-border-amber-400/30 cp-rounded-2xl cp-text-sm cp-font-bold cp-text-white cp-backdrop-blur-md">
                            <span class="cp-h-2 cp-w-2 cp-rounded-full cp-bg-amber-400"></span>
                            Line: Unknown
                        </span>
                    @endif

                    @if($dueAmount > 0)
                        <a href="{{ route('filament.portal.pages.pay-bill') }}"
                           title="Pay Bill — ৳{{ number_format($dueAmount, 2) }} Due"
                           class="cp-relative cp-inline-flex cp-items-center cp-justify-center cp-w-11 cp-h-11 cp-bg-white cp-text-rose-600 cp-rounded-2xl cp-shadow-lg hover:cp-scale-110 cp-transition-all cp-duration-200 hover:cp-shadow-xl">
                            {{-- Credit-card icon --}}
                            <svg style="width: 22px; height: 22px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            {{-- Pulsing red dot --}}
                            <span class="cp-absolute -cp-top-1 -cp-right-1 cp-flex cp-h-3.5 cp-w-3.5">
                                <span class="cp-animate-ping cp-absolute cp-inline-flex cp-h-full cp-w-full cp-rounded-full cp-bg-rose-400 cp-opacity-75"></span>
                                <span class="cp-relative cp-inline-flex cp-rounded-full cp-h-3.5 cp-w-3.5 cp-bg-rose-500"></span>
                            </span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-3 cp-gap-4">
                
                <!-- Package Details -->
                <div class="portal-card cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-p-6 cp-shadow-xl cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-block">Current Package</span>
                        <span class="cp-text-xl cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-mt-1.5 cp-block">
                            {{ $customer->pppUser?->profile ?? 'N/A' }}
                        </span>
                        @if($customer->package?->speed)
                            <span class="cp-inline-flex cp-items-center cp-gap-1 cp-mt-2 cp-px-2.5 cp-py-1 cp-bg-indigo-500/10 cp-text-indigo-600 dark:cp-text-indigo-400 cp-text-xs cp-font-bold cp-rounded-lg">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Speed: {{ $customer->package->speed }}
                            </span>
                        @endif
                    </div>
                    <div class="cp-p-3.5 cp-bg-indigo-500/10 cp-rounded-2xl cp-text-indigo-500 dark:cp-text-indigo-400">
                        <svg style="width: 30px; height: 30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.07 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Monthly Plan Rent -->
                <div class="portal-card cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-p-6 cp-shadow-xl cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-block">Monthly cost</span>
                        <span class="cp-text-xl cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-mt-1.5 cp-block">
                            ৳ {{ number_format($billing->monthly_rent ?? 0, 2) }}
                        </span>
                        <span class="cp-inline-flex cp-items-center cp-gap-1 cp-mt-2 cp-px-2.5 cp-py-1 cp-bg-emerald-500/10 cp-text-emerald-600 dark:cp-text-emerald-400 cp-text-xs cp-font-bold cp-rounded-lg">
                            Billing: {{ ucfirst($billing->billing_type ?? 'Prepaid') }}
                        </span>
                    </div>
                    <div class="cp-p-3.5 cp-bg-emerald-500/10 cp-rounded-2xl cp-text-emerald-500 dark:cp-text-emerald-400">
                        <svg style="width: 30px; height: 30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Current Due -->
                <div class="portal-card cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-p-6 cp-shadow-xl cp-flex cp-items-center cp-justify-between">
                    <div>
                        <span class="cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-bold cp-uppercase cp-tracking-wider cp-block">Current Due</span>
                        <span class="cp-text-xl cp-font-extrabold {{ $dueAmount > 0 ? 'cp-text-rose-500' : 'cp-text-emerald-500' }} cp-mt-1.5 cp-block">
                            ৳ {{ number_format($dueAmount, 2) }}
                        </span>
                        @if($dueAmount > 0)
                            <span class="cp-inline-flex cp-items-center cp-gap-1 cp-mt-2 cp-px-2.5 cp-py-1 cp-bg-rose-500/10 cp-text-rose-600 dark:cp-text-rose-400 cp-text-xs cp-font-bold cp-rounded-lg cp-animate-pulse">
                                Unpaid Balance
                            </span>
                        @else
                            <span class="cp-inline-flex cp-items-center cp-gap-1 cp-mt-2 cp-px-2.5 cp-py-1 cp-bg-emerald-500/10 cp-text-emerald-600 dark:cp-text-emerald-400 cp-text-xs cp-font-bold cp-rounded-lg">
                                No Arrears
                            </span>
                        @endif
                    </div>
                    <div class="cp-p-3.5 {{ $dueAmount > 0 ? 'cp-bg-rose-500/10 cp-text-rose-500 dark:cp-text-rose-400' : 'cp-bg-emerald-500/10 cp-text-emerald-500 dark:cp-text-emerald-400' }} cp-rounded-2xl">
                        <svg style="width: 30px; height: 30px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Main Split Grid -->
            <div class="cp-grid cp-grid-cols-1 lg:cp-grid-cols-12 cp-gap-6 cp-items-start">
                
                <!-- Left Panel (7 Columns) -->
                <div class="lg:cp-col-span-7 cp-space-y-6">
                    
                    <!-- Profile Information -->
                    <div class="portal-card cp-p-6 cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl">
                        <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-tracking-wide cp-flex cp-items-center cp-gap-2.5">
                            <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500 dark:cp-text-indigo-400">
                                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </span>
                            Profile Details
                        </h3>
                        <hr class="cp-my-4 cp-border-gray-100 dark:cp-border-white/5">

                        <div class="cp-grid cp-grid-cols-1 md:cp-grid-cols-2 cp-gap-6 cp-text-left">
                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Full Name</span>
                                <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->customer_name ?? 'N/A' }}</span>
                            </div>
                            
                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">PPPoE Username</span>
                                <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->pppUser?->username ?? 'N/A' }}</span>
                            </div>

                            @if($customer->parents_name)
                                <div>
                                    <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Father's Name</span>
                                    <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->parents_name }}</span>
                                </div>
                            @endif

                            @if($customer->contact_person)
                                <div>
                                    <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Contact Person</span>
                                    <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->contact_person }}</span>
                                </div>
                            @endif

                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Contact Mobile</span>
                                <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->mobile ?? 'N/A' }}</span>
                            </div>

                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Email Address</span>
                                <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->email ?? 'N/A' }}</span>
                            </div>

                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Connection Date</span>
                                <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->connection_date ? \Carbon\Carbon::parse($customer->connection_date)->format('d M, Y') : 'N/A' }}</span>
                            </div>

                            @if($customer->pppUser?->router_name)
                                <div>
                                    <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">MikroTik Router</span>
                                    <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $customer->pppUser->router_name }}</span>
                                </div>
                            @endif

                            <div class="md:cp-col-span-2">
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Billing Address</span>
                                @php
                                    $addrParts = [];
                                    if ($customer->customerAddress) {
                                        foreach ($customer->customerAddress as $addr) {
                                            $parts = array_filter([$addr->input_type_text, $addr->input_type_dropdown, $addr->input_type_textarea]);
                                            $addrParts[] = implode(', ', $parts);
                                        }
                                    }
                                    $addressString = implode(' | ', $addrParts);
                                @endphp
                                <span class="cp-font-extrabold cp-text-gray-900 dark:cp-text-white cp-text-base cp-mt-1 cp-block">{{ $addressString ?: 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Payments Table -->
                    <div class="portal-card cp-p-6 cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl">
                        <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-tracking-wide cp-flex cp-items-center cp-gap-2.5">
                            <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500 dark:cp-text-indigo-400">
                                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </span>
                            Recent Payments
                        </h3>
                        <hr class="cp-my-4 cp-border-gray-100 dark:cp-border-white/5">

                        @if(empty($recentPayments) || $recentPayments->isEmpty())
                            <div class="cp-text-center cp-py-8 cp-text-gray-400 dark:cp-text-slate-500">
                                No payment history found.
                            </div>
                        @else
                            <div class="cp-overflow-x-auto">
                                <table class="cp-w-full cp-text-left cp-border-collapse">
                                    <thead>
                                        <tr class="cp-border-b cp-border-gray-100 dark:cp-border-white/5 cp-text-gray-400 dark:cp-text-slate-400 cp-text-xs cp-font-bold cp-uppercase">
                                            <th class="cp-pb-3">Date</th>
                                            <th class="cp-pb-3">Invoice No</th>
                                            <th class="cp-pb-3">Method</th>
                                            <th class="cp-pb-3 cp-text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="cp-text-sm cp-font-semibold cp-text-gray-900 dark:cp-text-white">
                                        @foreach($recentPayments as $payment)
                                            <tr class="cp-border-b cp-border-gray-55 dark:cp-border-white/5 last:cp-border-none hover:cp-bg-gray-50/50 dark:hover:cp-bg-white/5 cp-transition-colors">
                                                <td class="cp-py-3.5">{{ \Carbon\Carbon::parse($payment->collection_date)->format('d M, Y') }}</td>
                                                <td class="cp-py-3.5 cp-font-mono cp-text-xs">{{ $payment->invoice_no ?? 'N/A' }}</td>
                                                <td class="cp-py-3.5">
                                                    <span class="cp-inline-flex cp-px-2.5 cp-py-1 cp-rounded-lg cp-text-xs cp-font-extrabold cp-uppercase cp-bg-indigo-500/10 cp-text-indigo-500">
                                                        {{ $payment->payment_method ?? 'Cash' }}
                                                    </span>
                                                </td>
                                                <td class="cp-py-3.5 cp-text-right cp-font-black cp-text-emerald-500">৳ {{ number_format($payment->collection_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Panel (5 Columns) -->
                <div class="lg:cp-col-span-5 cp-space-y-6">
                    
                    <!-- Connection & Billing Validity Status -->
                    <div class="portal-card cp-p-6 cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl cp-text-left">
                        <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-tracking-wide cp-flex cp-items-center cp-gap-2.5">
                            <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500 dark:cp-text-indigo-400">
                                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </span>
                            Account Status
                        </h3>
                        <hr class="cp-my-4 cp-border-gray-100 dark:cp-border-white/5">

                        <div class="cp-space-y-4">
                            <!-- Expiry date -->
                            <div>
                                <span class="cp-text-xs cp-text-gray-400 dark:cp-text-slate-400 cp-block cp-font-bold">Next Expiration Date</span>
                                <span class="cp-font-black cp-text-gray-900 dark:cp-text-white cp-text-lg cp-mt-1 cp-block">
                                    {{ $billing->auto_disable_date ? \Carbon\Carbon::parse($billing->auto_disable_date)->format('d F, Y') : 'N/A' }}
                                </span>
                            </div>

                            <!-- Validity Progress Bar -->
                            @php
                                $daysLeft = $this->getDaysUntilExpiry();
                            @endphp
                            @if($daysLeft !== null)
                                <div>
                                    <div class="cp-flex cp-justify-between cp-items-center cp-mb-2">
                                        <span class="cp-text-xs cp-font-bold cp-text-gray-500 dark:cp-text-slate-400">Remaining Validity</span>
                                        <span class="cp-text-xs cp-font-black {{ $daysLeft <= 3 ? 'cp-text-rose-500' : 'cp-text-indigo-500' }}">
                                            {{ $daysLeft }} Days Left
                                        </span>
                                    </div>
                                    <div class="cp-w-full cp-h-2.5 cp-bg-gray-100 dark:cp-bg-slate-950 cp-rounded-full cp-overflow-hidden">
                                        <div class="cp-h-full {{ $daysLeft <= 3 ? 'cp-bg-rose-500 cp-animate-pulse' : 'cp-bg-indigo-500' }}" 
                                             style="width: {{ min(100, max(0, ($daysLeft / 30) * 100)) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            @if($dueAmount > 0)
                                <!-- Alert to Pay -->
                                <div class="cp-p-4 cp-bg-rose-500/10 cp-border cp-border-rose-500/20 cp-text-rose-600 dark:cp-text-rose-400 cp-rounded-2xl cp-text-sm cp-flex cp-items-start cp-gap-3">
                                    <svg style="width: 20px; height: 20px; min-width: 20px;" class="cp-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <span class="cp-font-black cp-block">Payment Outstanding!</span>
                                        Please clear your outstanding balance of ৳{{ number_format($dueAmount, 2) }} to maintain uninterrupted service.
                                    </div>
                                </div>
                            @else
                                <!-- Good Standing Alert -->
                                <div class="cp-p-4 cp-bg-emerald-500/10 cp-border cp-border-emerald-500/20 cp-text-emerald-600 dark:cp-text-emerald-400 cp-rounded-2xl cp-text-sm cp-flex cp-items-start cp-gap-3">
                                    <svg style="width: 20px; height: 20px; min-width: 20px;" class="cp-mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <span class="cp-font-black cp-block">Account is in Good Standing</span>
                                        All due amounts are cleared. Thank you for being with us!
                                    </div>
                                </div>
                            @endif

                            <div class="cp-flex cp-flex-col cp-gap-2.5 cp-mt-4">
                                @if($dueAmount > 0)
                                    <a href="{{ route('filament.portal.pages.pay-bill') }}" 
                                       class="btn-shimmer cp-w-full cp-bg-indigo-600 hover:cp-bg-indigo-500 cp-text-white cp-font-bold cp-py-3.5 cp-rounded-2xl cp-shadow-lg cp-transition-all cp-duration-200 cp-flex cp-items-center cp-justify-center cp-gap-2">
                                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        Pay Outstanding Due
                                    </a>
                                @endif

                                <a href="/recharge/voucher" 
                                   class="cp-w-full cp-bg-emerald-600 hover:cp-bg-emerald-500 cp-text-white cp-font-bold cp-py-3.5 cp-rounded-2xl cp-shadow-lg cp-transition-all cp-duration-200 cp-flex cp-items-center cp-justify-center cp-gap-2">
                                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                    Recharge with Voucher
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Billing Summary -->
                    <div class="portal-card cp-p-6 cp-bg-white dark:cp-bg-slate-900/60 cp-border cp-border-gray-100 dark:cp-border-white/5 cp-rounded-3xl cp-shadow-xl">
                        <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-tracking-wide cp-flex cp-items-center cp-gap-2.5">
                            <span class="cp-p-2 cp-bg-indigo-500/10 cp-rounded-xl cp-text-indigo-500 dark:cp-text-indigo-400">
                                <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </span>
                            Billing Breakdown
                        </h3>
                        <hr class="cp-my-4 cp-border-gray-100 dark:cp-border-white/5">

                        <div class="cp-space-y-3.5">
                            <!-- Monthly Rent -->
                            <div class="cp-flex cp-justify-between cp-items-center">
                                <span class="cp-text-sm cp-font-medium cp-text-gray-500 dark:cp-text-slate-400">Monthly Plan Rent</span>
                                <span class="cp-text-sm cp-font-extrabold cp-text-gray-900 dark:cp-text-white">৳ {{ number_format($billing->monthly_rent ?? 0, 2) }}</span>
                            </div>

                            <!-- Previous Due -->
                            <div class="cp-flex cp-justify-between cp-items-center">
                                <span class="cp-text-sm cp-font-medium cp-text-gray-500 dark:cp-text-slate-400">Previous Outstanding Due</span>
                                <span class="cp-text-sm cp-font-extrabold cp-text-gray-900 dark:cp-text-white">৳ {{ number_format($billing->previous_due ?? 0, 2) }}</span>
                            </div>

                            <!-- Additional Charge -->
                            <div class="cp-flex cp-justify-between cp-items-center">
                                <span class="cp-text-sm cp-font-medium cp-text-gray-500 dark:cp-text-slate-400">Additional Charges</span>
                                <span class="cp-text-sm cp-font-extrabold cp-text-gray-900 dark:cp-text-white">৳ {{ number_format($billing->additional_charge ?? 0, 2) }}</span>
                            </div>

                            <!-- VAT -->
                            <div class="cp-flex cp-justify-between cp-items-center">
                                <span class="cp-text-sm cp-font-medium cp-text-gray-500 dark:cp-text-slate-400">Government Tax / VAT</span>
                                <span class="cp-text-sm cp-font-extrabold cp-text-gray-900 dark:cp-text-white">৳ {{ number_format($billing->vat ?? 0, 2) }}</span>
                            </div>

                            <!-- Discount -->
                            @if(($billing->discount ?? 0) > 0)
                                <div class="cp-flex cp-justify-between cp-items-center">
                                    <span class="cp-text-sm cp-font-medium cp-text-gray-500 dark:cp-text-slate-400">Discount Applied</span>
                                    <span class="cp-text-sm cp-font-extrabold cp-text-emerald-500">- ৳ {{ number_format($billing->discount, 2) }}</span>
                                </div>
                            @endif

                            <!-- Advance Paid -->
                            @if(($billing->advance ?? 0) > 0)
                                <div class="cp-flex cp-justify-between cp-items-center">
                                    <span class="cp-text-sm cp-font-medium cp-text-gray-500 dark:cp-text-slate-400">Advance Balance</span>
                                    <span class="cp-text-sm cp-font-extrabold cp-text-emerald-500">- ৳ {{ number_format($billing->advance, 2) }}</span>
                                </div>
                            @endif

                            <hr class="cp-my-3 cp-border-gray-100 dark:cp-border-white/5">

                            <!-- Net Payable / Due -->
                            <div class="cp-flex cp-justify-between cp-items-center cp-pt-1">
                                <span class="cp-text-sm cp-font-black cp-text-gray-900 dark:cp-text-white">Net Balance Due</span>
                                <span class="cp-text-base cp-font-black {{ $dueAmount > 0 ? 'cp-text-rose-500' : 'cp-text-emerald-500' }}">
                                    ৳ {{ number_format($dueAmount, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- Custom Review Popup Modal --}}
        @if($showReviewModal)
            <div class="cp-fixed cp-inset-0 cp-z-50 cp-flex cp-items-center cp-justify-center cp-p-4 cp-bg-black/60 cp-backdrop-blur-sm">
                <div class="cp-bg-white dark:cp-bg-slate-900 cp-border cp-border-gray-100 dark:cp-border-white/10 cp-rounded-3xl cp-shadow-2xl cp-w-full cp-max-w-md cp-p-6 cp-relative cp-overflow-hidden">
                    <h3 class="cp-text-lg cp-font-bold cp-text-gray-900 dark:cp-text-white cp-mb-2 cp-text-center">
                        {{ $existingReview ? 'Update Your Review' : 'We value your opinion!' }}
                    </h3>
                    <p class="cp-text-xs cp-text-gray-500 dark:cp-text-slate-400 cp-text-center cp-mb-4">
                        আমাদের সেবার মান উন্নয়নে আপনার মতামত দিন।
                    </p>

                    <form wire:submit.prevent="submitReview" class="cp-space-y-4">
                        {{-- Star Rating --}}
                        <div class="cp-flex cp-justify-center cp-gap-2 cp-my-3">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="$set('reviewRating', {{ $i }})" class="cp-transition-transform hover:cp-scale-125 focus:cp-outline-none">
                                    <svg class="cp-w-8 cp-h-8 {{ $reviewRating >= $i ? 'cp-text-amber-400 fill-amber-400' : 'cp-text-gray-300 dark:cp-text-slate-700' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            @endfor
                        </div>

                        {{-- Comment Box --}}
                        <div>
                            <label class="cp-block cp-text-xs cp-font-bold cp-text-gray-400 dark:cp-text-slate-400 cp-mb-1">Your Comment</label>
                            <textarea wire:model="reviewComment" rows="4" required placeholder="Write your review here..." class="cp-w-full cp-px-4 cp-py-3 cp-text-sm cp-bg-gray-55 dark:cp-bg-slate-950 cp-border cp-border-gray-200 dark:cp-border-white/10 cp-rounded-2xl focus:cp-outline-none focus:cp-ring-2 focus:cp-ring-indigo-500/20 focus:cp-border-indigo-500 dark:cp-text-white"></textarea>
                            @error('reviewComment')
                                <span class="cp-text-rose-500 cp-text-xs cp-block cp-mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="cp-flex cp-gap-2.5 cp-pt-2">
                            <button type="button" wire:click="$set('showReviewModal', false)" class="cp-flex-1 cp-py-3 cp-bg-gray-100 hover:cp-bg-gray-200 dark:cp-bg-slate-800 dark:hover:cp-bg-slate-700 cp-text-gray-700 dark:cp-text-gray-300 cp-text-sm cp-font-bold cp-rounded-2xl cp-transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="cp-flex-1 cp-py-3 cp-bg-indigo-600 hover:cp-bg-indigo-500 cp-text-white cp-text-sm cp-font-bold cp-rounded-2xl cp-shadow-lg cp-transition-colors">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- Javascript timer for 30 minutes review prompt --}}
        @if($timerRemainingMs > 0)
            <script>
                document.addEventListener('livewire:initialized', () => {
                    setTimeout(() => {
                        @this.call('triggerReviewModal');
                    }, {{ $timerRemainingMs }});
                });
            </script>
        @endif
    </div>
</x-filament-panels::page>
