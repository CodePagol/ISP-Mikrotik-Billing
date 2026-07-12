<x-app-layout>
    <div class="container my-4">
        <div class="d-flex justify-content-between mb-4 mt-2">
            <h3>Administrative SMS Setup</h3>
            <button type="button" class="btn btn-primary shadow-sm" onclick="openCreateModal()">+ Register Gateway</button>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Implementation ID</th>
                            <th>API URL</th>
                            <th>Sender Target</th>
                            <th>Priority Base</th>
                            <th>Activation</th>
                            <th>Default Config</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gateways as $gateway)
                            <tr>
                                <td><strong>{{ $gateway['name'] }}</strong></td>
                                <td><code>{{ $gateway['api_url'] }}</code></td>
                                <td>{{ $gateway['sender_id'] }}</td>
                                <td>{{ $gateway['priority'] }}</td>
                                <td class="align-middle">
                                    <form action="{{ route('sms-bridge.toggle', $gateway['id']) }}" method="POST"
                                        class="d-inline-flex align-items-center mb-0" id="toggleForm-{{ $gateway['id'] }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $gateway['name'] }}">

                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                                id="toggle-{{ $gateway['id'] }}"
                                                {{ !empty($gateway['is_active']) ? 'checked' : '' }}
                                                onchange="document.getElementById('toggleForm-{{ $gateway['id'] }}').submit()">
                                        </div>
                                        <label
                                            class="form-check-label ms-1 mb-0 fw-bold {{ !empty($gateway['is_active']) ? 'text-success' : 'text-secondary' }}"
                                            for="toggle-{{ $gateway['id'] }}">
                                            {{ !empty($gateway['is_active']) ? 'ON' : 'OFF' }}
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    @if (!empty($gateway['is_default']))
                                        <span class="badge bg-primary">Priority Node</span>
                                    @endif
                                </td>
                                <td class="align-middle">

                                    @if ($gateway['name'] !== 'log')
                                        <button type="button" class="btn btn-sm btn-info text-white me-2"
                                            data-gateway="{{ json_encode($gateway) }}"
                                            onclick="openEditModal(this)">Edit</button>
                                    @endif



                                    <button type="button" class="btn btn-sm btn-warning text-dark me-2"
                                        data-gateway="{{ json_encode($gateway) }}"
                                        onclick="openTestModal(this)">Test</button>

                                    @if ($gateway['name'] !== 'log')
                                        <button type="button" class="btn btn-sm btn-success text-white me-2"
                                            onclick="checkGatewayBalance('{{ $gateway['id'] }}', '{{ addslashes($gateway['api_balance_url'] ?? '') }}', this)">Balance</button>
                                    @endif

                                    @if ($gateway['name'] !== 'log')
                                        <form action="{{ route('sms-bridge.destroy', $gateway['id']) }}" method="post"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger"
                                                onclick="return confirm('You are deleting a physical node. Confirm validation.')">DELETE</button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary disabled"
                                            title="Log gateway is permanent">Fixed</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center font-bold pb-2 pt-2">JSON map is empty.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gateway Modal -->
    <div class="modal fade" id="gatewayModal" tabindex="-1" aria-labelledby="gatewayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="gatewayForm" action="{{ route('sms-bridge.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="gatewayModalLabel">Add Custom SMS Gateway</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-6 form-group">
                                <label>System Identification ID</label>
                                <select name="name" class="form-select mt-1" required>
                                    <option value="adnsms">AdnSms</option>
                                    <option value="alphasms">AlphaSms</option>
                                    <option value="banglalink">Banglalink</option>
                                    <option value="bulksms">BulkSMSBD</option>
                                    <option value="elitbuzz">ElitBuzz</option>
                                    <option value="esms">eSMS</option>
                                    <option value="grameenphone">Grameenphone</option>
                                    <option value="greenweb">GreenWeb BD</option>
                                    <option value="infobip">Infobip</option>
                                    <option value="mimsms">MimSMS</option>
                                    <option value="robi">Robi</option>
                                    <option value="smsnoc">SMSNOC</option>
                                    <option value="ssl" selected>SSL Wireless</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label id="lbl-api-url">Base API URL</label>
                                <input type="url" name="api_url" class="form-control mt-1" required
                                    placeholder="https://api.gateway.com/v3">
                                <div class="form-text">The root domain API address without endpoints.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 form-group">
                                <label class="text-primary">Send SMS Ext</label>
                                <input type="text" name="api_send_url" class="form-control mt-1"
                                    placeholder="sms/send" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="text-success">Balance Ext <small
                                        class="text-muted fw-normal">(Opt)</small></label>
                                <input type="text" name="api_balance_url" class="form-control mt-1"
                                    placeholder="balance">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="text-info">Profile Ext <small
                                        class="text-muted fw-normal">(Opt)</small></label>
                                <input type="text" name="api_profile_url" class="form-control mt-1"
                                    placeholder="profile">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 form-group">
                                <label id="lbl-api-key">API Key Variable</label>
                                <input type="password" id="api_key_input" name="api_key" class="form-control mt-1"
                                    required onmouseenter="this.type='text'"
                                    onmouseleave="if(document.activeElement !== this) this.type='password'"
                                    onfocus="this.type='text'" onblur="this.type='password'">
                            </div>
                            <div class="col-md-6 form-group">
                                <label id="lbl-api-secret">API Secret Option (Blank allowed)</label>
                                <input type="password" id="api_secret_input" name="api_secret" class="form-control mt-1"
                                    onmouseenter="this.type='text'"
                                    onmouseleave="if(document.activeElement !== this) this.type='password'"
                                    onfocus="this.type='text'" onblur="this.type='password'">
                            </div>
                        </div>

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4 form-group">
                                <label id="lbl-sender-id">Sender Code Base</label>
                                <input type="text" name="sender_id" class="form-control mt-1" required>
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Priority Rank Iteration</label>
                                <input type="number" name="priority" id="gatewayPriority" class="form-control mt-1"
                                    value="0" required>
                            </div>

                            <div class="col-md-4 mt-4 d-flex">
                                <div class="form-check me-4">
                                    <input type="checkbox" name="is_active" id="gatewayActive" class="form-check-input"
                                        value="1" checked>
                                    <label class="form-check-label font-bold">Active Gateway</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="is_default" id="gatewayDefault"
                                        class="form-check-input" value="1">
                                    <label class="form-check-label font-bold">Set as Default</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Save Gateway</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Gateway Modal -->
    <div class="modal fade" id="testModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="testForm" method="POST">
                    @csrf
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Test SMS Gateway <span id="testGatewayName"
                                class="badge bg-dark ms-2"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="fw-bold">Destination Phone</label>
                            <input type="text" name="phone" class="form-control mt-1" placeholder="8801XXXXXXXXX"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="fw-bold">Test Message</label>
                            <textarea name="message" class="form-control mt-1" rows="3" required>This is a test message from SmsBridge setup.</textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning fw-bold">Send Test SMS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Interactive Scripts -->
    <script>
        const storeUrl = "{{ route('sms-bridge.store') }}";
        const updateUrlBase = "{{ route('sms-bridge.update', 'GW_ID') }}";
        const testUrlBase = "{{ route('sms-bridge.test', 'GW_ID') }}";

        let gatewayModal = null;
        let testModal = null;

        const gatewayLabels = {
            adnsms: {
                url: "AdnSms API URL",
                key: "API Key",
                secret: "API Secret",
                sender: "Sender ID"
            },
            alphasms: {
                url: "AlphaSms API URL",
                key: "API Key",
                secret: "API Secret (Not Used)",
                sender: "Sender ID"
            },
            banglalink: {
                url: "Banglalink API URL",
                key: "UserID",
                secret: "Password",
                sender: "Sender"
            },
            bulksms: {
                url: "BulkSMSBD API URL",
                key: "API Key",
                secret: "API Secret (Not Used)",
                sender: "Sender ID"
            },
            elitbuzz: {
                url: "ElitBuzz API URL",
                key: "API Key",
                secret: "API Secret (Not Used)",
                sender: "Sender ID"
            },
            esms: {
                url: "eSMS API URL",
                key: "Token",
                secret: "API Secret (Not Used)",
                sender: "Sender ID"
            },
            grameenphone: {
                url: "Grameenphone API URL",
                key: "Username",
                secret: "Password",
                sender: "CLI"
            },
            greenweb: {
                url: "Greenweb API URL",
                key: "Token",
                secret: "API Secret (Not Used)",
                sender: "Sender ID"
            },
            infobip: {
                url: "Infobip Base URL",
                key: "Username",
                secret: "Password",
                sender: "From (Sender ID)"
            },
            mimsms: {
                url: "MimSMS API URL",
                key: "MIMSMS_USERNAME",
                secret: "MIMSMS_API_KEY",
                sender: "MIMSMS_SENDER_ID"
            },
            robi: {
                url: "Robi API URL",
                key: "Username",
                secret: "Password",
                sender: "Sender ID"
            },
            smsnoc: {
                url: "SMSNOC API URL",
                key: "Bearer Token",
                secret: "API Secret (Not Used)",
                sender: "Sender ID"
            },
            ssl: {
                url: "SSL API URL",
                key: "SSL_TOKEN",
                secret: "SSL_CSMS_ID (Optional)",
                sender: "SSL_SENDER_ID"
            },
            log: {
                url: "Log API URL",
                key: "API Key",
                secret: "API Secret",
                sender: "Sender ID"
            },
            default: {
                url: "API URL",
                key: "API Key",
                secret: "API Secret (Optional)",
                sender: "Sender ID"
            }
        };

        window.updateLabels = function() {
            const gatewaySelect = document.querySelector('select[name="name"]');
            if (!gatewaySelect) return;
            const val = gatewaySelect.value;
            const mapped = gatewayLabels[val] || gatewayLabels.default;

            document.getElementById('lbl-api-url').innerText = mapped.url;
            document.getElementById('lbl-api-key').innerText = mapped.key;
            document.getElementById('lbl-api-secret').innerText = mapped.secret;
            document.getElementById('lbl-sender-id').innerText = mapped.sender;

            // Toggle required status and disable fields for 'log' gateway
            const isLog = val === 'log';
            const fields = ['api_url', 'api_send_url', 'api_balance_url', 'api_profile_url', 'api_key', 'api_secret',
                'sender_id'
            ];

            fields.forEach(function(fieldName) {
                const input = document.querySelector(`input[name="${fieldName}"]`);
                if (input) {
                    input.disabled = isLog;
                    if (isLog) {
                        input.value = '';
                        input.required = false;
                    } else if (fieldName !== 'api_secret' && fieldName !== 'api_balance_url' && fieldName !==
                        'api_profile_url') {
                        input.required = true;
                    } else {
                        input.required = false;
                    }
                }
            });
        };

        function getModalInstance() {
            if (!gatewayModal) {
                // Ensure Bootstrap is loaded before initializing
                if (typeof bootstrap !== 'undefined') {
                    gatewayModal = new bootstrap.Modal(document.getElementById('gatewayModal'));
                } else {
                    console.error("Bootstrap JS is not loaded yet!");
                    alert("Please wait a second for the page to finish loading.");
                }
            }
            return gatewayModal;
        }

        function getTestModalInstance() {
            if (!testModal) {
                if (typeof bootstrap !== 'undefined') {
                    testModal = new bootstrap.Modal(document.getElementById('testModal'));
                }
            }
            return testModal;
        }

        document.addEventListener("DOMContentLoaded", function() {
            const gatewaySelect = document.querySelector('select[name="name"]');
            if (gatewaySelect) {
                gatewaySelect.addEventListener('change', window.updateLabels);
            }
        });

        function openCreateModal() {
            let modal = getModalInstance();
            if (!modal) return;

            document.getElementById('gatewayModalLabel').innerText = "Add Custom SMS Gateway";
            document.getElementById('gatewayForm').action = storeUrl;
            document.getElementById('formMethod').value = "POST";
            document.getElementById('btnSave').innerText = "Save Gateway";

            // Reset Fields
            document.querySelector('select[name="name"]').value = "ssl";
            document.querySelector('input[name="api_url"]').value = "";
            document.querySelector('input[name="api_send_url"]').value = "";
            document.querySelector('input[name="api_balance_url"]').value = "";
            document.querySelector('input[name="api_profile_url"]').value = "";
            document.querySelector('input[name="api_key"]').value = "";
            document.querySelector('input[name="api_secret"]').value = "";
            document.querySelector('input[name="sender_id"]').value = "";
            document.getElementById('gatewayPriority').value = "0";
            document.getElementById('gatewayActive').checked = true;
            document.getElementById('gatewayDefault').checked = false;

            window.updateLabels();
            modal.show();
        }

        function openEditModal(btn) {
            let modal = getModalInstance();
            if (!modal) return;

            const gateway = JSON.parse(btn.getAttribute('data-gateway'));

            document.getElementById('gatewayModalLabel').innerText = "Reconfigure Component Payload";
            document.getElementById('gatewayForm').action = updateUrlBase.replace('GW_ID', gateway.id);
            document.getElementById('formMethod').value = "PUT";
            document.getElementById('btnSave').innerText = "Save Edits Parameter";

            // Fill Fields
            document.querySelector('select[name="name"]').value = gateway.name;
            document.querySelector('input[name="api_url"]').value = gateway.api_url;
            document.querySelector('input[name="api_send_url"]').value = gateway.api_send_url || "";
            document.querySelector('input[name="api_balance_url"]').value = gateway.api_balance_url || "";
            document.querySelector('input[name="api_profile_url"]').value = gateway.api_profile_url || "";
            document.querySelector('input[name="api_key"]').value = gateway.api_key;
            document.querySelector('input[name="api_secret"]').value = gateway.api_secret || "";
            document.querySelector('input[name="sender_id"]').value = gateway.sender_id;
            document.getElementById('gatewayPriority').value = gateway.priority || 0;
            document.getElementById('gatewayActive').checked = (gateway.is_active == 1);
            document.getElementById('gatewayDefault').checked = (gateway.is_default == 1);

            window.updateLabels();
            modal.show();
        }

        function openTestModal(btn) {
            let modal = getTestModalInstance();
            if (!modal) return;

            const gateway = JSON.parse(btn.getAttribute('data-gateway'));

            document.getElementById('testGatewayName').innerText = gateway.name.toUpperCase();
            document.getElementById('testForm').action = testUrlBase.replace('GW_ID', gateway.id);

            modal.show();
        }


        async function checkGatewayBalance(id, balanceExt, btnElement) {
            if (!balanceExt || balanceExt.trim() === '') {
                alert('Balance Ext is empty! First fill up the Balance Ext field before checking.');
                return;
            }

            const originalText = btnElement.innerHTML;
            btnElement.innerHTML =
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            btnElement.disabled = true;

            try {
                const response = await fetch(`/sms-bridge/${id}/balance`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success && data.balance !== null) {
                    alert(`Live Balance for ${data.gateway}:\n${data.balance}`);
                } else {
                    alert(data.error ||
                        'Balance checking is not supported for this gateway or the credentials are invalid.');
                }
            } catch (error) {
                alert('An error occurred while checking the balance.');
                console.error(error);
            } finally {
                btnElement.innerHTML = originalText;
                btnElement.disabled = false;
            }
        }
    </script>
</x-app-layout>
