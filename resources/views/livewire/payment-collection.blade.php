<div class="px-md-4 zoom-in" x-data @focus-paid-amount.window="document.getElementById('paid_amount').focus()">
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            <i class="bi bi-wallet2 me-2 text-success"></i>{{ __('Payment Collection') }}
        </h2>
    </x-slot>
    <div class="row g-3 d-flex justify-content-center">
        <!-- Search Box Panel -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <x-mikrotik.section-form>
                <x-slot name="title">
                    <span class="text-success fw-bold"><i class="bi bi-search me-2"></i>{{ __('Search Customer') }}</span>
                </x-slot>
                <x-slot name="aside">
                    <div class="position-relative">
                        <input
                            type="search"
                            name="customer_list"
                            class="form-control form-control-sm w-100 shadow-sm"
                            placeholder="{{ siteUrlSettings('customer_id_prefix') ?: 'FCNET' }}-XXX, name, mobile"
                            wire:model.live="customer_list"
                            autocomplete="off"
                            tabindex="1"
                            wire:keydown.arrow-down="incrementHighlight"
                            wire:keydown.arrow-up="decrementHighlight"
                            wire:keydown.enter="selectHighlightedCustomer"
                            id="customer_list"
                            style="border-radius: 8px;"
                            autofocus
                        >
                        @if (!empty($customers))
                            <ul class="scrollbar-overlay overflow-auto list-group position-absolute w-100 shadow-lg mt-1" style="max-height:20rem; z-index: 1050; border-radius: 8px;">
                                @foreach ($customers as $index => $customer)
                                    <li
                                        wire:click="selectCustomer('{{ encrypt($customer->customer_unique_id) }}')"
                                        class="list-group-item list-group-item-action py-2 {{ $index === $highlightedIndex ? 'active bg-success border-success text-white' : '' }}"
                                        style="cursor: pointer; font-size: 0.85rem;"
                                        wire:key="customer-{{ $customer->id }}"
                                    >
                                        <div class="fw-bold">{{ $customer->customer_unique_id }}</div>
                                        <div class="text-muted small {{ $index === $highlightedIndex ? 'text-white-50' : '' }}">
                                            {{ $customer->customer_name }} | {{ $customer->mobile }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </x-slot>
            </x-mikrotik.section-form>
        </div>

        <!-- Customer Detail & Payment Form Panel -->
        <div class="col-lg-7 col-md-7 col-sm-12">
            @if (!empty($info_data))
                <x-mikrotik.section-form :class="'row'" x-init="$dispatch('focus-paid-amount')">
                    <x-slot name="title">
                        <span class="text-success fw-bold"><i class="bi bi-person-badge me-2"></i>{{ __('Customer Bill Summary') }}</span>
                    </x-slot>
                    <x-slot name="aside">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Customer ID') }}</td>
                                            <td class="fw-bold text-dark py-2">{{ $info_data->customer_unique_id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Name') }}</td>
                                            <td class="fw-bold text-dark py-2">{{ $info_data->customer_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Billing Type') }}</td>
                                            <td class="text-dark py-2">
                                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.75rem;">{{ __(ucfirst($info_data->billing->billing_type)) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('PPPoE Username') }}</td>
                                            <td class="text-dark py-2">{{ $info_data->pppUser->username ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Address') }}</td>
                                            <td class="text-muted py-2" style="font-size: 0.85rem;">
                                                @foreach ($info_data->customerAddress as $address)
                                                    {{ $address->input_type_dropdown }},
                                                    {{ $address->input_type_test }}
                                                    @if($address->input_type_textarea)
                                                        | {{ $address->input_type_textarea }}
                                                    @endif
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Status') }}</td>
                                            @php
                                                $badge = match($info_data->status) {
                                                    'active' => 'bg-success',
                                                    'pending' => 'bg-warning text-dark',
                                                    'free' => 'bg-info text-dark',
                                                    default => 'bg-danger',
                                                };
                                            @endphp
                                            <td class="py-2">
                                                <span class="badge rounded-pill {{ $badge }} px-2.5 py-1" style="font-size: 0.75rem;">{{ __(ucfirst($info_data->status)) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Expire Date') }}</td>
                                            <td class="text-dark py-2" style="font-size: 0.85rem;">
                                                <i class="bi bi-calendar-event text-success me-1"></i>
                                                {{ \Carbon\Carbon::parse($this->info_data->billing->auto_disable_date)->format('d-M-Y') }} 
                                                <span class="text-muted">({{ __('AutoDisable:') }} {{ $this->info_data->billing->auto_disable }})</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-2">{{ __('Monthly Rent') }}</td>
                                            <td class="text-end fw-semibold py-2">{{ $info_data->billing->monthly_rent }} {{ siteUrlSettings('site_currency') }}</td>
                                        </tr>
                                        @if ($info_data->billing->additional_charge != 0)
                                            <tr>
                                                <td class="text-muted py-2">{{ __('Additional Charge') }}</td>
                                                <td class="text-end text-danger py-2">+{{ $info_data->billing->additional_charge }} {{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                        @endif
                                        @if ($info_data->billing->discount != 0)
                                            <tr>
                                                <td class="text-muted py-2">{{ __('Discount') }}</td>
                                                <td class="text-end text-success py-2">-{{ $info_data->billing->discount }} {{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                        @endif
                                        @if ($info_data->billing->advance != 0)
                                            <tr>
                                                <td class="text-muted py-2">{{ __('Advance') }}</td>
                                                <td class="text-end text-success py-2">-{{ $info_data->billing->advance }} {{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                        @endif
                                        @if ($info_data->billing->vat != 0)
                                            <tr>
                                                <td class="text-muted py-2">{{ __('Vat (%)') }}</td>
                                                <td class="text-end py-2">{{ $info_data->billing->vat }}%</td>
                                            </tr>
                                        @endif
                                        @if ($info_data->billing->previous_due != 0)
                                            <tr>
                                                <td class="text-muted py-2">{{ __('Previous Due') }}</td>
                                                <td class="text-end text-danger py-2">+{{ $info_data->billing->previous_due }} {{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                        @endif
                                        <tr class="table-light">
                                            <td class="fw-bold py-2">{{ __('Subtotal Sum') }}</td>
                                            <td class="text-end fw-bold py-2">{{ $info_data->billing->total_amount }} {{ siteUrlSettings('site_currency') }}</td>
                                        </tr>
                                        <tr class="bg-success-subtle">
                                            <th class="text-success py-2">{{ __('Total Payable Amount') }}</th>
                                            <th class="text-end text-success py-2">{{ $info_data->billing->due_amount }} {{ siteUrlSettings('site_currency') }}</th>
                                        </tr>
                                        @if(count($collectionSummary) > 0)
                                        <tr>
                                            <td class="text-muted py-2" colspan="2">
                                                <div class="fw-bold text-dark mb-1">{{ __('Collected History') }}</div>
                                                <div class="bg-light p-2 rounded-3" style="font-size: 0.8rem;">
                                                    @foreach ($collectionSummary as $summary)
                                                        <div class="d-flex justify-content-between text-muted border-bottom py-1">
                                                            <span>#{{ $summary->customer_collection_unique_id }} ({{ \Carbon\Carbon::parse($summary->collection_date)->format('d-M-Y') }})</span>
                                                            <span class="fw-bold text-success">{{ $summary->collection_amount }} {{ siteUrlSettings('site_currency') }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr class="table-warning">
                                            <th class="py-2">{{ __('Current Payable Due') }}</th>
                                            <th class="text-end text-danger py-2">{{$due_amount}} {{ siteUrlSettings('site_currency') }}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Actions Form -->
                        <div class="col-md-12 mt-4 pt-3 border-top">
                            <form wire:submit.prevent="savePayment" class="row g-2 align-items-center">
                                <div class="col-md-3 col-sm-12">
                                    <input type="number" class="form-control form-control-sm" name="paid_amount" id="paid_amount" wire:model="paid_amount" wire:keyup="calculatePayment" min="1" autofocus placeholder="{{ __('Pay Amount') }}" required style="border-radius: 6px;">
                                </div>
                                <div class="col-md-3 col-sm-12">
                                    <input type="text" class="form-control form-control-sm" name="invoice" id="invoice" wire:model.live="invoice" placeholder="{{ __('Invoice No') }}" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-3 col-sm-12">
                                    <input type="date" class="form-control form-control-sm" name="expire_date" id="expire_date" wire:model.live="expire_date" style="border-radius: 6px;">
                                </div>
                                <div class="col-md-3 col-sm-12">
                                    <button class="btn btn-sm btn-success w-100 py-1.5" style="border-radius: 6px;">
                                        <i class="bi bi-check-circle me-1"></i>{{ __('Pay Now') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </x-slot>
                </x-mikrotik.section-form>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    Livewire.on('focusInput', () => {
        document.getElementById('customer_list').focus();
    });
</script>
@endpush
