<div>
    <div class="card border-0 shadow-sm mx-3 mt-3">
        <div class="card-body p-2">
            <div class="row align-items-center">
                @if ($routers->count() > 1)
                    <div class="col-md-2">
                        <label class="small text-muted fw-bold ps-1">{{ __('Router') }}</label>
                        <select class="form-select border-0 bg-light" id="router_filter">
                            <option value="">{{ __('All Routers') }}</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->router_name }}">{{ $router->router_name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="{{ $routers->count() > 1 ? 'col-md-10' : 'col-md-12' }}">
                    <label class="small text-muted fw-bold ps-1">{{ __('FILTERS') }}</label>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="filter-group d-flex gap-1 overflow-auto pb-1">
                            <input type="radio" class="btn-check" name="collection" id="all_list" autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none fw-600" for="all_list">
                                <i class="bi bi-people-fill me-1"></i> {{ __('All Customers') }} <span class="badge bg-white text-primary fw-bold"></span>
                            </label>
                            
                            <input type="radio" class="btn-check" name="collection" id="all_active_list" autocomplete="off" checked>
                            <label class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-none fw-600" for="all_active_list">
                                <i class="bi bi-people-fill me-1"></i> {{ __('All Active') }} <span class="badge bg-white text-primary fw-bold"></span>
                            </label>

                            <input type="radio" class="btn-check" name="collection" id="collection_list" autocomplete="off">
                            <label class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-none fw-600" for="collection_list">
                                <i class="bi bi-cash-stack me-1"></i> {{ __('Paid') }} <span class="badge bg-white text-success fw-bold"></span>
                            </label>

                            <input type="radio" class="btn-check" name="collection" id="without_collection_list" autocomplete="off">
                            <label class="btn btn-outline-indigo btn-sm rounded-pill px-3 shadow-none fw-600" for="without_collection_list">
                                <i class="bi bi-wallet2 me-1"></i> {{ __('Unpaid') }} <span class="badge bg-white text-indigo fw-bold"></span>
                            </label>

                            <input type="radio" class="btn-check" name="collection" id="pending_customer" autocomplete="off">
                            <label class="btn btn-outline-warning btn-sm rounded-pill px-3 shadow-none fw-600" for="pending_customer">
                                <i class="bi bi-clock-history me-1"></i> {{ __('Pending') }} <span class="badge bg-white text-warning fw-bold"></span>
                            </label>

                            <input type="radio" class="btn-check" name="collection" id="disable_customer" autocomplete="off">
                            <label class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-none fw-600" for="disable_customer">
                                <i class="bi bi-slash-circle me-1"></i> {{ __('Disabled') }} <span class="badge bg-white text-danger fw-bold"></span>
                            </label>

                            <input type="radio" class="btn-check" name="collection" id="inactive_customer" autocomplete="off">
                            <label class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-none fw-600" for="inactive_customer">
                                <i class="bi bi-person-x me-1"></i> {{ __('Inactive') }} <span class="badge bg-white text-secondary fw-bold"></span>
                            </label>
                        </div>
                        <div class="ms-auto border-start ps-2">
                            <a href="{{ route('reseller.customers.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold me-1">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Register Customer') }}
                            </a>
                            <button type="button" id="reset_table" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border" title="{{ __('Reset all filters and search') }}">
                                <i class="bi bi-arrow-clockwise"></i> {{ __('Reset') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm m-3 p-0 overflow-hidden">
        <div class="table-responsive bg-white px-3 pb-3" wire:ignore>
            <table class="customer-table table table-hover custom-data-table border-0 w-100" style="width:100%">
                <thead class="bg-light">
                    <tr>
                        <th class="text-center">{{ __('SL') }}</th>
                        <th class="text-center">{{ __('Customer Identity') }}</th>
                        <th class="text-center">{{ __('Address') }}</th>
                        <th class="text-center">{{ __('Billing Breakdown') }}</th>
                        <th class="text-center">{{ __('Connection Info') }}</th>
                        <th class="text-center">{{ __('Billing Summary') }}</th>
                        <th class="text-center">{{ __('Auto Disable') }}</th>
                        <th class="text-center">{{ __('Action') }}</th>
                        {{-- Raw columns for export (Indices 8-22) --}}
                        <th class="text-center">{{ __('ID') }}</th>
                        <th class="text-center">{{ __('Name') }}</th>
                        <th class="text-center">{{ __('Address') }}</th>
                        <th class="text-center">{{ __('Mobile') }}</th>
                        <th class="text-center">{{ __('IP') }}</th>
                        <th class="text-center">{{ __('Router') }}</th>
                        <th class="text-center">{{ __('Rent') }}</th>
                        <th class="text-center">{{ __('P.Due') }}</th>
                        <th class="text-center">{{ __('Add.') }}</th>
                        <th class="text-center">{{ __('Vat') }}</th>
                        <th class="text-center">{{ __('Disc') }}</th>
                        <th class="text-center">{{ __('Adv') }}</th>
                        <th class="text-center">{{ __('Bill') }}</th>
                        <th class="text-center">{{ __('Paid') }}</th>
                        <th class="text-center">{{ __('Due') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center"></tbody>
                <tfoot class="bg-light border-top">
                    <tr class="page-totals-row table-info">
                        @for($i=0; $i<23; $i++)
                            <th id="page_total_{{ $i }}" @if($i==0) class="text-end fw-bold" @elseif($i==3) class="text-start small" @elseif($i==5) class="text-end" @endif>
                                @if($i==0) {{ __('Page Totals:') }} @endif
                            </th>
                        @endfor
                    </tr>
                    <tr class="grand-totals-row table-primary">
                        @for($i=0; $i<23; $i++)
                            <th id="full_total_{{ $i }}" @if($i==0) class="text-end fw-bold" @elseif($i==3) class="text-start small" @elseif($i==5) class="text-end" @endif>
                                @if($i==0) {{ __('Grand Totals:') }} @endif
                            </th>
                        @endfor
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($editingCustomerId)
        <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog" wire:key="edit-customer-modal-{{ $editingCustomerId }}">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit Customer') }}</h5>
                        <button type="button" class="btn-close" wire:click="closeEditCustomerModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body bg-light" style="padding: 1.5rem; max-height: 85vh; overflow-y: auto;">
                        @livewire('edit-customer', ['customerId' => $editingCustomerId], key($editingCustomerId))
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" wire:click="closeEditCustomerModal">{{ __('Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
    <style>
        .custom-data-table {
            border-collapse: separate !important;
            border-spacing: 0 8px !important;
            font-size: 0.85rem !important;
        }
        .custom-data-table thead th {
            text-transform: uppercase;
            font-weight: 700;
            color: #6c757d;
            border-bottom: 2px solid #f8f9fa !important;
            padding: 12px 10px !important;
            white-space: nowrap;
        }
        .custom-data-table tbody tr {
            background-color: #fff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .custom-data-table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            background-color: #f8f9ff !important;
        }
        .custom-data-table td {
            padding: 12px 10px !important;
            vertical-align: middle !important;
            border-top: 1px solid #f1f1f1 !important;
        }
        .badge-soft {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .action-btns .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.2s;
        }
        .action-btns .btn:hover {
            transform: scale(1.1);
        }
        .fw-600 { font-weight: 600; }
        .btn-outline-indigo {
            color: #6610f2;
            border-color: #6610f2;
        }
        .btn-outline-indigo:hover, .btn-check:checked + .btn-outline-indigo {
            background-color: #6610f2;
            color: #fff;
        }
        .text-indigo { color: #6610f2 !important; }
        .bg-indigo { background-color: #6610f2; color: #fff; }
        
        .billing-card {
            min-width: 120px;
            padding: 5px;
            background: #fdfdfd;
            border-radius: 6px;
        }

        .btn-check:checked + .btn {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
        }
        
        .filter-group::-webkit-scrollbar {
            height: 4px;
        }
        .filter-group::-webkit-scrollbar-thumb {
            background: #e9ecef;
            border-radius: 10px;
        }

        /* DataTables Styling Overrides */
        .dt-container .dt-search input {
            border: 0;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 15px;
        }
        .dt-container .dt-paging .dt-paging-button.current {
            background: #0d6efd !important;
            color: #fff !important;
            border: 0;
            border-radius: 8px;
        }
        .dt-buttons .btn {
            border-radius: 8px !important;
            font-size: 0.8rem;
            margin-right: 5px;
        }
        .dt-paging {
            margin-top: 10px !important;
        }
        .dt-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if ($.fn.dataTable.isDataTable('.customer-table')) {
                $('.customer-table').DataTable().destroy();
            }

            var table = $('.customer-table').DataTable({
                processing: true,
                pagingType: 'full_numbers',
                pageLength: 10,
                lengthChange: true,
                searchable: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                select: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                buttons: [
                    'pageLength',
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> {{ __('Excel') }}',
                        className: 'btn-outline-success',
                        exportOptions: { columns: [0, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22], footer: true }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> {{ __('PDF') }}',
                        className: 'btn-outline-danger',
                        orientation: 'landscape',
                        pageSize: 'LEGAL',
                        exportOptions: { columns: [0, 8, 9, 10, 11, 12, 3, 20, 21, 22], footer: true }
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> {{ __('Print') }}',
                        className: 'btn-outline-dark',
                        exportOptions: { columns: [0, 8, 9, 10, 11, 12, 3, 20, 21, 22], footer: true },
                        customize: function (win) {
                            $(win.document.body).find('h1').css('text-align', 'center').text('{{ __('My Customer Billing Report') }}');
                        }
                    },
                    'colvis'
                ],
                ajax: {
                    url: "{{ route('reseller.customers.data') }}", 
                    data: function(d) {
                        if ($('#all_list').is(':checked')) {
                            d.filter = 'all';
                        } else if ($('#all_active_list').is(':checked')) {
                            d.filter = 'all_active';
                        } else if ($('#without_collection_list').is(':checked')) {
                            d.filter = 'without_collection';
                        } else if ($('#collection_list').is(':checked')) {
                            d.filter = 'collection';
                        } else if ($('#pending_customer').is(':checked')) {
                            d.filter = 'pending';
                        }else if ($('#disable_customer').is(':checked')) {
                            d.filter = 'disable';
                        }else if ($('#inactive_customer').is(':checked')) {
                            d.filter = 'inactive';
                        }
                        d.router_name = $('#router_filter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', title: '{{ __('SL') }}', searchable: false, orderable: false, className: 'text-center' },
                    { data: 'customer_identity', name: 'customer_name', title: '{{ __('Customer Identity') }}', className: 'text-start' },
                    { data: 'customers_address', name: 'customers_address', title: '{{ __('Address') }}', className: 'text-start' },
                    { data: 'billing_breakdown', name: 'billing.monthly_rent', title: '{{ __('Billing Breakdown') }}', className: 'text-start' },
                    { data: 'connection_details', name: 'ppp_user.username', title: '{{ __('Connection Info') }}', className: 'text-start' },
                    { data: 'billing_summary', name: 'billing.total_amount', title: '{{ __('Billing Summary') }}', className: 'text-end' },
                    { data: 'disable_details', name: 'disable_details', title: '{{ __('Auto Disable') }}', className: 'text-center' },
                    { data: 'action', name: 'action', title: '{{ __('Action') }}', orderable: false, searchable: false, className: 'text-center' },
                    
                    // Invisible columns for raw data & totals (8-22)
                    { data: 'customer_unique_id', name: 'customer_unique_id', title: '{{ __('ID') }}', visible: false, searchable: false },
                    { data: 'customer_name', name: 'customer_name', title: '{{ __('Name') }}', visible: false, searchable: false },
                    { data: 'customers_address', name: 'customers_address', title: '{{ __('Address') }}', visible: false, searchable: false },
                    { data: 'mobile', name: 'mobile', title: '{{ __('Mobile') }}', visible: false, searchable: false },
                    { data: 'ppp_user.username', name: 'ppp_user.username', title: '{{ __('IP') }}', visible: false, searchable: false },
                    { data: 'ppp_user.router_name', name: 'ppp_user.router_name', title: '{{ __('Router') }}', visible: false, searchable: false },
                    { data: 'billing.monthly_rent', name: 'billing.monthly_rent', title: '{{ __('Rent') }}', visible: false, searchable: false },
                    { data: 'billing.previous_due', name: 'billing.previous_due', title: '{{ __('P.Due') }}', visible: false, searchable: false },
                    { data: 'billing.additional_charge', name: 'billing.additional_charge', title: '{{ __('Add.') }}', visible: false, searchable: false },
                    { data: 'billing.vat', name: 'billing.vat', title: '{{ __('Vat') }}', visible: false, searchable: false },
                    { data: 'billing.discount', name: 'billing.discount', title: '{{ __('Disc') }}', visible: false, searchable: false },
                    { data: 'billing.advance', name: 'billing.advance', title: '{{ __('Adv') }}', visible: false, searchable: false },
                    { data: 'billing.total_amount', name: 'billing.total_amount', title: '{{ __('Bill') }}', visible: false, searchable: false },
                    { data: 'billing.paid_amount', name: 'billing.paid_amount', title: '{{ __('Paid') }}', visible: false, searchable: false },
                    { data: 'billing.due_amount', name: 'billing.due_amount', title: '{{ __('Due') }}', visible: false, searchable: false }
                ],
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();
                    var intVal = function (i) {
                        if (typeof i === 'string') return i.replace(/[\$,]/g, '') * 1;
                        if (typeof i === 'number') return i;
                        return 0;
                    };

                    var fields = {
                        rent: 14, prev_due: 15, add_charge: 16, vat: 17, disc: 18, adv: 19, 
                        bill: 20, paid: 21, due: 22
                    };

                    var pageTotals = {}, grandTotals = {};

                    Object.keys(fields).forEach(function(key) {
                        var colIdx = fields[key];
                        var col = api.column(colIdx);
                        if (!col || !col.data) {
                            pageTotals[key] = 0;
                            grandTotals[key] = 0;
                            return;
                        }
                        // Page Total
                        pageTotals[key] = api.column(colIdx, { page: 'current' }).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                        // Grand Total
                        grandTotals[key] = api.column(colIdx).data().reduce(function(a, b) { return intVal(a) + intVal(b); }, 0);
                    });

                    // Update UI safely (Matching Table Column Design)
                    var breakdownStyle = 'style="font-size: 0.7rem; line-height: 1.4;"';
                    
                    if ($('#page_total_3').length) {
                        $('#page_total_3').html(
                            '<div class="text-muted" ' + breakdownStyle + '>' +
                            '<div><i class="bi bi-calendar3 me-1"></i>{{ __('Rent:') }} <span class="text-dark fw-bold">' + pageTotals.rent.toFixed(2) + '</span></div>' +
                            '<div><i class="bi bi-exclamation-triangle me-1"></i>{{ __('P.Due:') }} <span class="text-dark fw-bold">' + pageTotals.prev_due.toFixed(2) + '</span></div>' +
                            '<div><i class="bi bi-plus-circle me-1"></i>{{ __('Add:') }} <span class="text-dark fw-bold">' + pageTotals.add_charge.toFixed(2) + '</span> | <i class="bi bi-percent me-1"></i>{{ __('Vat:') }} <span class="text-dark fw-bold">' + pageTotals.vat.toFixed(0) + '</span></div>' +
                            '<div><i class="bi bi-tag me-1"></i>{{ __('Disc:') }} <span class="text-danger fw-bold">' + pageTotals.disc.toFixed(2) + '</span> | <i class="bi bi-wallet-fill me-1"></i>{{ __('Adv:') }} <span class="text-success fw-bold">' + pageTotals.adv.toFixed(2) + '</span></div>' +
                            '</div>'
                        );
                    }

                    if ($('#page_total_5').length) {
                        $('#page_total_5').html(
                            '<div class="billing-card small shadow-none border-0 text-start bg-transparent p-0">' +
                            '<div class="d-flex justify-content-between text-muted"><span>{{ __('Bill:') }}</span> <span class="fw-bold text-primary">' + pageTotals.bill.toFixed(2) + '</span></div>' +
                            '<div class="d-flex justify-content-between text-muted"><span>{{ __('Paid:') }}</span> <span class="fw-bold text-success">' + pageTotals.paid.toFixed(2) + '</span></div>' +
                            '<hr class="my-1">' +
                            '<div class="d-flex justify-content-between"><span>{{ __('Due:') }}</span> <span class="fw-bold text-danger">' + pageTotals.due.toFixed(2) + '</span></div>' +
                            '</div>'
                        );
                    }

                    if ($('#full_total_3').length) {
                        $('#full_total_3').html(
                            '<div class="text-muted" ' + breakdownStyle + '>' +
                            '<div><i class="bi bi-calendar3 me-1 text-primary"></i>{{ __('Rent:') }} <span class="text-primary fw-bold">' + grandTotals.rent.toFixed(2) + '</span></div>' +
                            '<div><i class="bi bi-exclamation-triangle me-1 text-primary"></i>{{ __('P.Due:') }} <span class="text-primary fw-bold">' + grandTotals.prev_due.toFixed(2) + '</span></div>' +
                            '<div><i class="bi bi-plus-circle me-1 text-primary"></i>{{ __('Add:') }} <span class="text-primary fw-bold">' + grandTotals.add_charge.toFixed(2) + '</span> | <i class="bi bi-percent me-1 text-primary"></i>{{ __('Vat:') }} <span class="text-primary fw-bold">' + grandTotals.vat.toFixed(0) + '</span></div>' +
                            '<div><i class="bi bi-tag me-1 text-primary"></i>{{ __('Disc:') }} <span class="text-primary fw-bold">' + grandTotals.disc.toFixed(2) + '</span> | <i class="bi bi-wallet-fill me-1 text-primary"></i>{{ __('Adv:') }} <span class="text-primary fw-bold">' + grandTotals.adv.toFixed(2) + '</span></div>' +
                            '</div>'
                        );
                    }

                    if ($('#full_total_5').length) {
                        $('#full_total_5').html(
                            '<div class="billing-card small shadow-none border-0 text-start bg-transparent p-0">' +
                            '<div class="d-flex justify-content-between text-primary"><span>{{ __('Bill:') }}</span> <span class="fw-bold">' + grandTotals.bill.toFixed(2) + '</span></div>' +
                            '<div class="d-flex justify-content-between text-primary"><span>{{ __('Paid:') }}</span> <span class="fw-bold">' + grandTotals.paid.toFixed(2) + '</span></div>' +
                            '<hr class="my-1 border-primary opacity-50">' +
                            '<div class="d-flex justify-content-between text-primary"><span>{{ __('Due:') }}</span> <span class="fw-bold">' + grandTotals.due.toFixed(2) + '</span></div>' +
                            '</div>'
                        );
                    }

                    // Populate raw cells for Export/Print clarity
                    $('#page_total_20').text(pageTotals.bill.toFixed(2));
                    $('#page_total_21').text(pageTotals.paid.toFixed(2));
                    $('#page_total_22').text(pageTotals.due.toFixed(2));

                    $('#full_total_20').text(grandTotals.bill.toFixed(2));
                    $('#full_total_21').text(grandTotals.paid.toFixed(2));
                    $('#full_total_22').text(grandTotals.due.toFixed(2));
                }
            });

            function updateCustomerCount() {
                var count = table.page.info().recordsTotal;
                $('.btn-check + label span').text(''); // Clear all counts
                
                var checkedId = $('input[name="collection"]:checked').attr('id');
                if (checkedId) {
                    $('#' + checkedId + ' + label span').text('('+count+')');
                }
            }

            table.on('draw', function () { updateCustomerCount(); });
            
            function resetTableState() {
                table.search('').columns().search('');
                $('.dt-search input').val('');
            }

            $('input[name="collection"]').on('change', function() { 
                resetTableState();
                table.clear().draw(); 
                table.ajax.reload(null, true); 
            });

            $('#router_filter').on('change', function() { 
                resetTableState();
                table.clear().draw();
                table.ajax.reload(null, true); 
            });

            $('#reset_table').on('click', function() {
                $('#router_filter').val('');
                $('#all_active_list').prop('checked', true);
                resetTableState();
                table.clear().draw();
                table.ajax.reload(null, true);
            });

            window.confirmDeleteCustomer = function(encryptedId) {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __("You won't be able to revert this!") }}",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "{{ __('Yes, delete it!') }}"
                }).then((result) => {
                    if (result.isConfirmed) { Livewire.dispatch('delete-customer', { id: encryptedId }); }
                });
            };

            Livewire.on('customer-action-done', () => { table.ajax.reload(null, false); });
        });
    </script>
@endpush
