<x-app-layout>
    <div class="container-fluid">
        <div class="row p-3">
            <div class="col-md-12">
                <input type="radio" class="btn-check" name="collection" id="all_list" autocomplete="off" checked>
                <label class="btn btn-outline-success" for="all_list">{{ __('All List') }} <span class="text-warning"></span></label>

                <input type="radio" class="btn-check" name="collection" id="collection_list" autocomplete="off">
                <label class="btn btn-outline-success" for="collection_list">{{ __('Collection List') }} <span class="text-warning"></span></label>

                <input type="radio" class="btn-check" name="collection" id="without_collection_list" autocomplete="off">
                <label class="btn btn-outline-success" for="without_collection_list">{{ __('Without Collection List') }} <span class="text-warning"></span></label>

                <input type="radio" class="btn-check" name="collection" id="pending_customer" autocomplete="off">
                <label class="btn btn-outline-success" for="pending_customer">{{ __('Pending Customer') }} <span class="text-warning"></span></label>

                <input type="radio" class="btn-check" name="collection" id="disable_customer" autocomplete="off">
                <label class="btn btn-outline-success" for="disable_customer">{{ __('Disable Customer') }} <span class="text-warning"></span></label>

                <input type="radio" class="btn-check" name="collection" id="free_customer" autocomplete="off">
                <label class="btn btn-outline-success" for="free_customer">{{ __('Free Customer') }} <span class="text-warning"></span></label>

                <input type="radio" class="btn-check" name="collection" id="inactive_customer" autocomplete="off">
                <label class="btn btn-outline-success" for="inactive_customer">{{ __('Inactive Customer') }} <span class="text-warning"></span></label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table table table-striped table-hover display table-bordered">
                <thead class="text-white text-center">
                    <tr>
                        <th class="text-center">{{ __('SL') }}</th>
                        <th class="text-center">{{ __('ID') }}</th>
                        <th class="text-center">{{ __('Name') }}</th>
                        <th class="text-center">{{ __('Address') }}</th>
                        <th class="text-center">{{ __('Mobile') }}</th>
                        <th class="text-center">{{ __('IP/Username') }}</th>
                        <th class="text-center">{{ __('Monthly Rent') }}</th>
                        <th class="text-center">{{ __('Previous Due') }}</th>
                        <th class="text-center">{{ __('Discount') }}</th>
                        <th class="text-center">{{ __('Advance') }}</th>
                        <th class="text-center">{{ __('Add. Charge') }}</th>
                        <th class="text-center">{{ __('Vat (%)') }}</th>
                        <th class="text-center">{{ __('Bill Amount') }}</th>
                        <th class="text-center">{{ __('Collection Amount') }}</th>
                        <th class="text-center">{{ __('Total Due') }}</th>
                        <th class="text-center">{{ __('Action') }}</th>
                        <th class="text-center">{{ __('Disable Date') }}</th>
                        <th class="text-center">{{ __('Disable Details') }}</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" style="text-align:right">{{ __('Total:') }}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


    <div class="modal fade modal-lg" id="edit-bill-modal" tabindex="-1"  data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">{{ __('Update Bill') }} (<span id="edit-bill-name"></span>)</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bill-form" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-5">
                                {{ __('Unique ID:') }} <span id="edit-bill-unique-id"></span>
                                <br>
                                {{ __('Customer Name:') }} <span id="edit-bill-customer-name"></span>
                                <br>
                                {{ __('IP/Username:') }} <span id="edit-bill-ip-username"></span>
                                <br>
                                {{ __('Auto Disable Date:') }} <span id="edit-bill-auto-disable-date"></span>
                                <br>
                                <br>
                                <br>

                                <span class="text-danger" id="error-message"></span>
                            </div>
                            <div class="col-7 border-start">
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Monthly Rent :') }}</span>
                                    <input type="number" min="0" name="monthly_rent" class="form-control" id="monthly_rent" required>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Additional Charge :') }}</span>
                                    <input type="number" min="0" name="additional_charge" class="form-control" id="additional_charge" required>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Vat (%) :') }}</span>
                                    <input type="number" min="0" name="vat" class="form-control" id="vat" required>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Sub Total :') }}</span>
                                    <input type="text" class="form-control" id="sub_total_amount" disabled readonly>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Previous Due :') }}</span>
                                    <input type="number" min="0" name="previous_due" class="form-control" id="previous_due" disabled readonly>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Advance :') }}</span>
                                    <input type="number" min="0" name="advance" class="form-control" id="advance" required>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Discount :') }}</span>
                                    <input type="number" min="0" name="discount" class="form-control" id="discount" required>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Grand Total :') }}</span>
                                    <input type="text" name="grand_total" class="form-control" id="total_amount" disabled readonly>
                                    <span class="input-group-text">{{ __('Tk') }}</span>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text ps-5 w-50">{{ __('Auto Disable :') }}</span>
                                    <div class="input-group-text form-check form-switch form-check-reverse w-50 text-center">
                                        <input class="form-check-input" name="auto_disable" type="checkbox" id="auto_disable">
                                    </div>
                                </div>
                                <input type="hidden" name="due_amount" id="due_amount">
                                <input type="hidden" name="paid_amount" id="paid_amount">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('styles')
    <style>
        .data-table {
            font-size: .9em !important;
        }
        .data-table td, .data-table th {
            padding: .25rem !important;
        }
        .dt-container .data-table.dataTable td, .dt-container .data-table.dataTable th {
            white-space: normal;
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
                // Destroy Datatable and re-initialize it
            if ($.fn.dataTable.isDataTable('.data-table')) {
                $('.data-table').DataTable().destroy();
            }
            /*
            * Start Datatable
            * Data table Apply the search With select search
            */
            // DataTable.datetime('M/D/YYYY');
            var table = $('.data-table').DataTable({
                processing: true,
                // autoWidth: false,
                // serverSide: true,
                // responsive: true,
                pagingType: 'full_numbers',
                pageLength: 10,
                lengthChange: true,
                searchable: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'All']
                ],
                select: true,
                dom: 'Bfrtip',
                buttons: ['pageLength',
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-journal-arrow-down"></i> (Excel)', // For Bootstrap Icons
                        titleAttr: 'Export to Excel',
                        exportOptions: {
                            columns: [0,':visible']
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-filetype-pdf"></i> (PDF)', // For Bootstrap Icons
                        titleAttr: 'Export to PDF',
                        orientation: 'landscape',
                        pageSize: 'LEGAL',
                        exportOptions: {
                            columns: [0,':visible']
                        },
                        customize: function (doc) {
                            var col = doc.content[1].table.body;
                            for (i = 1; i < col.length; i++) {
                                col[i][0]["text"] = i;
                            }
                        },
                    },
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-journal-arrow-down"></i> (Selected Download)', // For Bootstrap Icons
                        exportOptions: {
                            modifier: {
                                selected: true
                            }
                        },
                    },{
                        extend: 'print',
                        text: '<i class="bi bi-printer-fill"></i> (Print)', // Bootstrap Icons
                        exportOptions: {
                            columns: [0, ':visible']
                        },
                        customize: function (win) {
                            // Add numbering for each row in the printed table
                            $(win.document.body).find('table tbody tr').each(function (index) {
                                $(this).find('td:first').text(index + 1);
                            });
                            // Center the title in the print preview
                            $(win.document.body).find('h1').css('text-align', 'center');
                        }
                    },
                    'colvis'
                ],
                ajax: {
                    url: "{{ route('customers.index') }}", // Your server-side URL
                    data: function(d) {
                        // Send additional data to the server based on the selected filter
                        if ($('#all_list').is(':checked')) {
                            d.filter = 'all';
                        } else if ($('#without_collection_list').is(':checked')) {
                            d.filter = 'without_collection';
                        } else if ($('#collection_list').is(':checked')) {
                            d.filter = 'collection';
                        } else if ($('#pending_customer').is(':checked')) {
                            d.filter = 'pending';
                        }else if ($('#disable_customer').is(':checked')) {
                            d.filter = 'disable';
                        }else if ($('#free_customer').is(':checked')) {
                            d.filter = 'free';
                        }else if ($('#inactive_customer').is(':checked')) {
                            d.filter = 'inactive';
                        }
                    }
                },
                columns: [
                    // { data: 'DT_RowIndex', name: 'DT_RowIndex', searchable: false, className: 'text-center' },
                    { data: null, defaultContent: '', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'customer_unique_id', name: 'customer_unique_id', className: 'select-search text-start' },
                    { data: 'customer_name', name: 'customer_name', className: 'select-search text-start' },
                    { data: 'customers_address', name: 'customers_address', className: 'select-search text-start' },
                    { data: 'mobile', name: 'mobile', className: 'select-search text-start' },

                    { data: 'ppp_user.username', name: 'ppp_user.username', className: 'select-search text-start' },
                    { data: 'billing.monthly_rent', name: 'billing.monthly_rent', className: 'select-search text-start' },
                    { data: 'billing.previous_due', name: 'billing.previous_due', className: 'select-search text-start' },
                    { data: 'billing.discount', name: 'billing.discount', className: 'select-search text-start' },
                    { data: 'billing.advance', name: 'billing.advance', className: 'select-search text-start' },
                    { data: 'billing.additional_charge', name: 'billing.additional_charge', className: 'select-search text-start' },
                    { data: 'billing.vat', name: 'billing.vat', className: 'select-search text-start' },
                    { data: 'billing.total_amount', name: 'billing.total_amount', className: 'select-search text-start' },
                    { data: 'billing.paid_amount', name: 'billing.paid_amount', className: 'select-search text-start' },
                    { data: 'billing.due_amount', name: 'billing.due_amount', className: 'select-search text-start' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'billing.auto_disable_date', name: 'billing.auto_disable_date', className: 'select-search text-start', render: function (data, type, row) {
                        const date = new Date(data);
                        const options = { day: '2-digit', month: 'short', year: 'numeric' };
                        return date.toLocaleDateString('en-GB', options); // "01 Jan 2025"
                    }, },
                    { data: 'disable_details', name: 'disable_details',  className: 'select-search text-start' }
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column(0, { page: 'current' }).nodes().each(function(cell, i) {
                        var pageInfo = api.page.info();
                        var serial = pageInfo.start + i + 1;
                        $(cell).html(serial);
                    });
                },
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to remove formatting and convert to integer
                    var intVal = function (i) {
                        return typeof i === 'string'
                            ? i.replace(/[\$,]/g, '') * 1
                            : typeof i === 'number'
                            ? i
                            : 0;
                    };

                    // Loop through each column index (6 to 15)
                    for (var colIndex = 6; colIndex <= 14; colIndex++) {
                        // Total over all pages for this column
                        var total = api
                            .column(colIndex)
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Total over the current page for this column
                        var pageTotal = api
                            .column(colIndex, { page: 'current' })
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Update the footer for the current column
                        $(api.column(colIndex).footer()).html(
                            + pageTotal + ' (total=' + total + ')'
                        );
                    }
                }
            });

            function updateCustomerCount() {
                var count = table.page.info().recordsDisplay; // Get the filtered row count
                ($('#all_list').is(':checked')) ? $('#all_list + label span').text('('+count+')') : $('#all_list + label span').text('');
                ($('#without_collection_list').is(':checked')) ? $('#without_collection_list + label span').text('('+count+')') : $('#without_collection_list + label span').text('');
                ($('#collection_list').is(':checked')) ? $('#collection_list + label span').text('('+count+')') : $('#collection_list + label span').text('');
                ($('#pending_customer').is(':checked')) ? $('#pending_customer + label span').text('('+count+')') : $('#pending_customer + label span').text('');
                ($('#disable_customer').is(':checked')) ? $('#disable_customer + label span').text('('+count+')') : $('#disable_customer + label span').text('');
                ($('#free_customer').is(':checked')) ? $('#free_customer + label span').text('('+count+')') : $('#free_customer + label span').text('');
                ($('#inactive_customer').is(':checked')) ? $('#inactive_customer + label span').text('('+count+')') : $('#inactive_customer + label span').text('');
            }

            // Call the function initially to set the count on load
            // updateCustomerCount();

            // Update the count whenever the table is redrawn
            table.on('draw', function () {
                updateCustomerCount();
            });

            $('input[name="collection"]').on('change', function() {
                table.ajax.reload(); // Reload the table with new data based on the selected filter
            });

            var id = '';

            $('body').on('click', '#delete-customer', function() {
                id = $(this).data('id');
                Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('You won\'t be able to revert this!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{ __('Yes, delete it!') }}"
                }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('customers.destroy', ':id') }}".replace(':id', id),
                        method: 'DELETE',
                        success: function(response) {
                            Swal.fire({
                                title: "{{ __('Deleted!') }}",
                                text: response.message,
                                icon: "success"
                            });
                            table.ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            var response = JSON.parse(xhr.responseText);
                            var errorMessage = response.message;
                            Swal.fire({
                                title: "{{ __('Error!') }}",
                                text: errorMessage,
                                icon: "error"
                            });

                        }
                    });
                }
                });
            });

            $('body').on('click', '#enable-customer', function() {
                id = $(this).data('id');
                Swal.fire({
                title: "{{ __('Are you sure?') }}",
                text: "{{ __('You won\'t be able to revert this!') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{ __('Yes, Enable it!') }}"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('customers.enable', ':id') }}".replace(':id', id),
                            method: 'POST',
                            success: function(response) {
                                Swal.fire({
                                    title: "{{ __('Enabled!') }}",
                                    text: response.message,
                                    icon: "success"
                                });
                                table.ajax.reload();
                            },
                            error: function(xhr, status, error) {
                                var response = JSON.parse(xhr.responseText);
                                var errorMessage = response.message;
                                Swal.fire({
                                    title: "{{ __('Error!') }}",
                                    text: errorMessage,
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            });

            $('body').on('click', '#edit-bill', function() {
                id = $(this).data('id');
                $.ajax({
                    url: "{{ route('customers.show', ':id') }}".replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        // console.log(response);
                        $('#edit-bill-name').html(response.customer_name);
                        $('#edit-bill-unique-id').html(response.customer_unique_id);
                        $('#edit-bill-customer-name').html(response.customer_name);
                        $('#edit-bill-ip-username').html(response.username);
                        $('#edit-bill-auto-disable-date').html(response.auto_disable_date);
                        $('#monthly_rent').val(response.monthly_rent);
                        $('#previous_due').val(response.previous_due);
                        $('#discount').val(response.discount);
                        $('#advance').val(response.advance);
                        function sum(...values) {
                            return values.reduce((acc, value) => acc + (parseFloat(value) || 0), 0);
                        }
                        var sub_total = sum(response.monthly_rent, response.additional_charge);

                        $('#sub_total_amount').val(sub_total);
                        $('#additional_charge').val(response.additional_charge);
                        $('#vat').val(response.vat);
                        $('#total_amount').val(response.total_amount);
                        if(response.auto_disable == 1) {
                            $('#auto_disable').prop('checked', true);
                        }else{
                            $('#auto_disable').prop('checked', false);
                        }
                        $('#due_amount').val(response.due_amount);
                        $('#paid_amount').val(response.paid_amount);

                        function calculateTotal() {
                            // Get values and parse them as numbers (or default to 0 if empty)
                            var monthlyRent = parseFloat($('#monthly_rent').val()) || 0.0;
                            var previousDue = parseFloat($('#previous_due').val()) || 0.0;
                            var additionalCharge = parseFloat($('#additional_charge').val()) || 0.0;
                            var discount = parseFloat($('#discount').val()) || 0.0;
                            var advance = parseFloat($('#advance').val()) || 0.0;
                            var vat = parseFloat($('#vat').val()) || 0.0;
                            var due = parseFloat($('#due_amount').val()) || 0.0;
                            var paid = parseFloat($('#paid_amount').val()) || 0.0;
                            // Calculate subtotal
                            var subtotal = monthlyRent + previousDue + additionalCharge;
                            // Calculate VAT
                            var vatAmount = (vat / 100) * subtotal;
                            var sub_total_amount = subtotal + vatAmount;
                            // Calculate total amount
                            var total_amount = sub_total_amount - (discount + advance);
                            // Calculate due amount
                            var dueAmount = total_amount - paid;
                            // Set the total amount to the relevant fields
                            $('#sub_total_amount').val(sub_total_amount.toFixed(2)); // Two decimal places
                            $('#total_amount').val(total_amount.toFixed(2)); // Two decimal places
                            $('#due_amount').val(dueAmount.toFixed(2)); // Update due amount here
                        }

                        // Trigger calculation on change of any input field
                        $('#monthly_rent, #previous_due, #additional_charge, #discount, #advance, #vat, #due_amount').on('input', calculateTotal);

                        $('#bill-form').on('submit', function(e) {
                            e.preventDefault();
                            console.log(id);
                            $.ajax({
                                url: "{{ route('customers.update', ':id') }}".replace(':id', id),
                                method: 'PATCH',
                                data: {
                                    'monthly_rent': $('#monthly_rent').val(),
                                    'additional_charge': $('#additional_charge').val(),
                                    'discount': $('#discount').val(),
                                    'advance': $('#advance').val(),
                                    'vat': $('#vat').val(),
                                    'total_amount': $('#total_amount').val(),
                                    'due_amount': $('#due_amount').val(),
                                    'auto_disable': $('#auto_disable').is(':checked') ? 1 : 0
                                },
                                success: function(response) {
                                    $('#error-message').html(response.message).fadeIn().fadeOut(5000);
                                    table.ajax.reload();
                                },
                                error: function(xhr) {
                                    $('#error-message').html(xhr.responseJSON.message).fadeIn().fadeOut(5000);
                                }
                            });
                        });
                    }
                });
            });

            $('.close').on('click', function() {
                $('#edit-bill-name').text('');
                $('#edit-bill-unique-id').text('');
                $('#edit-bill-customer-name').text('');
                $('#edit-bill-ip-username').text('');
                $('#monthly_rent').val('');
                $('#previous_due').val('');
                $('#discount').val('');
                $('#advance').val('');
                $('#sub_total_amount').val('');
                $('#additional_charge').val('');
                $('#vat').val('');
                $('#total_amount').val('');
                $('#due_amount').val('');
                $('#paid_amount').val('');
                $('#error-message').hide();
            });
        });
    </script>
@endpush
</x-app-layout>


{{-- <div class="container">
    <div class="card">
        <div class="card-header">Manage Users</div>
        <div class="card-body">
            {{ $dataTable->table() }}
        </div>
    </div>
</div>


@push('scripts')
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endpush --}}
