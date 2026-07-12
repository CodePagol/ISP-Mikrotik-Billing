<div class="px-4 zoom-in">
    <x-slot name="header">
        <h2 class="h4 font-weight-bold text-dark mb-0">
            <i class="bi bi-file-earmark-bar-graph me-2 text-success"></i>{{ __('Customer Summary') }}
        </h2>
    </x-slot>

    <div class="row g-3 d-flex justify-content-center mb-3">
        <!-- Search Panel -->
        <div class="col-lg-4 col-md-5 col-sm-12">
            <x-mikrotik.section-form>
                <x-slot name="title">
                    <span class="text-success fw-bold"><i class="bi bi-search me-2"></i>{{ __('Search Customer') }}</span>
                </x-slot>
                <x-slot name="aside">
                    @include('livewire.partials.customer-search')
                </x-slot>
            </x-mikrotik.section-form>
        </div>

        <!-- Customer Basic Info -->
        <div class="col-lg-7 col-md-7 col-sm-12">
            @if (!empty($info_data))
                <x-mikrotik.section-form>
                    <x-slot name="title">
                        <span class="text-success fw-bold"><i
                                class="bi bi-person-badge me-2"></i>{{ __('Customer Information') }}</span>
                    </x-slot>
                    <x-slot name="aside">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <tbody>
                                    <tr>
                                        <td class="text-muted py-2">{{ __('Customer ID') }}</td>
                                        <td class="fw-bold text-dark py-2">{{ $info_data->customer_unique_id }}</td>
                                        <td class="text-muted py-2">{{ __('Name') }}</td>
                                        <td class="fw-bold text-dark py-2">{{ $info_data->customer_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ __('Billing Type') }}</td>
                                        <td class="py-2">
                                            <span class="badge bg-light text-dark border px-2 py-1"
                                                style="font-size: 0.75rem;">{{ __(ucfirst($info_data->billing->billing_type)) }}</span>
                                        </td>
                                        <td class="text-muted py-2">{{ __('PPPoE Username') }}</td>
                                        <td class="py-2">{{ $info_data->pppUser->username ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ __('Address') }}</td>
                                        <td colspan="3" class="text-muted py-2" style="font-size: 0.85rem;">
                                            @foreach ($info_data->customerAddress as $address)
                                                {{ $address->input_type_dropdown }},
                                                {{ $address->input_type_test }}
                                                @if ($address->input_type_textarea)
                                                    | {{ $address->input_type_textarea }}
                                                @endif
                                            @endforeach
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted py-2">{{ __('Status') }}</td>
                                        @php
                                            $badge = match ($info_data->status) {
                                                'active' => 'bg-success',
                                                'pending' => 'bg-warning text-dark',
                                                'free' => 'bg-info text-dark',
                                                default => 'bg-danger',
                                            };
                                        @endphp
                                        <td class="py-2">
                                            <span class="badge rounded-pill {{ $badge }} px-2.5 py-1"
                                                style="font-size: 0.75rem;">{{ __(ucfirst($info_data->status)) }}</span>
                                        </td>
                                        <td class="text-muted py-2">{{ __('Expire Date') }}</td>
                                        <td class="text-dark py-2">
                                            <i class="bi bi-calendar-event text-success me-1"></i>
                                            {{ \Carbon\Carbon::parse($this->info_data->billing->auto_disable_date)->format('d-M-Y') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </x-slot>
                </x-mikrotik.section-form>
            @endif
        </div>
    </div>

    <!-- Payments Ledger Summary Section -->
    @if (!empty($info_data))
        <div class="row g-3 d-flex justify-content-center">
            <div class="col-12">
                <x-mikrotik.section-form>
                    <x-slot name="title">
                        <span class="text-success fw-bold"><i
                                class="bi bi-credit-card-2-back me-2"></i>{{ __('Customer Payment Ledger') }}</span>
                    </x-slot>
                    <x-slot name="aside">
                        <div class="table-responsive">
                            <table id="payment_summary"
                                class="data-table table table-sm table-hover align-middle display table-bordered scrollbar"
                                style="font-size: 0.85rem;">
                                <thead class="table-success text-success text-center">
                                    <tr>
                                        <th class="py-2">{{ __('Date') }}</th>
                                        <th class="py-2">{{ __('Monthly Rent') }}</th>
                                        <th class="py-2">{{ __('Discount') }}</th>
                                        <th class="py-2">{{ __('Advance') }}</th>
                                        <th class="py-2">{{ __('Add. Charge') }}</th>
                                        <th class="py-2">{{ __('Vat (%)') }}</th>
                                        <th class="py-2">{{ __('Previous Due') }}</th>
                                        <th class="py-2">{{ __('Bill Amount') }}</th>
                                        <th class="py-2">{{ __('Collection Amount') }}</th>
                                        <th class="py-2">{{ __('Total Due') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    @foreach ($info_data->paymentSummary->sortByDesc('summary_date') as $paymentSummary)
                                        @php
                                            $collectionStartDate = \Carbon\Carbon::parse(
                                                $paymentSummary->summary_date,
                                            )->startOfMonth();
                                            $collectionEndDate = \Carbon\Carbon::parse(
                                                $paymentSummary->summary_date,
                                            )->endOfMonth();

                                            $collections = $info_data->collectionSummary
                                                ->whereBetween('collection_date', [
                                                    $collectionStartDate,
                                                    $collectionEndDate,
                                                ])
                                                ->values()
                                                ->toArray();
                                            $bill_amount =
                                                $paymentSummary->monthly_rent +
                                                $paymentSummary->additional_charge +
                                                $paymentSummary->previous_due -
                                                ($paymentSummary->discount + $paymentSummary->advance);
                                        @endphp
                                        <tr class="table-light fw-bold text-dark">
                                            <td>{{ \Carbon\Carbon::parse($paymentSummary->summary_date)->format('d-M-Y') }}
                                            </td>
                                            <td>{{ $paymentSummary->monthly_rent }}</td>
                                            <td>{{ $paymentSummary->discount }}</td>
                                            <td>{{ $paymentSummary->advance }}</td>
                                            <td>{{ $paymentSummary->additional_charge }}</td>
                                            <td>{{ $paymentSummary->vat }}%</td>
                                            <td>{{ $paymentSummary->previous_due }}</td>
                                            <td class="text-dark">{{ $bill_amount }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                        </tr>

                                        @if ($collections)
                                            @foreach ($collections as $collection)
                                                <tr class="table-success-subtle">
                                                    <td class="text-muted"><i
                                                            class="bi bi-arrow-return-right me-1 text-success"></i>{{ date('d-M-Y', strtotime($collection['collection_date'])) }}
                                                    </td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td class="text-end text-muted small">
                                                        {{ __('Collected By:') }} {{ $collection['collected_by'] }}
                                                    </td>
                                                    <td class="text-success fw-semibold">
                                                        {{ $collection['collection_amount'] }}
                                                    </td>
                                                    <td class="text-danger fw-semibold">
                                                        {{ $bill_amount - $collection['collection_amount'] }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="text-muted small text-end">{{ __('No Collection') }}</td>
                                                <td class="text-muted">0</td>
                                                <td class="text-danger fw-semibold">{{ $bill_amount }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-slot>
                </x-mikrotik.section-form>
            </div>
        </div>
    @endif
</div>

@push('styles')
    <style>
        #payment_summary th,
        #payment_summary td {
            padding: 0.4rem 0.5rem;
        }

        .table-success-subtle {
            background-color: rgba(40, 167, 69, 0.04) !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function initializeDataTable() {
            if ($('#payment_summary').length === 0) {
                return;
            }
            setTimeout(() => {
                if ($.fn.DataTable.isDataTable('#payment_summary')) {
                    $('#payment_summary').DataTable().destroy();
                }
                $('#payment_summary').DataTable({
                    searching: false,
                    paging: false,
                    ordering: false,
                    info: false,
                    dom: 'Bfrtip',
                    buttons: [
                        'excel',
                        'print'
                    ]
                });
            }, 200);
        }

        document.addEventListener('DOMContentLoaded', initializeDataTable);
        document.addEventListener('livewire:navigated', initializeDataTable);

        Livewire.on('dataTable', () => {
            initializeDataTable();
        });
    </script>
@endpush
