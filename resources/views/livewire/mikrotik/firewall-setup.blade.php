<div class="zoom-in">
    <x-slot name="header">{{ __('Firewall Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-shield-fill text-danger fs-5"></i>
        <select class="form-select form-select-sm w-auto" wire:model.live="selectedRouter">
            <option value="">{{ __('-- Select Router --') }}</option>
            @foreach($routers as $r)<option value="{{ $r->router_name }}">{{ $r->router_name }} ({{ $r->ip_address }})</option>@endforeach
        </select>
        @if($selectedRouter)
            <button class="btn btn-sm btn-outline-secondary" wire:click="loadData">
                <span wire:loading.remove wire:target="loadData"><i class="bi bi-arrow-clockwise"></i> {{ __('Refresh') }}</span>
                <span wire:loading wire:target="loadData"><span class="spinner-border spinner-border-sm"></span></span>
            </button>
            <button class="btn btn-sm btn-outline-warning" wire:click="quickMasquerade" title="{{ __('Quickly add srcnat masquerade rule') }}">
                <i class="bi bi-lightning-fill me-1"></i>{{ __('Quick Masquerade') }}
            </button>
        @endif
    </div>

    @if(!$selectedRouter)
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router to manage firewall rules.') }}</div>
    @else
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link {{ $activeTab==='filter'?'active':'' }}" wire:click="$set('activeTab','filter')"><i class="bi bi-funnel me-1"></i>{{ __('Filter') }} <span class="badge bg-secondary ms-1">{{ count($filterRules) }}</span></button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='nat'?'active':'' }}" wire:click="$set('activeTab','nat')"><i class="bi bi-arrow-left-right me-1"></i>{{ __('NAT') }} <span class="badge bg-secondary ms-1">{{ count($natRules) }}</span></button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='mangle'?'active':'' }}" wire:click="$set('activeTab','mangle')"><i class="bi bi-wrench me-1"></i>{{ __('Mangle') }} <span class="badge bg-secondary ms-1">{{ count($mangleRules) }}</span></button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='addresslist'?'active':'' }}" wire:click="$set('activeTab','addresslist')"><i class="bi bi-list-check me-1"></i>{{ __('Address Lists') }}</button></li>
    </ul>

    @php
        $badgeClasses = [
            'accept' => 'bg-success', 'drop' => 'bg-danger', 'reject' => 'bg-warning text-dark',
            'masquerade' => 'bg-info text-dark', 'dst-nat' => 'bg-primary', 'src-nat' => 'bg-primary'
        ];
    @endphp

    {{-- FILTER --}}
    @if($activeTab==='filter')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header {{ $editFilterId ? 'bg-warning text-dark' : 'bg-danger text-white' }}"><i class="bi bi-{{ $editFilterId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editFilterId ? __('Edit Filter Rule') : __('Add Filter Rule') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addFilterRule">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('Chain') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="f_chain">
                                    <option value="input">{{ __('input') }}</option><option value="forward">{{ __('forward') }}</option><option value="output">{{ __('output') }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Action') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="f_action">
                                    <option value="accept">{{ __('Accept') }}</option><option value="drop">{{ __('drop') }}</option><option value="reject">{{ __('reject') }}</option><option value="log">{{ __('log') }}</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Protocol') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="f_protocol">
                                    <option value="">any</option><option value="tcp">tcp</option><option value="udp">udp</option><option value="icmp">icmp</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Src Address') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="f_src" placeholder="0.0.0.0/0">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Dst Address') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="f_dst">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="f_comment">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-danger btn-sm flex-fill" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="addFilterRule"><i class="bi bi-{{ $editFilterId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editFilterId ? __('Update Rule') : __('Add Rule') }}</span>
                                    <span wire:loading wire:target="addFilterRule"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Saving...') }}</span>
                                </button>
                                @if($editFilterId)
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editFilterId', null)">{{ __('Cancel') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-funnel me-1"></i>{!! __('Filter Rules on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive" wire:key="container-firewall-filter-{{ $selectedRouter }}">
                        <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-filter">
                        <thead class="table-light"><tr><th>#</th><th>{{ __('Chain') }}</th><th>{{ __('Action') }}</th><th>{{ __('Protocol') }}</th><th>{{ __('Src') }}</th><th>{{ __('Dst') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Act') }}</th></tr></thead>
                        <tbody>
                             @forelse($filterRules as $i => $rule)
                             @php $isR = is_array($rule); @endphp
                             <tr wire:key="row-filter-{{ $i }}-{{ md5(($isR ? ($rule['.id'] ?? $i) : $rule) . ($isR ? ($rule['comment'] ?? '') : '')) }}" class="{{ ($isR ? ($rule['disabled'] ?? 'false') : 'false') !== 'false' ? 'table-secondary text-muted' : '' }}">
                                 <td><small>{{ $i }}</small></td>
                                 <td><span class="badge bg-dark">{{ $isR ? ($rule['chain'] ?? '-') : '-' }}</span></td>
                                 <td><span class="badge {{ $isR ? ($badgeClasses[$rule['action'] ?? ''] ?? 'bg-secondary') : 'bg-secondary' }}">{{ $isR ? ($rule['action'] ?? '-') : '-' }}</span></td>
                                 <td><small>{{ $isR ? ($rule['protocol'] ?? 'any') : 'any' }}</small></td>
                                 <td><small><code>{{ $isR ? ($rule['src-address'] ?? 'any') : 'any' }}</code></small></td>
                                 <td><small><code>{{ $isR ? ($rule['dst-address'] ?? 'any') : 'any' }}</code></small></td>
                                 <td><small class="text-muted">{{ $isR ? ($rule['comment'] ?? '') : '' }}</small></td>
                                 <td class="d-flex gap-1">
                                     <div class="btn-group me-1">
                                         <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveUp('filter', {{ $i }})" {{ $loop->first ? 'disabled' : '' }}><i class="bi bi-caret-up-fill"></i></button>
                                         <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveDown('filter', {{ $i }})" {{ $loop->last ? 'disabled' : '' }}><i class="bi bi-caret-down-fill"></i></button>
                                     </div>
                                     <button class="btn btn-warning btn-sm" wire:click="editFilter({{ json_encode($isR ? $rule : ['id' => $i]) }})"><i class="bi bi-pencil-square"></i></button>
                                     @if(($isR ? ($rule['disabled'] ?? 'false') : 'false') !== 'false')
                                         <button class="btn btn-success btn-sm" wire:click="toggleFilter({{ $i }}, true)"><i class="bi bi-play-fill"></i></button>
                                     @else
                                         <button class="btn btn-warning btn-sm" wire:click="toggleFilter({{ $i }}, false)"><i class="bi bi-pause-fill"></i></button>
                                     @endif
                                     <button class="btn btn-danger btn-sm" wire:click="removeFilter({{ $i }})" wire:confirm="{{ __('Remove filter rule #:index?', ['index' => $i]) }}"><i class="bi bi-trash"></i></button>
                                 </td>
                             </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">{{ __('No filter rules found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- NAT --}}
    @if($activeTab==='nat')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header {{ $editNatId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editNatId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editNatId ? __('Edit NAT Rule') : __('Add NAT Rule') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addNatRule">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">{{ __('Chain') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="n_chain">
                                    <option value="srcnat">srcnat</option><option value="dstnat">dstnat</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('Action') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="n_action">
                                    <option value="masquerade">masquerade</option><option value="src-nat">src-nat</option><option value="dst-nat">dst-nat</option><option value="redirect">redirect</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Out Interface') }}</label>
                                <select class="form-select form-select-sm" wire:model.defer="n_out_interface">
                                    <option value="">{{ __('-- Any --') }}</option>
                                    @foreach($interfaces as $i)<option value="{{ $i }}">{{ $i }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Src Address') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="n_src_address" placeholder="{{ __('Leave blank for any') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('Comment') }}</label>
                                <input type="text" class="form-control form-control-sm" wire:model.defer="n_comment">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-fill" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="addNatRule"><i class="bi bi-{{ $editNatId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editNatId ? __('Update NAT Rule') : __('Add NAT Rule') }}</span>
                                    <span wire:loading wire:target="addNatRule"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Saving...') }}</span>
                                </button>
                                @if($editNatId)
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editNatId', null)">{{ __('Cancel') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-arrow-left-right me-1"></i>{!! __('NAT Rules on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive" wire:key="container-firewall-nat-{{ $selectedRouter }}">
                        <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-nat">
                        <thead class="table-light"><tr><th>#</th><th>{{ __('Chain') }}</th><th>{{ __('Action') }}</th><th>{{ __('Out Iface') }}</th><th>{{ __('Src Addr') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Act') }}</th></tr></thead>
                        <tbody>
                             @forelse($natRules as $i => $rule)
                             @php $isN = is_array($rule); @endphp
                              <tr wire:key="row-nat-{{ $i }}-{{ md5(($isN ? ($rule['.id'] ?? $i) : $rule) . ($isN ? ($rule['comment'] ?? '') : '')) }}" class="{{ ($isN ? ($rule['disabled'] ?? 'false') : 'false') !== 'false' ? 'table-secondary text-muted' : '' }}">
                                 <td><small>{{ $i }}</small></td>
                                 <td><span class="badge bg-dark">{{ $isN ? ($rule['chain'] ?? '-') : '-' }}</span></td>
                                 <td><span class="badge {{ $isN ? ($badgeClasses[$rule['action'] ?? ''] ?? 'bg-secondary') : 'bg-secondary' }}">{{ $isN ? ($rule['action'] ?? '-') : '-' }}</span></td>
                                 <td><small>{{ $isN ? ($rule['out-interface'] ?? 'any') : 'any' }}</small></td>
                                 <td><small><code>{{ $isN ? ($rule['src-address'] ?? 'any') : 'any' }}</code></small></td>
                                 <td><small class="text-muted">{{ $isN ? ($rule['comment'] ?? '') : '' }}</small></td>
                                 <td class="d-flex gap-1">
                                     <div class="btn-group me-1">
                                         <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveUp('nat', {{ $i }})" {{ $loop->first ? 'disabled' : '' }}><i class="bi bi-caret-up-fill"></i></button>
                                         <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveDown('nat', {{ $i }})" {{ $loop->last ? 'disabled' : '' }}><i class="bi bi-caret-down-fill"></i></button>
                                     </div>
                                     <button class="btn btn-warning btn-sm" wire:click="editNat({{ json_encode($isN ? $rule : ['id' => $i]) }})"><i class="bi bi-pencil-square"></i></button>
                                     @if(($isN ? ($rule['disabled'] ?? 'false') : 'false') !== 'false')
                                         <button class="btn btn-success btn-sm" wire:click="toggleNat({{ $i }}, true)"><i class="bi bi-play-fill"></i></button>
                                     @else
                                         <button class="btn btn-warning btn-sm" wire:click="toggleNat({{ $i }}, false)"><i class="bi bi-pause-fill"></i></button>
                                     @endif
                                     <button class="btn btn-danger btn-sm" wire:click="removeNat({{ $i }})" wire:confirm="{{ __('Remove NAT rule #:index?', ['index' => $i]) }}"><i class="bi bi-trash"></i></button>
                                 </td>
                             </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">{{ __('No NAT rules found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MANGLE --}}
    @if($activeTab==='mangle')
    <div class="card">
        <div class="card-header"><i class="bi bi-wrench me-1"></i>{!! __('Mangle Rules on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-mangle">
                <thead class="table-light"><tr><th>#</th><th>{{ __('Chain') }}</th><th>{{ __('Action') }}</th><th>{{ __('Passthrough') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Act') }}</th></tr></thead>
                <tbody>
                     @forelse($mangleRules as $i => $rule)
                     @php $isM = is_array($rule); @endphp
                     <tr wire:key="row-mangle-{{ $i }}-{{ md5(($isM ? ($rule['.id'] ?? $i) : $rule) . ($isM ? ($rule['comment'] ?? '') : '')) }}" class="{{ ($isM ? ($rule['disabled'] ?? 'false') : 'false') !== 'false' ? 'table-secondary text-muted' : '' }}">
                         <td><small>{{ $i }}</small></td>
                         <td><span class="badge bg-dark">{{ $isM ? ($rule['chain'] ?? '-') : '-' }}</span></td>
                         <td><span class="badge bg-info text-dark">{{ $isM ? ($rule['action'] ?? '-') : '-' }}</span></td>
                         <td>{{ $isM ? ($rule['passthrough'] ?? '-') : '-' }}</td>
                         <td><small class="text-muted">{{ $isM ? ($rule['comment'] ?? '') : '' }}</small></td>
                         <td class="d-flex gap-1">
                             @if(($isM ? ($rule['disabled'] ?? 'false') : 'false') !== 'false')
                                 <button class="btn btn-success btn-sm" wire:click="toggleMangle({{ $i }}, true)"><i class="bi bi-play-fill"></i></button>
                             @else
                                 <button class="btn btn-warning btn-sm" wire:click="toggleMangle({{ $i }}, false)"><i class="bi bi-pause-fill"></i></button>
                             @endif
                             <button class="btn btn-danger btn-sm" wire:click="removeMangle({{ $i }})" wire:confirm="{{ __('Remove mangle rule #:index?', ['index' => $i]) }}"><i class="bi bi-trash"></i></button>
                         </td>
                     </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No mangle rules found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ADDRESS LISTS --}}
    @if($activeTab==='addresslist')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header {{ $editListId ? 'bg-warning text-dark' : 'bg-info text-dark' }}"><i class="bi bi-{{ $editListId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editListId ? __('Edit Address List') : __('Add to Address List') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addToAddressList">
                        <div class="mb-2">
                            <label class="form-label">{{ __('List Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="al_list" placeholder="blocked-ips">
                            @error('al_list')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Address') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="al_address" placeholder="1.2.3.4">
                            @error('al_address')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="al_comment">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info btn-sm flex-fill text-dark" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="addToAddressList"><i class="bi bi-{{ $editListId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editListId ? __('Update Entry') : __('Add Entry') }}</span>
                                <span wire:loading wire:target="addToAddressList"><span class="spinner-border spinner-border-sm me-1"></span>{{ __('Saving...') }}</span>
                            </button>
                            @if($editListId)
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editListId', null)">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-list-check me-1"></i>{!! __('Address Lists on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive" wire:key="container-firewall-al-{{ $selectedRouter }}">
                        <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-address-list">
                        <thead class="table-light"><tr><th>{{ __('List') }}</th><th>{{ __('Address') }}</th><th>{{ __('Timeout') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse($addressLists as $i => $entry)
                            @php $isA = is_array($entry); @endphp
                            <tr wire:key="row-al-{{ $i }}-{{ md5(($isA ? ($entry['.id'] ?? $i) : $entry) . ($isA ? ($entry['comment'] ?? '') : '')) }}">
                                <td><span class="badge bg-info text-dark">{{ $isA ? ($entry['list'] ?? '-') : '-' }}</span></td>
                                <td><code>{{ ($isA ? $entry['address'] : $entry) ?? '-' }}</code></td>
                                <td><small>{{ $isA ? ($entry['timeout'] ?? '—') : '—' }}</small></td>
                                <td><small class="text-muted">{{ $isA ? ($entry['comment'] ?? '') : '' }}</small></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" wire:click="editAddressList({{ json_encode($entry) }})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-danger btn-sm"
                                        wire:click="removeFromAddressList('{{ $isA ? ($entry['list'] ?? '') : '' }}', '{{ $isA ? ($entry['address'] ?? '') : $entry }}')"
                                        wire:confirm="{{ __('Remove :address from :list?', ['address' => $isA ? ($entry['address'] ?? '') : $entry, 'list' => $isA ? ($entry['list'] ?? '') : '']) }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No address list entries found.') }}</td></tr>
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
