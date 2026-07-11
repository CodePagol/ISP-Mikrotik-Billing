<div class="zoom-in">
    <x-slot name="header">{{ __('RADIUS Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-hdd-network-fill text-primary fs-5"></i>
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
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router to manage RADIUS servers.') }}</div>
    @else
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header {{ $editRadiusId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editRadiusId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editRadiusId ? __('Edit RADIUS Server') : __('Add RADIUS Server') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addServer">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Address') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('r_address') is-invalid @enderror" wire:model.defer="r_address" placeholder="10.0.0.1">
                            @error('r_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Secret') }} <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-sm @error('r_secret') is-invalid @enderror" wire:model.defer="r_secret">
                            @error('r_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Service') }} <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" wire:model.defer="r_service">
                                <option value="ppp">PPP</option>
                                <option value="hotspot">Hotspot</option>
                                <option value="dhcp">DHCP</option>
                                <option value="login">Login</option>
                                <option value="wireless">Wireless</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('Auth Port') }}</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="r_auth_port">
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Acct Port') }}</label>
                                <input type="number" class="form-control form-control-sm" wire:model.defer="r_acct_port">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Timeout (ms)') }}</label>
                            <input type="number" class="form-control form-control-sm" wire:model.defer="r_timeout">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="r_comment">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-{{ $editRadiusId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editRadiusId ? __('Update Server') : __('Add Server') }}</button>
                            @if($editRadiusId)
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editRadiusId', null)">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-hdd-network-fill me-1"></i>{!! __('RADIUS Servers on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-radius-servers">
                        <thead class="table-light"><tr><th>{{ __('Address') }}</th><th>{{ __('Service(s)') }}</th><th>{{ __('Auth/Acct') }}</th><th>{{ __('Timeout') }}</th><th>{{ __('Status') }}</th><th>{{ __('Act') }}</th></tr></thead>
                        <tbody>
                            @forelse($radiusServers as $s)
                            <tr wire:key="row-radius-{{ $loop->index }}-{{ $s['address'] ?? $loop->index }}">
                                <td><strong><code>{{ $s['address'] ?? '-' }}</code></strong></td>
                                <td>
                                    @foreach(explode(',', $s['service'] ?? '') as $srv)
                                        <span class="badge bg-info text-dark">{{ $srv }}</span>
                                    @endforeach
                                </td>
                                <td><small>{{ $s['authentication-port'] ?? '-' }} / {{ $s['accounting-port'] ?? '-' }}</small></td>
                                <td><small>{{ $s['timeout'] ?? '-' }}</small></td>
                                <td>
                                    @if(($s['disabled'] ?? 'false') === 'false')
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Disabled') }}</span>
                                    @endif
                                </td>
                                <td class="d-flex gap-1">
                                    <button class="btn btn-warning btn-sm" wire:click="editServer({{ json_encode($s) }})"><i class="bi bi-pencil-square"></i></button>
                                    @if(($s['disabled'] ?? 'false') === 'false')
                                        <button class="btn btn-secondary btn-sm" wire:click="toggleServer('{{ $s['address'] ?? '' }}', false)" title="{{ __('Disable') }}"><i class="bi bi-pause-fill"></i></button>
                                    @else
                                        <button class="btn btn-success btn-sm" wire:click="toggleServer('{{ $s['address'] ?? '' }}', true)" title="{{ __('Enable') }}"><i class="bi bi-play-fill"></i></button>
                                    @endif
                                    <button class="btn btn-danger btn-sm" wire:click="removeServer('{{ $s['address'] ?? '' }}')" wire:confirm="{{ __('Remove RADIUS server at :address?', ['address' => $s['address'] ?? '']) }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No RADIUS servers configured.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
