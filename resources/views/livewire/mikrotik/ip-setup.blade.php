<div class="zoom-in">
    <x-slot name="header">{{ __('IP & Pool Setup') }}</x-slot>

    {{-- Router Selector --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-router-fill text-primary fs-5"></i>
        <select class="form-select form-select-sm w-auto" wire:model.live="selectedRouter">
            <option value="">{{ __('-- Select Router --') }}</option>
            @foreach($routers as $r)
                <option value="{{ $r->router_name }}">{{ $r->router_name }} ({{ $r->ip_address }})</option>
            @endforeach
        </select>
        @if($selectedRouter)
            <button class="btn btn-sm btn-outline-secondary" wire:click="loadData">
                <span wire:loading.remove wire:target="loadData"><i class="bi bi-arrow-clockwise"></i> {{ __('Refresh') }}</span>
                <span wire:loading wire:target="loadData"><span class="spinner-border spinner-border-sm"></span></span>
            </button>
        @endif
    </div>

    @if(!$selectedRouter)
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router to manage IP addresses and pools.') }}</div>
    @else
        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-3" id="ipTabs">
            <li class="nav-item"><button class="nav-link {{ $activeTab === 'addresses' ? 'active' : '' }}" wire:click="$set('activeTab','addresses')"><i class="bi bi-hdd-network me-1"></i>{{ __('IP Addresses') }}</button></li>
            <li class="nav-item"><button class="nav-link {{ $activeTab === 'pools' ? 'active' : '' }}" wire:click="$set('activeTab','pools')"><i class="bi bi-diagram-3 me-1"></i>{{ __('IP Pools') }}</button></li>
            <li class="nav-item"><button class="nav-link {{ $activeTab === 'dhcp' ? 'active' : '' }}" wire:click="$set('activeTab','dhcp')"><i class="bi bi-server me-1"></i>{{ __('DHCP') }}</button></li>
        </ul>

        {{-- IP ADDRESSES TAB --}}
        @if($activeTab === 'addresses')
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header {{ $editAddressId ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                            <i class="bi bi-{{ $editAddressId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editAddressId ? __('Edit Address') : __('Add IP Address') }}
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="addAddress">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Address/Prefix') }} <span class="text-danger">*</span><small class="text-muted ms-1">(e.g. 192.168.1.1/24)</small></label>
                                    <input type="text" class="form-control form-control-sm @error('addr_address') is-invalid @enderror" wire:model.defer="addr_address" placeholder="192.168.1.1/24">
                                    @error('addr_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Interface') }} <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm @error('addr_interface') is-invalid @enderror" wire:model.defer="addr_interface">
                                        <option value="">{{ __('-- Select --') }}</option>
                                        @foreach($interfaces as $iface)<option value="{{ $iface }}">{{ $iface }}</option>@endforeach
                                    </select>
                                    @error('addr_interface')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Comment') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="addr_comment" placeholder="{{ __('Optional') }}">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary flex-fill" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="addAddress"><i class="bi bi-{{ $editAddressId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editAddressId ? __('Update Address') : __('Add Address') }}</span>
                                        <span wire:loading wire:target="addAddress"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Saving...') }}</span>
                                    </button>
                                    @if($editAddressId)
                                        <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('editAddressId', null)">{{ __('Cancel') }}</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-hdd-network me-1"></i>{!! __('IP Addresses on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                        <div class="card-body p-0">
                            <div class="table-responsive" wire:key="container-ip-addr-{{ $selectedRouter }}">
                                <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-ip-addresses">
                                    <thead class="table-light"><tr><th>{{ __('Address') }}</th><th>{{ __('Network') }}</th><th>{{ __('Interface') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Action') }}</th></tr></thead>
                                    <tbody>
                                        @forelse($ipAddresses as $addr)
                                        @php $isArr = is_array($addr); @endphp
                                            <tr wire:key="row-addr-{{ $loop->index }}-{{ md5($isArr ? ($addr['address'] ?? $loop->index) : $addr) }}">
                                                <td><code>{{ ($isArr ? $addr['address'] : $addr) ?? '-' }}</code></td>
                                                <td><code>{{ $isArr ? ($addr['network'] ?? '-') : '-' }}</code></td>
                                                <td><span class="badge bg-secondary">{{ $isArr ? ($addr['interface'] ?? '-') : '-' }}</span></td>
                                                <td><small class="text-muted">{{ $isArr ? ($addr['comment'] ?? '') : '' }}</small></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" wire:click="editAddress({{ json_encode($isArr ? $addr : ['address' => $addr]) }})"><i class="bi bi-pencil-square"></i></button>
                                                    <button class="btn btn-danger btn-sm" wire:click="removeAddress('{{ $isArr ? ($addr['address'] ?? '') : $addr }}')" wire:confirm="{{ __('Remove this IP address from MikroTik?') }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No IP addresses found.') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- IP POOLS TAB --}}
        @if($activeTab === 'pools')
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header {{ $editPoolId ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                            <i class="bi bi-{{ $editPoolId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editPoolId ? __('Edit Pool') : __('Add IP Pool') }}
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="addPool">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Pool Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('pool_name') is-invalid @enderror" wire:model.defer="pool_name" placeholder="dhcp-pool" {{ $editPoolId ? 'readonly' : '' }}>
                                    @error('pool_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Ranges') }} <span class="text-danger">*</span><small class="text-muted ms-1">(e.g. 192.168.1.10-192.168.1.100)</small></label>
                                    <input type="text" class="form-control form-control-sm @error('pool_ranges') is-invalid @enderror" wire:model.defer="pool_ranges" placeholder="192.168.1.10-192.168.1.100">
                                    @error('pool_ranges')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Next Pool') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="pool_next_pool" placeholder="none">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Comment') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="pool_comment" placeholder="{{ __('Optional') }}">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                                        <i class="bi bi-save me-1"></i>{{ $editPoolId ? __('Update') : __('Add Pool') }}
                                    </button>
                                    @if($editPoolId)
                                        <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('editPoolId', null)">{{ __('Cancel') }}</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-diagram-3 me-1"></i>{!! __('IP Pools on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                        <div class="card-body p-0">
                            <div class="table-responsive" wire:key="container-ip-pools-{{ $selectedRouter }}">
                                <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-ip-pools">
                                    <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Ranges') }}</th><th>{{ __('Next Pool') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Used') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                                    <tbody>
                                        @forelse($ipPools as $pool)
                                        @php $isP = is_array($pool); @endphp
                                        <tr wire:key="row-pool-{{ $loop->index }}-{{ md5($isP ? ($pool['name'] ?? $loop->index) : $pool) }}">
                                            <td><strong>{{ ($isP ? $pool['name'] : $pool) ?? '-' }}</strong></td>
                                            <td><code class="small">{{ $isP ? ($pool['ranges'] ?? '-') : '-' }}</code></td>
                                            <td><small>{{ $isP ? ($pool['next-pool'] ?? 'none') : 'none' }}</small></td>
                                            <td><small class="text-muted">{{ $isP ? ($pool['comment'] ?? '') : '' }}</small></td>
                                            <td><small class="text-muted">{{ $isP ? ($pool['total-addresses'] ?? '-') : '-' }}</small></td>
                                            <td>
                                                <button class="btn btn-info btn-sm" wire:click="editPool({{ json_encode($isP ? $pool : ['name' => $pool]) }})"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-danger btn-sm" wire:click="removePool('{{ $isP ? ($pool['name'] ?? '') : $pool }}')" wire:confirm="{{ __('Remove pool :name?', ['name' => $isP ? ($pool['name'] ?? '') : $pool]) }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No IP pools found.') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- DHCP TAB --}}
        @if($activeTab === 'dhcp')
            <div class="row g-3">
                <div class="col-lg-4">
                    {{-- DHCP Server Form --}}
                    <div class="card mb-3">
                        <div class="card-header {{ $editDhcpId ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                            <i class="bi bi-{{ $editDhcpId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editDhcpId ? __('Edit DHCP Server') : __('Add DHCP Server') }}
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="addDhcpServer">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="dhcp_name" placeholder="dhcp1">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Interface') }} <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" wire:model.defer="dhcp_interface">
                                        <option value="">{{ __('-- Select --') }}</option>
                                        @foreach($interfaces as $iface)<option value="{{ $iface }}">{{ $iface }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Address Pool') }}</label>
                                    <select class="form-select form-select-sm" wire:model.defer="dhcp_pool">
                                        <option value="static-only">static-only</option>
                                        @foreach($ipPools as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Lease Time') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="dhcp_lease" placeholder="00:10:00">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Comment') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="dhcp_comment">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                                        <i class="bi bi-save me-1"></i>{{ $editDhcpId ? __('Update') : __('Add Server') }}
                                    </button>
                                    @if($editDhcpId)
                                        <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('editDhcpId', null)">{{ __('Cancel') }}</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- DHCP Network Form --}}
                    <div class="card">
                        <div class="card-header {{ $editNetId ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                            <i class="bi bi-{{ $editNetId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editNetId ? __('Edit DHCP Network') : __('Add DHCP Network') }}
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="addDhcpNetwork">
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Basis (From IP Pool)') }}</label>
                                    <select class="form-select form-select-sm" wire:model.live="net_pool">
                                        <option value="">{{ __('-- No Pool / Manual --') }}</option>
                                        @foreach($ipPools as $p)<option value="{{ $p['name'] }}">{{ $p['name'] }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Address (CIDR)') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('net_address') is-invalid @enderror" wire:model.defer="net_address" placeholder="192.168.1.0/24">
                                    @error('net_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('Gateway') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="net_gateway" placeholder="192.168.1.1">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">{{ __('DNS Servers') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="net_dns" placeholder="8.8.8.8,1.1.1.1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('Comment') }}</label>
                                    <input type="text" class="form-control form-control-sm" wire:model.defer="net_comment">
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-sm btn-info text-dark flex-fill">
                                        <i class="bi bi-save me-1"></i>{{ $editNetId ? __('Update') : __('Add Network') }}
                                    </button>
                                    @if($editNetId)
                                        <button type="button" class="btn btn-sm btn-secondary" wire:click="$set('editNetId', null)">{{ __('Cancel') }}</button>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    {{-- DHCP Servers Table --}}
                    <div class="card mb-3">
                        <div class="card-header"><i class="bi bi-server me-1"></i>{!! __('DHCP Servers on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                        <div class="card-body p-0">
                            <div class="table-responsive" wire:key="container-dhcp-srv-{{ $selectedRouter }}">
                                <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-dhcp-servers">
                                    <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Interface') }}</th><th>{{ __('Pool') }}</th><th>{{ __('Lease') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                                    <tbody>
                                        @forelse($dhcpServers as $srv)
                                        @php $isArr = is_array($srv); @endphp
                                        <tr wire:key="row-dhcp-srv-{{ $loop->index }}-{{ md5($isArr ? ($srv['name'] ?? $loop->index) : $srv) }}">
                                            <td><strong>{{ ($isArr ? ($srv['name'] ?? '-') : $srv) }}</strong></td>
                                            <td>{{ $isArr ? ($srv['interface'] ?? '-') : '-' }}</td>
                                            <td><code>{{ $isArr ? ($srv['address-pool'] ?? '-') : '-' }}</code></td>
                                            <td>{{ $isArr ? ($srv['lease-time'] ?? '-') : '-' }}</td>
                                            <td><small class="text-muted">{{ $isArr ? ($srv['comment'] ?? '') : '' }}</small></td>
                                            <td>
                                                <div class="form-check form-switch p-0 m-0 d-flex justify-content-center">
                                                    <input class="form-check-input ms-0" type="checkbox" role="switch" @checked(($isArr ? ($srv['disabled'] ?? 'false') : 'false') === 'false') wire:click="toggleDhcpServer('{{ $isArr ? ($srv['name'] ?? '') : '' }}', {{ ($isArr ? ($srv['disabled'] ?? 'false') : 'false') === 'true' ? 'true' : 'false' }})">
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" wire:click="editDhcpServer({{ json_encode($isArr ? $srv : ['name' => $srv]) }})"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-danger btn-sm" wire:click="removeDhcpServer('{{ $isArr ? ($srv['name'] ?? '') : $srv }}')" wire:confirm="{{ __('Remove DHCP Server :name?', ['name' => $isArr ? ($srv['name'] ?? '') : $srv]) }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No DHCP servers found.') }}</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- DHCP Networks Table --}}
                    <div class="card">
                        <div class="card-header"><i class="bi bi-grid-3x3 me-1"></i>{!! __('DHCP Networks on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                        <div class="card-body p-0">
                            <div class="table-responsive" wire:key="container-dhcp-net-{{ $selectedRouter }}">
                                <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-dhcp-networks">
                                    <thead class="table-light"><tr><th>{{ __('Address') }}</th><th>{{ __('Gateway') }}</th><th>{{ __('DNS Servers') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Actions') }}</th></tr></thead>
                                    <tbody>
                                        @forelse($dhcpNetworks as $net)
                                        @php $isN = is_array($net); @endphp
                                        <tr wire:key="row-dhcp-net-{{ $loop->index }}-{{ md5($isN ? ($net['address'] ?? $loop->index) : $net) }}">
                                            <td><code>{{ ($isN ? $net['address'] : $net) ?? '-' }}</code></td>
                                            <td><code>{{ $isN ? ($net['gateway'] ?? '-') : '-' }}</code></td>
                                            <td><small>{{ $isN ? ($net['dns-server'] ?? '-') : '-' }}</small></td>
                                            <td><small class="text-muted">{{ $isN ? ($net['comment'] ?? '') : '' }}</small></td>
                                            <td>
                                                <button class="btn btn-warning btn-sm" wire:click="editDhcpNetwork({{ json_encode($isN ? $net : ['address' => $net]) }})"><i class="bi bi-pencil-square"></i></button>
                                                <button class="btn btn-danger btn-sm" wire:click="removeDhcpNetwork('{{ $isN ? ($net['address'] ?? '') : $net }}')" wire:confirm="{{ __('Remove DHCP Network :name?', ['name' => $isN ? ($net['address'] ?? '') : $net]) }}"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No DHCP networks found.') }}</td></tr>
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
