@vite(['resources/sass/app.scss', 'resources/css/app.css', 'resources/js/app.js'])

<div id="print-section" class="col-md-12 p-0" style='min-height: 297mm;'>
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
                            <div class="fw-bold fs-3 text-uppercase">Invoice</div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-5 d-flex align-items-center">
                            <img class="img-fluid w-100" src="{{ asset('images/arrow_bg.svg') }}" alt="">
                        </div>
                        <div class="col-7 invoice_info_list">
                            <p class="m-0 z-1 position-relative pe-3">Invoice No:
                                <b class="text-dark fw-bold">
                                    #{{ $record->id }}
                                </b>
                            </p>
                            <p class="m-0 z-1 position-relative">Date: <b class="text-dark fw-bold">{{ now()->format('d-M-Y') }}</b></p>
                            <div class="invoice_info_list_bg accent_bg_10"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div><b class="text-dark">Invoice To:</b></div>
                            <div>
                                {{ $record->customer?->customer_name }} <br>
                                @foreach ($record->customer?->customerAddress ?? [] as $address)
                                    {{ $address->input_type_dropdown }},
                                    {{ $address->input_type_test }},
                                    {{ $address->input_type_textarea }}
                                @endforeach <br>
                                {{ $record->customer?->mobile }} <br>
                                {{ $record->customer?->email }} <br>
                            </div>
                        </div>
                        <div class="col text-end">
                            <div><b class="text-dark">Pay To:</b></div>
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
                                    <th>Description</th>
                                    <th>Bill Of Month</th>
                                    <th class='text-center' colspan="2">Bill Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        User ID: {{ $record->customer?->customer_unique_id }} <br>
                                        Connection Date:
                                        {{ $record->customer?->connection_date ? \Carbon\Carbon::parse($record->customer->connection_date)->format('d-M-Y') : '' }}
                                        <br>
                                        PPPoE Username: {{ $record->customer?->pppUser?->username ?? '' }}
                                        <br>
                                        Billing Type: {{ $record->customer?->billing?->billing_type ?? '' }}
                                        <br>
                                        Status : <span
                                            class='badge rounded-pill ms-2 badge-subtle-success'>{{ $record->customer?->pppUser?->status ?? '' }}</span>
                                    </td>
                                    <td>
                                        {{ $record->summary_date ? \Carbon\Carbon::parse($record->summary_date)->format('M Y') : '' }}
                                        <br>
                                    </td>
                                    <td class="text-end">
                                        Monthly Rent: <br>
                                        Vat (%): <br>
                                        Additional: <br>
                                        Previous Due: <br>
                                    </td>
                                    <td class="text-end pe-5">
                                        {{ $record->monthly_rent ?? 0 }}
                                        {{ siteUrlSettings('site_currency') }}<br>
                                        {{ $record->vat ?? 0 }}
                                        {{ siteUrlSettings('site_currency') }}<br>
                                        {{ $record->additional_charge ?? 0 }}
                                        {{ siteUrlSettings('site_currency') }}<br>
                                        {{ $record->previous_due ?? 0 }}
                                        {{ siteUrlSettings('site_currency') }}<br>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class='col'>
                            <div class="row">
                                <div class="col-7">
                                    <p class="mb-1 mt-3"><b class="text-dark">Payment
                                            info:</b></p>
                                    <p class="ms-3">
                                        @foreach ($record->monthlyCollections as $summary)
                                            <span class="text-end">{{ \Carbon\Carbon::parse($summary->collection_date)->format('d-M-Y') }} -> {{ $summary->collection_amount }} {{ siteUrlSettings('site_currency') }}</span><br>
                                        @endforeach</p>
                                </div>
                                <div class="col-5">
                                    <table class='table'>
                                        <tbody>
                                            <tr>
                                                <td class="text-dark fw-bold py-1">Subtotal</td>
                                                <td
                                                    class="text-dark fw-bold text-end py-1 pe-4">
                                                    {{ number_format(($record->monthly_rent ?? 0) + ($record->additional_charge ?? 0) + ($record->previous_due ?? 0) + ($record->vat ?? 0), 2) }}
                                                    {{ siteUrlSettings('site_currency') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold py-1">Discount</td>
                                                <td
                                                    class="fw-bold text-end py-1 pe-4">
                                                    -{{ $record->discount ?? 0 }}
                                                    {{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold py-1">Advance</td>
                                                <td
                                                    class="fw-bold text-end py-1 pe-4">
                                                    -{{ $record->advance ?? 0 }}
                                                    {{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-dark fw-bold py-1">Grand Total</td>
                                                <td
                                                    class="text-dark fw-bold text-end py-1 pe-4">
                                                    {{ number_format(($record->monthly_rent ?? 0) + ($record->additional_charge ?? 0) + ($record->previous_due ?? 0) + ($record->vat ?? 0) - ($record->discount ?? 0) - ($record->advance ?? 0), 2) }}
                                                    {{ siteUrlSettings('site_currency') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold py-1">Paid Amount</td>
                                                <td
                                                    class="fw-bold text-end py-1 pe-4">
                                                    {{ $record->paid_amount ?? 0 }}{{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-dark fw-bold py-1">Last Due Amount</td>
                                                <td
                                                    class="text-dark fw-bold text-end py-1 pe-4">
                                                    {{ number_format(($record->monthly_rent ?? 0) + ($record->additional_charge ?? 0) + ($record->previous_due ?? 0) + ($record->vat ?? 0) - ($record->discount ?? 0) - ($record->advance ?? 0) - ($record->paid_amount ?? 0), 2) }}{{ siteUrlSettings('site_currency') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row p-4 pt-0">
                        <div class="text-dark fw-bold">
                            Terms &amp; Conditions:
                        </div>
                        <ul>
                            <li>Pay the bill within 7–10 days of the billing date.</li>
                            <li>Late payments may result in extra charges.</li>
                            <li>Service may be suspended for non-payment.</li>
                            <li>Payments are non-refundable after activation.</li>
                            <li>Temporary service disruptions may occur due to technical or natural issues.</li>
                            <li>Provide accurate personal and contact information.</li>
                            <li>Illegal or abusive service use is strictly prohibited.</li>
                            <li>Violation of these terms may lead to suspension or termination.</li>
                        </ul>
                    </div>

                    <div class="pt-5 text-center">
                        ***This is computer generated invoice. No signature required***

                        Thank you for your prompt payment.
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

<div class="col-md-12 text-center mt-4" x-data='{
    handlePrint() {
        const printElement = document.getElementById("print-section");
        if (!printElement) return;

        const iframe = document.createElement("iframe");
        iframe.style.position = "fixed";
        iframe.style.width = "0px";
        iframe.style.height = "0px";
        iframe.style.visibility = "hidden";
        iframe.style.border = "none";
        iframe.style.zIndex = "-1";
        document.body.appendChild(iframe);

        let cssLinks = Array.from(document.querySelectorAll("link[rel=stylesheet]"))
            .map(link => `<link rel="stylesheet" href="${link.href}">`)
            .join("");

        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write("<!DOCTYPE html><html><head><title>Invoice</title>");
        doc.write(cssLinks);
        doc.write("<style>body { background-color: white !important; margin: 0; padding: 0px; } * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }</style>");
        doc.write("</head><body>");
        doc.write(printElement.outerHTML);
        doc.write("</body></html>");
        doc.close();

        iframe.contentWindow.onload = () => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
            setTimeout(() => {
                document.body.removeChild(iframe);
            }, 1000);
        };
    }
}'>
    <button class="btn btn-sm btn-primary no-print" x-on:click="handlePrint()">Print Invoice</button>
</div>

<style>
    @media print {
        .fi-sidebar, .fi-topbar, .fi-header, .no-print {
            display: none !important;
        }
        .fi-main-ctn {
            margin-left: 0 !important;
            padding-top: 0 !important;
        }
        body {
            background-color: white !important;
        }
    }
</style>
