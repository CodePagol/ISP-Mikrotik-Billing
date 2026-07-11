<div class="zoom-in">
    <x-slot name="header">{{ __('OpenVPN Server Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-shield-lock-fill text-primary fs-5"></i>
        <select class="form-select form-select-sm w-auto" wire:model.change="selectedRouter">
            @foreach($routers as $r)
                <option value="{{ $r->router_name }}">{{ $r->router_name }} ({{ $r->ip_address }})</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-secondary" wire:click="loadData" wire:loading.attr="disabled">
            <i class="bi bi-arrow-clockwise" wire:loading.class="spin"></i> {{ __('Refresh') }}
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-gear-fill me-2"></i>{{ __('OVPN Server Settings') }}</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" wire:model.defer="enabled">
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Port') }}</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="port">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Mode') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="mode">
                                    <option value="ip">{{ __('IP (Tun)') }}</option>
                                    <option value="ethernet">{{ __('Ethernet (Tap)') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Protocol') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="protocol">
                                    <option value="tcp">TCP</option>
                                    <option value="udp">UDP</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('MAC Address') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="mac_address" placeholder="00:00:00:00:00:00">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Max MTU') }}</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="max_mtu">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Keepalive Timeout') }}</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="keepalive_timeout">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Default Profile') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="default_profile">
                                    @foreach($profiles as $p)<option value="{{ $p }}">{{ $p }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">{{ __('Certificate') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="certificate">
                                    <option value="none">none</option>
                                    @foreach($certificates as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.defer="require_client_cert">
                                    <label class="form-check-label small fw-bold">{{ __('Require Client Certificate') }}</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">{{ __('Auth Algorithms') }}</label>
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @foreach(['sha1', 'md5', 'null'] as $a)
                                        <div class="form-check form-check-inline border rounded-3 px-2 py-1 bg-light">
                                            <input class="form-check-input" type="checkbox" value="{{ $a }}" wire:model.defer="auth">
                                            <label class="form-check-label x-small">{{ $a }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold">{{ __('Cipher Algorithms') }}</label>
                                <div class="d-flex flex-wrap gap-2 pt-1">
                                    @foreach(['aes128-cbc', 'aes256-cbc', 'blowfish', 'aes192-cbc', 'null'] as $c)
                                        <div class="form-check form-check-inline border rounded-3 px-2 py-1 bg-light">
                                            <input class="form-check-input" type="checkbox" value="{{ $c }}" wire:model.defer="cipher">
                                            <label class="form-check-label x-small">{{ $c }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill shadow-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="bi bi-save2 me-2"></i>{{ __('Apply Changes') }}</span>
                                <span wire:loading><i class="bi bi-hourglass-split spin me-2"></i>{{ __('Applying...') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="alert alert-info border-0 shadow-sm rounded-4">
                <h6 class="fw-bold"><i class="bi bi-info-circle-fill me-2"></i>{{ __('How to Setup OVPN') }}</h6>
                <p class="small mb-2">{{ __('To successfully use OpenVPN on MikroTik:') }}</p>
                <ol class="small x-small ps-3 mb-0">
                    <li>{!! __('Generate or install a :cert on the router.', ['cert' => '<b>' . __('Server Certificate') . '</b>']) !!}</li>
                    <li>{!! __('Ensure the :profile has valid Local/Remote addresses.', ['profile' => '<b>' . __('Default Profile') . '</b>']) !!}</li>
                    <li>{!! __('Open port :port in your Firewall filter rules.', ['port' => '<b>' . e($port) . ' (TCP)</b>']) !!}</li>
                    <li>{!! __('If using Windows clients, :mode is usually required.', ['mode' => '<b>' . __('Mode: IP') . '</b>']) !!}</li>
                </ol>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-3">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-activity fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted x-small uppercase fw-bold">{{ __('Server Connectivity') }}</div>
                        <div class="fs-5 fw-bold text-{{ $enabled ? 'success' : 'danger' }}">
                            {{ $enabled ? __('Active & Listening') : __('Server Stopped') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { from {transform: rotate(0deg);} to {transform: rotate(360deg);} }
        .x-small { font-size: 0.75rem; }
    </style>
</div>
