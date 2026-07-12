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

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 text-dark font-medium"><i class="bi bi-info-circle text-primary mr-2"></i> {{ __('Data Import Instructions') }}</h5>
            </div>
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-3" id="importInstructionsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="customer-tab" data-bs-toggle="tab" data-bs-target="#customer-instruction" type="button" role="tab" aria-controls="customer-instruction" aria-selected="true">{{ __('Customer Profiles') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="collection-tab" data-bs-toggle="tab" data-bs-target="#collection-instruction" type="button" role="tab" aria-controls="collection-instruction" aria-selected="false">{{ __('Collections') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bill-tab" data-bs-toggle="tab" data-bs-target="#bill-instruction" type="button" role="tab" aria-controls="bill-instruction" aria-selected="false">{{ __('Monthly Bills') }}</button>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- Customer Tab -->
                    <div class="tab-pane fade show active" id="customer-instruction" role="tabpanel" aria-labelledby="customer-tab">
                        <p class="text-secondary mb-2">{{ __('First, add and sync your Mikrotik router to generate the PPPoE secret records in the system. Then upload an Excel/CSV file with the following column headers to update customer profiles:') }}</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('Excel Column Header (Any of these)') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>username</code> / <code>pppoe_username</code> / <code>pppoe_user</code> / <code>user</code></td>
                                        <td>{{ __('PPPoE Secret Username on Mikrotik (Used to map to the synced user)') }}</td>
                                        <td><span class="badge bg-danger">{{ __('Required') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>customer_unique_id</code> / <code>customer_id</code> / <code>unique_id</code> / <code>id</code></td>
                                        <td>{{ __('Optional: Customer Unique ID. If provided, the system will update the ID in all related tables to match this old ID.') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>name</code> / <code>customer_name</code></td>
                                        <td>{{ __('Full Name of the Customer') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>address</code> / <code>customer_address</code> / <code>location</code></td>
                                        <td>{{ __('Physical Address of the Customer') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>phone</code> / <code>mobile</code> / <code>mobile_no</code> / <code>contact</code></td>
                                        <td>{{ __('Primary Phone Number') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>alternative_phone</code> / <code>alternative_mobile</code> / <code>alt_phone</code></td>
                                        <td>{{ __('Alternative Phone Number') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Collection Tab -->
                    <div class="tab-pane fade" id="collection-instruction" role="tabpanel" aria-labelledby="collection-tab">
                        <p class="text-secondary mb-2">{{ __('Upload an Excel/CSV file to record bill collections. The system will match payments using the customer unique ID or PPPoE username:') }}</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('Excel Column Header (Any of these)') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>customer_id</code> / <code>customer_unique_id</code> / <code>username</code> / <code>pppoe_username</code></td>
                                        <td>{{ __('Identifies the customer (matches either Customer Unique ID or Mikrotik PPPoE username)') }}</td>
                                        <td><span class="badge bg-danger">{{ __('Required') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>amount</code> / <code>collection_amount</code> / <code>paid</code></td>
                                        <td>{{ __('The payment amount collected (must be greater than 0)') }}</td>
                                        <td><span class="badge bg-danger">{{ __('Required') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>date</code> / <code>collection_date</code></td>
                                        <td>{{ __('Date of payment (defaults to current date if missing or invalid)') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>payment_method</code> / <code>method</code></td>
                                        <td>{{ __('e.g., Cash, Bkash, Nagad, Rocket, Bank (defaults to Cash)') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>bill_month</code> / <code>month</code></td>
                                        <td>{{ __('The bill month billing cycle (format: YYYY-MM, defaults to current month)') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>transaction_id</code> / <code>txid</code></td>
                                        <td>{{ __('MFS or Bank Transaction Reference ID') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Month Bill Tab -->
                    <div class="tab-pane fade" id="bill-instruction" role="tabpanel" aria-labelledby="bill-tab">
                        <p class="text-secondary mb-2">{{ __('Upload an Excel/CSV file to generate monthly rent invoices. The system will match billing profiles using the customer unique ID or PPPoE username:') }}</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('Excel Column Header (Any of these)') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>customer_id</code> / <code>customer_unique_id</code> / <code>username</code> / <code>pppoe_username</code></td>
                                        <td>{{ __('Identifies the customer (matches either Customer Unique ID or Mikrotik PPPoE username)') }}</td>
                                        <td><span class="badge bg-danger">{{ __('Required') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>rent</code> / <code>monthly_rent</code> / <code>bill_amount</code></td>
                                        <td>{{ __('Monthly package price (defaults to the customer\'s active package price if left blank)') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>date</code> / <code>summary_date</code></td>
                                        <td>{{ __('Invoice generation date (defaults to current date if missing or invalid)') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><code>additional_charge</code> / <code>discount</code> / <code>vat</code> / <code>previous_due</code></td>
                                        <td>{{ __('Numerical adjustments to the invoice total (default to 0)') }}</td>
                                        <td><span class="badge bg-secondary">{{ __('Optional') }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
