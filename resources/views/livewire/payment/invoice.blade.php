<div class="px-4 zoom-in" x-data @focus-paid-amount.window="document.getElementById('paid_amount').focus()">
    <x-slot name="header">
        {{ __('Payment Invoice') }}
    </x-slot>
    <div class="row g-2 d-flex justify-content-center">
        <div class="{{ !empty($info_data) ? 'col-lg-4 col-md-5 col-sm-12' : 'col-lg-4 col-md-5 col-sm-12' }}">
            <x-mikrotik.section-form>
                <x-slot name="title">
                    <span class="text-success fw-bold"><i class="bi bi-search me-2"></i>{{ __('Search Customer') }}</span>
                </x-slot>
                <x-slot name="aside">
                    @include('livewire.partials.customer-search')
                </x-slot>
            </x-mikrotik.section-form>
        </div>

        <div class="col-lg-8 col-md-7 col-sm-12">
            @if (!empty($info_data))
                <x-mikrotik.section-form :class="'row'">
                    <x-slot name="title">{{ __('Customer Info') }}</x-slot>
                    <x-slot name="aside">
                        <div id="print-section" class="col-md-12 p-0" style='height: 297mm;'>
                            <div class="container-fluid h-100">
                                <div class="position-relative h-100">
                                    <div class="invoice style1 type3 p-1 h-100">
                                        <div class="position-absolute w-100 top-0 start-0">
                                            <svg width="100%" height="151" viewBox="0 0 850 151"
                                                preserveAspectRatio="none" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M850 0.889398H0V150.889H184.505C216.239 150.889 246.673 141.531 269.113 124.872L359.112 58.0565C381.553 41.3977 411.987 32.0391 443.721 32.0391H850V0.889398Z"
                                                    fill="#4ce8a7" fill-opacity="0.1"></path>
                                            </svg>
                                        </div>
                                        <div class="p-2 h-100">
                                            <div class="row">
                                                <div class="col ps-0 d-flex align-items-start">
                                                    <img class="img-fluid w-75"
                                                        src="{{ site_image(siteUrlSettings('site_logo')) }}"
                                                        alt="Logo">
                                                </div>
                                                <div class="col d-flex justify-content-end align-items-center">
                                                    <div class="fw-bold fs-3 text-uppercase">{{ __('Invoice') }}</div>
                                                </div>
                                            </div>

                                            <div class="row mt-4">
                                                <div class="col-5 d-flex align-items-center">
                                                    <img class="img-fluid w-100" src="images/arrow_bg.svg"
                                                        alt="">
                                                </div>
                                                <div class="col-7 invoice_info_list">
                                                    <p class="m-0 z-1 pe-3">{{ __('Invoice No:') }}
                                                        <b class="text-dark fw-bold">
                                                            @foreach ($collectionSummary as $summary)
                                                                #{{ siteUrlSettings('site_invoice_prefix') }}{{ $summary->id }},
                                                            @endforeach
                                                        </b>
                                                    </p>
                                                    <p class="m-0 z-1">{{ __('Date:') }} <b
                                                            class="text-dark fw-bold">{{ date('d-M-Y') }}</b></p>
                                                    <div class="invoice_info_list_bg accent_bg_10"></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div><b class="text-dark">{{ __('Invoice To:') }}</b></div>
                                                    <div>
                                                        {{ $info_data->customer_name }} <br>
                                                        @foreach ($info_data->customerAddress as $address)
                                                            {{ $address->input_type_dropdown }},
                                                            {{ $address->input_type_test }},
                                                            {{ $address->input_type_textarea }}
                                                        @endforeach <br>
                                                        {{ $info_data->mobile }} <br>
                                                        {{ $info_data->email }} <br>
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
                                                            <th>{{ __('Bill Of Month') }}</th>
                                                            <th class='text-center' colspan="2">
                                                                {{ __('Bill Info') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                {{ __('User ID:') }}
                                                                {{ $info_data->customer_unique_id }} <br>
                                                                {{ __('Connection Date:') }}
                                                                {{ \Carbon\Carbon::parse($info_data->connection_date)->format('d-M-Y') }}
                                                                <br>
                                                                {{ __('PPPoE Username:') }}
                                                                {{ $info_data->pppUser->username ?? 'N/A' }}
                                                                <br>
                                                                {{ __('Billing Type:') }}
                                                                {{ $info_data->billing->billing_type }}
                                                                <br>
                                                                {{ __('Status :') }} <span
                                                                    class='badge rounded-pill ms-2 badge-subtle-success'>{{ $info_data->pppUser->status ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                {{ \Carbon\Carbon::parse($info_data->billing->billing_month)->format('M Y') }}
                                                                <br>
                                                            </td>
                                                            <td class="text-end">
                                                                {{ __('Monthly Rent:') }} <br>
                                                                {{ __('Vat (%):') }} <br>
                                                                {{ __('Additional:') }} <br>
                                                                {{ __('Previous Due:') }} <br>
                                                            </td>
                                                            <td class="text-end pe-5">
                                                                {{ $info_data->billing->monthly_rent }}
                                                                {{ siteUrlSettings('site_currency') }}<br>
                                                                {{ $info_data->billing->vat }}
                                                                {{ siteUrlSettings('site_currency') }}<br>
                                                                {{ $info_data->billing->additional_charge }}
                                                                {{ siteUrlSettings('site_currency') }}<br>
                                                                {{ $info_data->billing->previous_due }}
                                                                {{ siteUrlSettings('site_currency') }}<br>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div class='col'>
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <p class="mb-1 mt-3"><b
                                                                    class="text-dark">{{ __('Payment info:') }}</b></p>
                                                            <p class="ms-3">
                                                                @foreach ($collectionSummary as $summary)
                                                                    <span
                                                                        class="text-end">{{ \Carbon\Carbon::parse($summary->collection_date)->format('d-M-Y') }}
                                                                        -> {{ $summary->collection_amount }}
                                                                        {{ siteUrlSettings('site_currency') }}</span><br>
                                                                @endforeach
                                                            </p>
                                                        </div>
                                                        <div class="col-5">
                                                            <table class='table'>
                                                                <tbody>
                                                                    <tr>
                                                                        <td class="text-dark fw-bold py-1">
                                                                            {{ __('Subtotal') }}</td>
                                                                        <td
                                                                            class="text-dark fw-bold text-end py-1 pe-4">
                                                                            {{ number_format($info_data->billing->monthly_rent + $info_data->billing->additional_charge + $info_data->billing->previous_due + ($info_data->billing->monthly_rent * $info_data->billing->vat) / 100, 2) }}
                                                                            {{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="fw-bold py-1">{{ __('Discount') }}
                                                                        </td>
                                                                        <td class="fw-bold text-end py-1 pe-4">
                                                                            -{{ $info_data->billing->discount }}
                                                                            {{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="fw-bold py-1">{{ __('Advance') }}
                                                                        </td>
                                                                        <td class="fw-bold text-end py-1 pe-4">
                                                                            -{{ $info_data->billing->advance }}
                                                                            {{ siteUrlSettings('site_currency') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-dark fw-bold py-1">
                                                                            {{ __('Grand Total') }}</td>
                                                                        <td
                                                                            class="text-dark fw-bold text-end py-1 pe-4">
                                                                            {{ $info_data->billing->total_amount }}{{ siteUrlSettings('site_currency') }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="fw-bold py-1">
                                                                            {{ __('Paid Amount') }}</td>
                                                                        <td class="fw-bold text-end py-1 pe-4">
                                                                            {{ $info_data->billing->paid_amount }}{{ siteUrlSettings('site_currency') }}
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td class="text-dark fw-bold py-1">
                                                                            {{ __('Last Due Amount') }}</td>
                                                                        <td
                                                                            class="text-dark fw-bold text-end py-1 pe-4">
                                                                            {{ $info_data->billing->due_amount }}{{ siteUrlSettings('site_currency') }}
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row p-4 pt-0">
                                                <div class="text-dark fw-bold">
                                                    {{ __('Terms & Conditions:') }}
                                                </div>
                                                <ul>
                                                    <li>{{ __('Pay the bill within 7–10 days of the billing date.') }}
                                                    </li>
                                                    <li>{{ __('Late payments may result in extra charges.') }}</li>
                                                    <li>{{ __('Service may be suspended for non-payment.') }}</li>
                                                    <li>{{ __('Payments are non-refundable after activation.') }}</li>
                                                    <li>{{ __('Temporary service disruptions may occur due to technical or natural issues.') }}
                                                    </li>
                                                    <li>{{ __('Provide accurate personal and contact information.') }}
                                                    </li>
                                                    <li>{{ __('Illegal or abusive service use is strictly prohibited.') }}
                                                    </li>
                                                    <li>{{ __('Violation of these terms may lead to suspension or termination.') }}
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="row pt-5">
                                                <div class="col">
                                                    <div class="text-dark fw-bold mt-2">
                                                        {{ __('Customer Signature:') }}
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="text-dark fw-bold text-end mt-2">
                                                        {{ __('Authorized Signature:') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="position-absolute w-100 bottom-0 start-0">
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
                        <div class="col-md-12">
                            <button class="btn btn-sm btn-primary"
                                wire:click="printPage">{{ __('Print Invoice') }}</button>
                        </div>
                    </x-slot>
                </x-mikrotik.section-form>
            @endif
        </div>
    </div>
</div>


@push('scripts')
    <script>
        Livewire.on('triggerPrint', () => {
            let cssLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .map(link => link.href);

            printJS({
                printable: 'print-section',
                type: 'html',
                css: cssLinks,
                targetStyles: ['*'],
                scanStyles: false,
            });
        });
        // Listen for the 'focusInput' event from Livewire
        Livewire.on('focusInput', () => {
            // Set focus to the customer_list input after form submission
            document.getElementById('customer_list').focus();
        });
    </script>
@endpush
