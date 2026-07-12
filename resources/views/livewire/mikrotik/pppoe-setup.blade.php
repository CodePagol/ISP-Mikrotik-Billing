<div class="zoom-in">
    <x-slot name="header">{{ __('PPPoE Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-router-fill text-primary fs-5"></i>
        <select class="form-select form-select-sm w-auto" wire:model.live="selectedRouter">
            <option value="">{{ __('-- Select Router --') }}</option>
            @foreach($routers as $r)<option value="{{ $r->router_name }}">{{ $r->router_name }} ({{ $r->ip_address }})</option>@endforeach
        </select>
        @if($selectedRouter)
            <button class="btn btn-sm btn-outline-secondary" wire:click="loadData">
                <span wire:loading.remove wire:target="loadData"><i class="bi bi-arrow-clockwise"></i> {{ __('Refresh') }}</span>
                <span wire:loading wire:target="loadData"><span class="spinner-border spinner-border-sm"></span></span>
            </button>
        @endif
    </div>

    @if(!$selectedRouter)
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router.') }}</div>
    @else
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link {{ $activeTab==='servers'?'active':'' }}" wire:click="$set('activeTab','servers')"><i class="bi bi-server me-1"></i>{{ __('PPPoE Servers') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='profiles'?'active':'' }}" wire:click="$set('activeTab','profiles')"><i class="bi bi-person-lines-fill me-1"></i>{{ __('PPP Profiles') }}</button></li>
        <li class="nav-item"><a class="nav-link {{ $activeTab === 'secrets' ? 'active fw-bold border-bottom border-3 border-primary' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'secrets')">{{ __('PPP Secrets') }}</a></li>
        <li class="nav-item"><a class="nav-link {{ $activeTab === 'ovpn' ? 'active fw-bold border-bottom border-3 border-primary' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'ovpn')">{{ __('OpenVPN Server') }}</a></li>
        <li class="nav-item"><a class="nav-link {{ $activeTab === 'active' ? 'active fw-bold border-bottom border-3 border-primary' : '' }}" href="#" wire:click.prevent="$set('activeTab', 'active')"><span class="badge bg-success me-1">{{ count($activeSessions) }}</span> {{ __('Active') }}</a></li>
    </ul>

    {{-- SERVERS --}}
    @if($activeTab==='servers')
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header {{ $editServerId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editServerId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editServerId ? __('Edit PPPoE Server') : __('Add PPPoE Server') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addPppoeServer">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">{{ __('Service Name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="srv_service_name" placeholder="pppoe-server">
                                @error('srv_service_name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Interface') }} <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" wire:model.defer="srv_interface">
                                    <option value="">{{ __('-- Select --') }}</option>
                                    @foreach($interfaces as $i)<option value="{{ $i }}">{{ $i }}</option>@endforeach
                                </select>
                                @error('srv_interface')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Default Profile') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="srv_default_profile">
                                    <option value="default">{{ __('default') }}</option>
                                    @foreach($pppProfiles as $profile)
                                        <option value="{{ $profile['name'] }}">{{ $profile['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label d-block">{{ __('Authentication Protocols') }}</label>
                                <div class="d-flex flex-wrap gap-3 p-2 bg-light rounded shadow-sm">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auth-pap" value="pap" wire:model.defer="srv_authentication">
                                        <label class="form-check-label ps-1" for="auth-pap">PAP</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auth-chap" value="chap" wire:model.defer="srv_authentication">
                                        <label class="form-check-label ps-1" for="auth-chap">CHAP</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auth-mschap1" value="mschap1" wire:model.defer="srv_authentication">
                                        <label class="form-check-label ps-1" for="auth-mschap1">MSCHAP1</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auth-mschap2" value="mschap2" wire:model.defer="srv_authentication">
                                        <label class="form-check-label ps-1" for="auth-mschap2">MSCHAP2</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4"><label class="form-label">{{ __('Max MTU') }}</label><input type="number" class="form-control form-control-sm" wire:model.defer="srv_max_mtu"></div>
                            <div class="col-4"><label class="form-label">{{ __('Max MRU') }}</label><input type="number" class="form-control form-control-sm" wire:model.defer="srv_max_mru"></div>
                            <div class="col-4"><label class="form-label">{{ __('MRRU') }}</label><input type="text" class="form-control form-control-sm" wire:model.defer="srv_mrru" placeholder="{{ __('Disabled') }}"></div>
                            <div class="col-12"><label class="form-label">{{ __('Keepalive (s)') }}</label><input type="number" class="form-control form-control-sm" wire:model.defer="srv_keepalive"></div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                    <span><i class="bi bi-{{ $editServerId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editServerId ? __('Update Server') : __('Add Server') }}</span>
                                </button>
                                @if($editServerId)
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editServerId', null)">{{ __('Cancel') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-server me-1"></i>{!! __('PPPoE Servers on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table">
                        <thead class="table-light"><tr><th>{{ __('Service Name') }}</th><th>{{ __('Interface') }}</th><th>{{ __('Auth') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse($pppoeServers as $srv)
                            <tr>
                                <td><strong>{{ $srv['service-name'] ?? '-' }}</strong></td>
                                <td><span class="badge bg-secondary">{{ $srv['interface'] ?? '-' }}</span></td>
                                <td><small>{{ $srv['authentication'] ?? '-' }}</small></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" wire:click="editPppoeServer({{ json_encode($srv) }})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-danger btn-sm" wire:click="removePppoeServer('{{ $srv['name'] ?? '' }}')" wire:confirm="{{ __('Remove this server?') }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No PPPoE servers found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PROFILES --}}
    @if($activeTab==='profiles')
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-secondary text-white d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-person-lines-fill me-2"></i>{!! __('PPP Profiles on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</span>
                    <span class="badge bg-light text-dark">{{ count($pppProfiles) }} {{ __('Found') }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table">
                        <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Rate Limit (Up/Down)') }}</th><th>{{ __('Local Addr') }}</th><th>{{ __('Remote Addr') }}</th><th>{{ __('Comment') }}</th></tr></thead>
                        <tbody>
                            @forelse($pppProfiles as $p)
                            <tr>
                                <td class="fw-bold text-primary">{{ $p['name'] ?? '-' }}</td>
                                <td><code class="text-danger">{{ $p['rate-limit'] ?? '-' }}</code></td>
                                <td><small class="text-muted">{{ $p['local-address'] ?? 'default' }}</small></td>
                                <td><small class="text-muted">{{ $p['remote-address'] ?? 'default' }}</small></td>
                                <td><small class="text-muted italic">{{ $p['comment'] ?? '' }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-5 text-italic">{{ __('No profiles found on this router.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="mt-2 x-small text-muted italic">{!! __('Note: To add or edit packages, please go to the :link menu.', ['link' => '<b><a wire:navigate.hover href="' . route('package-list-setup') . '">' . __('Package List') . '</a></b>']) !!}</div>
        </div>
    </div>
    @endif

    {{-- SECRETS --}}
    @if($activeTab==='secrets')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header {{ $editSecretId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editSecretId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editSecretId ? __('Edit PPP Secret') : __('Add PPP Secret') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addSecret">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('Username') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="sec_name">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-sm" wire:model.defer="sec_password">
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('Profile') }} <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" wire:model.defer="sec_profile">
                                    <option value="default">{{ __('default') }}</option>
                                    @foreach($pppProfiles as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Service') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="sec_service">
                                    <option value="pppoe">pppoe</option><option value="l2tp">l2tp</option><option value="pptp">pptp</option><option value="any">any</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('Local IP (Static)') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="sec_local_address" placeholder="10.0.0.1">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Remote IP (Static)') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="sec_remote_address" placeholder="10.0.0.2">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Caller ID (MAC/Binding)') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="sec_caller_id" placeholder="AA:BB:CC:DD:EE:FF">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="sec_comment">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-{{ $editSecretId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editSecretId ? __('Update Secret') : __('Add Secret') }}</button>
                            @if($editSecretId)
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editSecretId', null)">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-key me-1"></i>{!! __('PPP Secrets on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table">
                        <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Profile') }}</th><th>{{ __('Service') }}</th><th>{{ __('IP') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse($pppSecrets as $s)
                            <tr>
                                <td><strong>{{ $s['name'] ?? '-' }}</strong></td>
                                <td><code>{{ $s['profile'] ?? '-' }}</code></td>
                                <td><span class="badge bg-info text-dark">{{ $s['service'] ?? '-' }}</span></td>
                                <td><small>{{ $s['caller-id'] ?? '' }}</small></td>
                                <td><small class="text-muted">{{ $s['comment'] ?? '' }}</small></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" wire:click="editSecret({{ json_encode($s) }})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-danger btn-sm" wire:click="removeSecret('{{ $s['name'] ?? '' }}')" wire:confirm="{{ __('Delete secret :name?', ['name' => $s['name'] ?? '']) }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No PPP secrets found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- OVPN --}}
    @if($activeTab === 'ovpn')
        <div class="row">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-shield-lock-fill me-2"></i>{{ __('OpenVPN Config') }}</span>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch" wire:model.defer="ovpn_enabled">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 border-bottom pb-2">
                             <label class="form-label small fw-bold text-muted uppercase">{{ __('Interface Name') }}</label>
                             <input type="text" class="form-control form-control-sm" wire:model.defer="ovpn_name">
                             <div class="x-small text-muted italic mt-1">{{ __("Default is usually 'ovpn-server1'.") }}</div>
                        </div>
                        <form wire:submit.prevent="saveOvpn">
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('Mode') }}</label>
                                    <select class="form-select form-select-sm" wire:model.defer="ovpn_mode">
                                        <option value="ip">IP (Tun)</option><option value="ethernet">Ethernet (Tap)</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('Protocol') }}</label>
                                    <select class="form-select form-select-sm" wire:model.defer="ovpn_protocol">
                                        <option value="tcp">tcp</option><option value="udp">udp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label class="form-label small fw-bold">{{ __('Port') }}</label>
                                    <input type="number" class="form-control form-control-sm" wire:model.defer="ovpn_port">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold">{{ __('Netmask') }}</label>
                                    <input type="number" class="form-control form-control-sm" wire:model.defer="ovpn_netmask">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold">{{ __('Max MTU') }}</label>
                                    <input type="number" class="form-control form-control-sm" wire:model.defer="ovpn_max_mtu">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('MAC Address') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="ovpn_mac_address">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('Keepalive Timeout') }}</label>
                                    <input type="number" class="form-control form-control-sm" wire:model.defer="ovpn_keepalive_timeout">
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('User Auth Method') }}</label>
                                    <select class="form-select form-select-sm" wire:model.defer="ovpn_user_auth_method">
                                        <option value="pap">pap</option>
                                        <option value="chap">chap</option>
                                        <option value="mschap1">mschap1</option>
                                        <option value="mschap2">mschap2</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('Profile') }}</label>
                                    <select class="form-select form-select-sm" wire:model.defer="ovpn_default_profile">
                                        @foreach($pppProfiles as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold">{{ __('Certificate') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="ovpn_certificate">
                                    <option value="none">none</option>
                                    @foreach($certificates as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                </select>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" wire:model.defer="ovpn_require_client_cert">
                                <label class="form-check-label small fw-bold">{{ __('Require Client Certificate') }}</label>
                            </div>

                             <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">{{ __('TLS Version') }}</label>
                                    <select class="form-select form-select-sm" wire:model.defer="ovpn_tls_version">
                                        <option value="any">any</option>
                                        <option value="1.0">1.0</option>
                                        <option value="1.1">1.1</option>
                                        <option value="1.2">1.2</option>
                                        <option value="1.3">1.3</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label x-small fw-bold text-muted">{{ __('Redirect Gateway') }}</label>
                                    <div class="d-flex flex-wrap gap-2 border p-1 rounded bg-light">
                                        @foreach(['disabled', 'def1', 'ipv6'] as $opt)
                                            <label class="x-small mb-0 border px-2 py-1 rounded bg-white pointer hover-shadow">
                                                <input type="checkbox" value="{{ $opt }}" wire:model.defer="ovpn_redirect_gateway"> {{ $opt }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label x-small fw-bold">{{ __('Authentication') }}</label>
                                <div class="d-flex flex-wrap gap-1 border p-1 rounded bg-light">
                                    @foreach(['sha1', 'md5', 'sha256', 'sha512', 'sha384', 'null'] as $a)
                                        <label class="x-small mb-0 border px-1 rounded bg-white pointer">
                                            <input type="checkbox" value="{{ $a }}" wire:model.defer="ovpn_auth"> {{ $a }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label x-small fw-bold">{{ __('Cipher') }}</label>
                                <div class="d-flex flex-wrap gap-1 border p-1 rounded bg-light">
                                    @foreach(['aes128-cbc', 'aes256-cbc', 'blowfish', 'aes128-gcm', 'aes192-gcm', 'aes256-gcm', 'null'] as $c)
                                        <label class="x-small mb-0 border px-1 rounded bg-white pointer">
                                            <input type="checkbox" value="{{ $c }}" wire:model.defer="ovpn_cipher"> {{ $c }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">{{ __('Key Renegotiate (Sec)') }}</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="ovpn_key_renegotiate_sec">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-sm rounded-pill shadow-sm" wire:loading.attr="disabled">
                                    <span wire:loading.remove><i class="bi bi-save2 me-1"></i>{{ __('Apply Config') }}</span>
                                    <span wire:loading><i class="bi bi-hourglass-split spin me-1"></i>{{ __('Saving...') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-people-fill me-2"></i>{{ __('Active OVPN Clients') }}</span>
                        <div class="d-flex align-items-center gap-2">
                             <span class="badge bg-success rounded-pill" wire:loading.remove wire:target="refreshSessions">{{ count(array_filter($activeSessions, fn($s) => str_contains(strtolower($s['service'] ?? ''), 'ovpn'))) }} {{ __('Online') }}</span>
                             <button class="btn btn-xs btn-outline-light py-0 px-1" wire:click="refreshSessions" wire:loading.attr="disabled">
                                  <i class="bi bi-arrow-clockwise" wire:loading.class="spin" wire:target="refreshSessions"></i>
                             </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 x-small align-middle">
                                <thead class="table-light">
                                    <tr><th>{{ __('User') }}</th><th>{{ __('Address') }}</th><th>{{ __('Uptime') }}</th><th>{{ __('Service') }}</th></tr>
                                </thead>
                                <tbody>
                                    @php $ovpnActive = array_filter($activeSessions, fn($s) => str_contains(strtolower($s['service'] ?? ''), 'ovpn')); @endphp
                                    @forelse($ovpnActive as $s)
                                        <tr>
                                            <td class="fw-bold text-primary">{{ $s['name'] ?? '-' }}</td>
                                            <td><code>{{ $s['address'] ?? '-' }}</code></td>
                                            <td>{{ $s['uptime'] ?? '-' }}</td>
                                            <td><span class="badge bg-secondary x-small">{{ $s['service'] ?? '-' }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="py-4 text-center text-muted italic">{{ __('No active OVPN clients detected on :router.', ['router' => $selectedRouter]) }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mt-3 bg-light border-start border-4 border-info">
                    <div class="card-body py-3">
                        <h6 class="fw-bold text-info mb-2"><i class="bi bi-patch-question-fill me-2"></i>{{ __('OVPN Configuration Clarification') }}</h6>
                        
                        <div class="row g-3">
                            <div class="col-12 border-bottom pb-2">
                                <div class="small fw-bold text-dark"><i class="bi bi-diagram-3 me-2"></i>{{ __('MODE (Tun vs Tap)') }}</div>
                                <p class="x-small text-muted mb-0">{!! __('Use :tun for standard VPN connections on Android, iOS, and Windows. Only use :tap if you are doing Layer 2 bridging, usually for router-to-router links.', ['tun' => '<b>IP (Tun)</b>', 'tap' => '<b>Ethernet (Tap)</b>']) !!}</p>
                            </div>
                            
                            <div class="col-12 border-bottom pb-2">
                                <div class="small fw-bold text-dark"><i class="bi bi-shield-lock me-2"></i>{{ __('CERTIFICATE REQUIREMENTS') }}</div>
                                <p class="x-small text-muted mb-0">{!! __("The selected certificate MUST have a :key imported. For public connections, ensure the certificate's 'Common Name' matches your router's Public IP or DDNS. Most clients expect a CA-signed cert.", ['key' => '<b>Private Key</b>']) !!}</p>
                            </div>
                            
                            <div class="col-12">
                                <div class="small fw-bold text-dark"><i class="bi bi-fire me-2"></i>{{ __('FIREWALL RULES') }}</div>
                                <p class="x-small text-muted mb-0">{!! __('Manually add a rule in :code: :br Chain: :input | Protocol: :protocol | Dst. Port: :port | Action: :accept', ['code' => '<code>/ip firewall filter</code>', 'br' => '<br>', 'input' => '<b>input</b>', 'protocol' => '<b>' . e($ovpn_protocol) . '</b>', 'port' => '<b>' . e($ovpn_port) . '</b>', 'accept' => '<b>accept</b>']) !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- SESSIONS --}}
    @if($activeTab==='active')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-activity me-1"></i>{!! __('Active PPP Sessions on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</span>
            <button class="btn btn-sm btn-outline-success" wire:click="refreshSessions"><i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 data-table">
                <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Service') }}</th><th>{{ __('Address') }}</th><th>{{ __('Uptime') }}</th><th>{{ __('Caller ID') }}</th></tr></thead>
                <tbody>
                    @forelse($activeSessions as $s)
                    <tr>
                        <td><strong>{{ $s['name'] ?? '-' }}</strong></td>
                        <td><span class="badge bg-success">{{ $s['service'] ?? '-' }}</span></td>
                        <td><code>{{ $s['address'] ?? '-' }}</code></td>
                        <td>{{ $s['uptime'] ?? '-' }}</td>
                        <td><small>{{ $s['caller-id'] ?? '-' }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No active sessions.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif
</div>
