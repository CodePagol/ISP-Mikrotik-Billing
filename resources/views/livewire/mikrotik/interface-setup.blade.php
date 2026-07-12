<div class="zoom-in">
    <x-slot name="header">{{ __('Interface & VLAN Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-ethernet text-primary fs-5"></i>
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
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router to manage interfaces.') }}</div>
    @else
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link {{ $activeTab==='interfaces'?'active':'' }}" wire:click="$set('activeTab','interfaces')"><i class="bi bi-list-nested me-1"></i>{{ __('Interfaces') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='vlans'?'active':'' }}" wire:click="$set('activeTab','vlans')"><i class="bi bi-tag me-1"></i>{{ __('VLANs') }}</button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='bridges'?'active':'' }}" wire:click="$set('activeTab','bridges')"><i class="bi bi-diagram-2 me-1"></i>{{ __('Bridges') }}</button></li>
    </ul>

    {{-- INTERFACES --}}
    @if($activeTab==='interfaces')
    <div class="card">
        <div class="card-header"><i class="bi bi-list-nested me-1"></i>{!! __('All Interfaces on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-interfaces">
                <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th><th>{{ __('MTU') }}</th><th>{{ __('MAC Address') }}</th><th>{{ __('Status') }}</th><th>{{ __('Act') }}</th></tr></thead>
                <tbody>
                    @forelse($interfaces as $i)
                    <tr wire:key="row-iface-{{ $loop->index }}-{{ $i['name'] ?? $loop->index }}" class="{{ ($i['disabled'] ?? 'false') !== 'false' ? 'table-secondary text-muted' : '' }}">
                        <td><strong>{{ $i['name'] ?? '-' }}</strong> {!! ($i['running'] ?? 'false') === 'true' ? '<i class="bi bi-activity text-success" title="' . __('Running') . '"></i>' : '' !!}</td>
                        <td><span class="badge bg-secondary">{{ $i['type'] ?? '-' }}</span></td>
                        <td><small>{{ $i['mtu'] ?? '-' }} / {{ $i['actual-mtu'] ?? '-' }}</small></td>
                        <td><code>{{ $i['mac-address'] ?? 'N/A' }}</code></td>
                        <td>
                            @if(($i['disabled'] ?? 'false') === 'false')
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Disabled') }}</span>
                            @endif
                        </td>
                        <td>
                            @if(($i['disabled'] ?? 'false') !== 'false')
                                <button class="btn btn-success btn-sm" wire:click="toggleInterface('{{ $i['name'] ?? '' }}', true)"><i class="bi bi-play-fill"></i></button>
                            @else
                                <button class="btn btn-warning btn-sm" wire:click="toggleInterface('{{ $i['name'] ?? '' }}', false)"><i class="bi bi-pause-fill"></i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No interfaces found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

    {{-- VLANS --}}
    @if($activeTab==='vlans')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header {{ $editVlanId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editVlanId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editVlanId ? __('Edit VLAN') : __('Add VLAN') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addVlan">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('vlan_name') is-invalid @enderror" wire:model.defer="vlan_name" placeholder="vlan10-staff">
                            @error('vlan_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('VLAN ID') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm @error('vlan_id') is-invalid @enderror" wire:model.defer="vlan_id" min="1" max="4094">
                            @error('vlan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Parent Interface') }} <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('vlan_interface') is-invalid @enderror" wire:model.defer="vlan_interface">
                                <option value="">{{ __('-- Select --') }}</option>
                                @foreach($interfaces as $ifc)
                                    @if(($ifc['type'] ?? '') !== 'vlan')
                                        <option value="{{ $ifc['name'] ?? '' }}">{{ $ifc['name'] ?? '' }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('vlan_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="vlan_comment">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-{{ $editVlanId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editVlanId ? __('Update VLAN') : __('Add VLAN') }}</button>
                            @if($editVlanId)
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editVlanId', null)">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-tag me-1"></i>{!! __('VLANs on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-vlans">
                        <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('VLAN ID') }}</th><th>{{ __('Interface') }}</th><th>{{ __('MTU') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Act') }}</th></tr></thead>
                        <tbody>
                            @forelse($vlans as $v)
                            <tr wire:key="row-vlan-{{ $loop->index }}-{{ $v['vlan-id'] ?? $loop->index }}">
                                <td><strong>{{ $v['name'] ?? '-' }}</strong></td>
                                <td><span class="badge bg-primary fs-6">{{ $v['vlan-id'] ?? '-' }}</span></td>
                                <td><span class="badge bg-secondary">{{ $v['interface'] ?? '-' }}</span></td>
                                <td><small>{{ $v['mtu'] ?? '-' }}</small></td>
                                <td><small class="text-muted">{{ $v['comment'] ?? '' }}</small></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" wire:click="editVlan({{ json_encode($v) }})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-danger btn-sm" wire:click="removeVlan('{{ $v['name'] ?? '' }}')" wire:confirm="{{ __('Remove VLAN?') }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No VLANs configured.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- BRIDGES --}}
    @if($activeTab==='bridges')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white"><i class="bi bi-plus-circle me-1"></i>{{ __('Add Bridge') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addBridge">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Bridge Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('bridge_name') is-invalid @enderror" wire:model.defer="bridge_name" placeholder="bridge1">
                            @error('bridge_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="bridge_comment">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-plus-lg me-1"></i>{{ __('Add Bridge') }}</button>
                    </form>
                </div>
            </div>
            <div class="alert alert-info small"><i class="bi bi-info-circle me-1"></i>{{ __('To add ports to a bridge, please use Winbox. Interface port assignments can be complex and are best done securely in the Mikrotik UI.') }}</div>
        </div>
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-diagram-2 me-1"></i>{!! __('Bridges on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-bridges">
                        <thead class="table-light"><tr><th>{{ __('Bridge Name') }}</th><th>{{ __('MTU') }}</th><th>{{ __('MAC Address') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Act') }}</th></tr></thead>
                        <tbody>
                            @forelse($bridges as $b)
                            <tr wire:key="row-bridge-{{ $loop->index }}-{{ $b['name'] ?? $loop->index }}">
                                <td><strong>{{ $b['name'] ?? '-' }}</strong></td>
                                <td><small>{{ $b['mtu'] ?? '-' }}</small></td>
                                <td><code>{{ $b['mac-address'] ?? 'N/A' }}</code></td>
                                <td><small class="text-muted">{{ $b['comment'] ?? '' }}</small></td>
                                <td><button class="btn btn-danger btn-sm" wire:click="removeBridge('{{ $b['name'] ?? '' }}')" wire:confirm="{{ __('Remove Bridge?') }}"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No bridges configured.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="card border-0 bg-transparent">
                <h6 class="text-muted mb-2">{{ __('Bridge Ports') }}</h6>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0 data-table" wire:key="tbl-bridge-ports">
                            <thead class="table-light"><tr><th>{{ __('Interface') }}</th><th>{{ __('Bridge') }}</th><th>{{ __('Status') }}</th></tr></thead>
                            <tbody>
                                @forelse($bridgePorts as $p)
                                <tr wire:key="row-bport-{{ $loop->index }}-{{ $p['interface'] ?? $loop->index }}">
                                    <td><span class="badge bg-secondary">{{ $p['interface'] ?? '-' }}</span></td>
                                    <td><strong>{{ $p['bridge'] ?? '-' }}</strong></td>
                                    <td><small class="text-muted">{{ $p['status'] ?? '-' }}</small></td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">{{ __('No bridge ports configured.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
