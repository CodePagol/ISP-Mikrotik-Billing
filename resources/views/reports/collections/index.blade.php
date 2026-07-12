<x-app-layout>
    <x-slot name="header">
        {{ __('Collections') }}
    </x-slot>
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="row p-3">
                    <div class="col">
                        <form class="row g-3">
                            <div class="col-auto">
                                <input type="date" name="from-date" class="form-control" id="from-date">
                            </div>
                            <div class="col-auto h5 align-item-center">{{ __('To') }}</div>
                            <div class="col-auto">
                                <input type="date" name="to-date" class="form-control" id="to-date">
                            </div>
                            <div class="col-auto">
                                <select name="collector" id="collector" class="form-control">
                                    <option value="">{{ __('Select Collector') }}</option>
                                    @foreach ($collectors as $collector)
                                        <option value="{{ $collector->email }}">{{ $collector->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" id="report-submit" class="btn btn-primary mb-3">{{ __('Confirm') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Id') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th>{{ __('IP/Username') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Collected By') }}</th>
                                {{-- <th>Expire Date</th> --}}
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('livewire:navigated', function () {
              // Destroy Datatable and re-initialize it
                if ($.fn.dataTable.isDataTable('.data-table')) {
                    $('.data-table').DataTable().destroy();
                }
                var reportTable; // For storing the DataTable instance
                $('#report-submit').on('click', function (e) {
                    e.preventDefault();
                    // if the table already exists, destroy it
                    if ($.fn.DataTable.isDataTable('.data-table')) {
                        reportTable.ajax.reload();
                    } else {
                        // 1st datatable load
                        reportTable = $('.data-table').DataTable({
                            processing: true,
                            autoWidth: true,
                            responsive:true,
                            lengthMenu: [
                                [10, 25, 50, 100, -1],
                                [10, 25, 50, 100, 'All']
                            ],
                            select: true,
                            dom: 'Bfrtip',
                            buttons: [
                                'copy', 'csv', 'excel', 'pdf', 'print'
                            ],
                            ajax: {
                                url: "{{ route('collection-report.index') }}",
                                data: function (d) {
                                    d.fromDate = $('#from-date').val();
                                    d.toDate = $('#to-date').val();
                                    d.collector = $('#collector').val();
                                }
                            },
                            columns: [
                                { data: 'customer_collection_unique_id', name: 'customer_collection_unique_id' },
                                { data: 'collection_date', name: 'collection_date',
                                    render: function (data, type, row) {
                                        const date = new Date(data);
                                        const options = { day: '2-digit', month: 'short', year: 'numeric' };
                                        return date.toLocaleDateString('en-GB', options); // "01 Jan 2025"
                                    },
                                },
                                { data: 'customer_name', name: 'customer_name' },
                                { data: 'customers_address', name: 'customers_address' },
                                { data: 'ppp_secret', name: 'ppp_secret' },
                                { data: 'collection_amount', name: 'collection_amount' },
                                { data: 'collected_by', name: 'collected_by' },
                                // { data: 'expire_date', name: 'expire_date' }
                            ],
                        });
                    }
                });
            });

        </script>

    @endpush
</x-app-layout>
