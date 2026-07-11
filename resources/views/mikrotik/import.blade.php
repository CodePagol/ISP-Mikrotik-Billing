<x-app-layout>
    <div class="container mt-5">
        <h2>{{ __('Import and Preview Excel Data') }}</h2>
        <div class="float-end">
            <a href="{{ url('/') }}" class="btn btn-primary btn-sm">&larr; {{ __('Back') }}</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- @if($errors->has('duplicates'))
            <div class="alert alert-danger">
                <strong>Warning!</strong> Some records were duplicates and were not imported.
                <ul>
                    @foreach(session('duplicates', []) as $duplicate)
                        <li>SSN: {{ $duplicate['user'] }}</li>
                    @endforeach
                </ul>
                <p>Total Skipped Rows: {{ session('skippedRows') }}</p>
            </div>
        @endif --}}

        <form id="importForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-10">
                    <div class="form-group">
                        <input type="file" name="file" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-2">
                    {{-- <button type="submit" onclick="setFormAction('{{ route('import.form') }}');" class="btn btn-primary">Customer</button> --}}
                    <button type="submit" onclick="setFormAction('{{ route('collection.form') }}');" class="btn btn-info">{{ __('Collection') }}</button>
                    <button type="submit" onclick="setFormAction('{{ route('monthly.bill.form') }}');" class="btn btn-warning">{{ __('Month Bill') }}</button>
                    <button type="submit" onclick="setFormAction('{{ route('import') }}');" class="btn btn-success">{{ __('Customer') }}</button>
                </div>
            </div>
        </form>

        {{-- @if (isset($data) && !empty($data))
            <div class="row mt-4">
                <div class="col-md-10">
                    <h3>Preview Data</h3>
                </div>
            </div>
            <table class="table table-bordered mt-3 data-table">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Zip</th>
                        <th>Country</th>
                        <th>SSN</th>
                        <th>DOB</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr>
                            <td>{{ $row['first_name'] ?? 'N/A' }}</td>
                            <td>{{ $row['last_name'] ?? 'N/A' }}</td>
                            <td>{{ $row['address'] ?? 'N/A' }}</td>
                            <td>{{ $row['city'] ?? 'N/A' }}</td>
                            <td>{{ $row['state'] ?? 'N/A' }}</td>
                            <td>{{ $row['zip'] ?? 'N/A' }}</td>
                            <td>{{ $row['country'] ?? 'N/A' }}</td>
                            <td>{{ $row['ssn'] ?? 'N/A' }}</td>
                            <td>{{ excelDateToDate($row['dob'] ?? 'N/A') }}</td>
                            <td>{{ $row['email'] ?? 'N/A' }}</td>
                            <td>{{ $row['phone'] ?? 'N/A' }}</td>
                            <td>{{ $row['price'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif(isset($data))
            <p>No data found in the file.</p>
        @else
            <h3 class="mt-5">Preview Data</h3>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Zip</th>
                        <th>Country</th>
                        <th>SSN</th>
                        <th>DOB</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Price</th>
                    </tr>
                </thead>
            </table>
        @endif --}}
    </div>

    @push('scripts')
        <script>
            function setFormAction(action) {
                document.getElementById('importForm').action = action;
            }

            document.addEventListener('DOMContentLoaded', function() {
                $('.data-table').DataTable({
                    responsive: true
                });
            });
        </script>
    @endpush
</x-app-layout>
