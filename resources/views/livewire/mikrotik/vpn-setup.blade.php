<div class="zoom-in">
    <x-slot name="header">{{ __('VPN Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-globe text-primary fs-5"></i>
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
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router to manage VPN settings.') }}</div>
    @else
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link {{ $activeTab==='l2tp'?'active':'' }}" wire:click="$set('activeTab','l2tp')"><i class="bi bi-shield-lock me-1"></i>{{ __('L2TP/IPsec') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='pptp'?'active':'' }}" wire:click="$set('activeTab','pptp')"><i class="bi bi-arrow-down-up me-1"></i>{{ __('PPTP') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='sstp'?'active':'' }}" wire:click="$set('activeTab','sstp')"><i class="bi bi-lock me-1"></i>{{ __('SSTP') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='sessions'?'active':'' }}" wire:click="$set('activeTab','sessions')"><i class="bi bi-activity me-1"></i>{{ __('Active VPN Sessions') }}</button></li>
    </ul>

    <div class="row">
        <div class="col-lg-6">
            {{-- L2TP --}}
            @if($activeTab==='l2tp')
            <div class="card">
                <div class="card-header bg-primary text-white"><i class="bi bi-shield-lock me-1"></i>{{ __('L2TP Server Settings') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="saveL2tp">
                        <div class="form-check form-switch mb-3 text-start">
                            <input class="form-check-input form-check-input-lg" type="checkbox" role="switch" id="l2tpEnable" wire:model.defer="l2tp_enabled">
                            <label class="form-check-label ms-2" for="l2tpEnable"><strong>{{ __('Enable L2TP Server') }}</strong></label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Default Profile') }}</label>
                            <select class="form-select" wire:model.defer="l2tp_profile">
                                @forelse($pppProfiles as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@empty<option value="default">default</option>@endforelse
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Authentication') }}</label>
                            <select class="form-select" wire:model.defer="l2tp_auth">
                                <option value="mschap2,mschap1">{{ __('MS-CHAP v1 & v2') }}</option>
                                <option value="mschap2">{{ __('MS-CHAP v2 Only') }}</option>
                                <option value="pap,chap,mschap1,mschap2">{{ __('All (Less Secure)') }}</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">{!! __('IPsec Secret :info', ['info' => '<small class="text-muted">(' . __('enables IPsec') . ')</small>']) !!}</label>
                            <input type="password" class="form-control" wire:model.defer="l2tp_ipsec_secret" placeholder="{{ __('Optional but recommended') }}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>{{ __('Save L2TP Settings') }}</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- PPTP --}}
            @if($activeTab==='pptp')
            <div class="card">
                <div class="card-header bg-primary text-white"><i class="bi bi-arrow-down-up me-1"></i>{{ __('PPTP Server Settings') }}</div>
                <div class="card-body">
                    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>{{ __('PPTP is considered obsolete and insecure. Use L2TP/IPsec or SSTP if possible.') }}</div>
                    <form wire:submit.prevent="savePptp">
                        <div class="form-check form-switch mb-3 text-start">
                            <input class="form-check-input form-check-input-lg" type="checkbox" role="switch" id="pptpEnable" wire:model.defer="pptp_enabled">
                            <label class="form-check-label ms-2" for="pptpEnable"><strong>{{ __('Enable PPTP Server') }}</strong></label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Default Profile') }}</label>
                            <select class="form-select" wire:model.defer="pptp_profile">
                                @forelse($pppProfiles as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@empty<option value="default">default</option>@endforelse
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">{{ __('Authentication') }}</label>
                            <select class="form-select" wire:model.defer="pptp_auth">
                                <option value="mschap2,mschap1">{{ __('MS-CHAP v1 & v2') }}</option>
                                <option value="mschap2">{{ __('MS-CHAP v2 Only') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>{{ __('Save PPTP Settings') }}</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- SSTP --}}
            @if($activeTab==='sstp')
            <div class="card">
                <div class="card-header bg-primary text-white"><i class="bi bi-lock me-1"></i>{{ __('SSTP Server Settings') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="saveSstp">
                        <div class="form-check form-switch mb-3 text-start">
                            <input class="form-check-input form-check-input-lg" type="checkbox" role="switch" id="sstpEnable" wire:model.defer="sstp_enabled">
                            <label class="form-check-label ms-2" for="sstpEnable"><strong>{{ __('Enable SSTP Server') }}</strong></label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Default Profile') }}</label>
                            <select class="form-select" wire:model.defer="sstp_profile">
                                @forelse($pppProfiles as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@empty<option value="default">default</option>@endforelse
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">{{ __('Port') }}</label>
                            <input type="number" class="form-control" wire:model.defer="sstp_port" min="1" max="65535">
                        </div>
                        <div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>{{ __('SSTP requires a valid certificate to be configured on the router. Ensure a certificate is installed and selected in Winbox/WebFig for clients to connect successfully.') }}</div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>{{ __('Save SSTP Settings') }}</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        @if($activeTab !== 'sessions')
        <div class="col-lg-6">
            <div class="card border-info bg-info-subtle shadow-none">
                <div class="card-body">
                    <h5 class="text-info-emphasis mb-3"><i class="bi bi-lightbulb"></i> {{ __('VPN Setup Guide') }}</h5>
                    <p class="small text-muted mb-2">{{ __('To provision a VPN for a user:') }}</p>
                    <ol class="small text-muted mb-0 ps-3">
                        <li class="mb-1">{!! __('Go to :link and switch to the :tab tab.', ['link' => '<strong><a href="' . route('mikrotik-pppoe-setup') . '">' . __('PPPoE Setup') . '</a></strong>', 'tab' => '<strong>' . __('PPP Secrets') . '</strong>']) !!}</li>
                        <li class="mb-1">{{ __('Create a new secret with Username and Password.') }}</li>
                        <li class="mb-1">{!! __('Set the :service to :any (or specifically :l2tp, :pptp, :sstp).', ['service' => '<strong>' . __('Service') . '</strong>', 'any' => '<code>any</code>', 'l2tp' => '<code>l2tp</code>', 'pptp' => '<code>pptp</code>', 'sstp' => '<code>sstp</code>']) !!}</li>
                        <li class="mb-1">{{ __("The user can now connect using the router's public IP address using their credentials.") }}</li>
                    </ol>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- SESSIONS --}}
    @if($activeTab==='sessions')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-shield-check me-1"></i>{!! __('Active VPN/PPP Sessions on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</span>
            <button class="btn btn-sm btn-outline-success" wire:click="refreshSessions"><i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-vpn-sessions">
                <thead class="table-light"><tr><th>{{ __('User') }}</th><th>{{ __('Service') }}</th><th>{{ __('Remote IP') }}</th><th>{{ __('Uptime') }}</th><th>{{ __('Client IP') }}</th></tr></thead>
                <tbody>
                    @forelse($activeSessions as $s)
                    <tr wire:key="row-vpn-sess-{{ $loop->index }}-{{ $s['user'] ?? $loop->index }}">
                        <td><strong>{{ $s['name'] ?? '-' }}</strong></td>
                        <td>
                            @php $srv = $s['service'] ?? ''; @endphp
                            <span class="badge {{ $srv==='pppoe' ? 'bg-secondary' : 'bg-primary' }}">{{ $srv }}</span>
                        </td>
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
    </div>
    @endif
    @endif
</div>
