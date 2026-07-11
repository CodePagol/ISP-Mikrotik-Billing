<div class="zoom-in">
    <x-slot name="header">{{ __('Queue Setup') }}</x-slot>

    <div class="d-flex align-items-center gap-2 mb-3">
        <i class="bi bi-speedometer text-primary fs-5"></i>
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
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>{{ __('Select a connected router to manage queues.') }}</div>
    @else
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link {{ $activeTab==='simple'?'active':'' }}" wire:click="$set('activeTab','simple')"><i class="bi bi-sliders me-1"></i>{{ __('Simple Queue') }} <span class="badge bg-secondary ms-1">{{ count($simpleQueues) }}</span></button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='tree'?'active':'' }}" wire:click="$set('activeTab','tree')"><i class="bi bi-diagram-3 me-1"></i>{{ __('Queue Tree') }} <span class="badge bg-secondary ms-1">{{ count($queueTree) }}</span></button></li>
        <li class="nav-item"><button class="nav-link {{ $activeTab==='types'?'active':'' }}" wire:click="$set('activeTab','types')"><i class="bi bi-list-ul me-1"></i>{{ __('Queue Types') }}</button></li>
    </ul>

    {{-- SIMPLE QUEUE --}}
    @if($activeTab==='simple')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header {{ $editSimpleId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editSimpleId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editSimpleId ? __('Edit Simple Queue') : __('Add Simple Queue') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addSimpleQueue">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('sq_name') is-invalid @enderror" wire:model.defer="sq_name" placeholder="{{ __('customer-01') }}">
                            @error('sq_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{!! __('Target :info', ['info' => '<span class="text-danger">*</span><small class="text-muted ms-1">(' . __('IP/subnet') . ')</small>']) !!}</label>
                            <input type="text" class="form-control form-control-sm @error('sq_target') is-invalid @enderror" wire:model.defer="sq_target" placeholder="192.168.1.10/32">
                            @error('sq_target')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{!! __('Max Limit :info', ['info' => '<span class="text-danger">*</span><small class="text-muted ms-1">(' . __('upload/download') . ')</small>']) !!}</label>
                            <input type="text" class="form-control form-control-sm @error('sq_max_limit') is-invalid @enderror" wire:model.defer="sq_max_limit" placeholder="10M/10M">
                            @error('sq_max_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="sq_comment">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" wire:loading.attr="disabled" wire:target="addSimpleQueue">
                                <span wire:loading.remove wire:target="addSimpleQueue"><i class="bi bi-{{ $editSimpleId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editSimpleId ? __('Update Queue') : __('Add Queue') }}</span>
                                <span wire:loading wire:target="addSimpleQueue"><span class="spinner-border spinner-border-sm"></span> {{ __('Saving...') }}</span>
                            </button>
                            @if($editSimpleId)
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editSimpleId', null)">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-sliders me-1"></i>{!! __('Simple Queues on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-simple-queues">
                        <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Target') }}</th><th>{{ __('Max Limit') }}</th><th>{{ __('Comment') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse($simpleQueues as $i => $q)
                            <tr wire:key="row-sq-{{ $i }}-{{ $q['name'] ?? $i }}">
                                <td><strong>{{ $q['name'] ?? '-' }}</strong></td>
                                <td><code>{{ $q['target'] ?? '-' }}</code></td>
                                <td><code class="text-danger">{{ $q['max-limit'] ?? '-' }}</code></td>
                                <td><small class="text-muted">{{ $q['comment'] ?? '' }}</small></td>
                                <td>
                                    @if(($q['disabled'] ?? 'false') === 'false')
                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Disabled') }}</span>
                                    @endif
                                </td>
                                <td class="d-flex gap-1">
                                    <div class="btn-group me-1">
                                        <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveUp('simple', {{ $i }})" {{ $loop->first ? 'disabled' : '' }}><i class="bi bi-caret-up-fill"></i></button>
                                        <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveDown('simple', {{ $i }})" {{ $loop->last ? 'disabled' : '' }}><i class="bi bi-caret-down-fill"></i></button>
                                    </div>
                                    <button class="btn btn-warning btn-sm" wire:click="editSimpleQueue({{ json_encode($q) }})"><i class="bi bi-pencil-square"></i></button>
                                    @if(($q['disabled'] ?? 'false') === 'false')
                                        <button class="btn btn-warning btn-sm" wire:click="toggleSimpleQueue('{{ $q['name'] ?? '' }}', false)" title="{{ __('Disable') }}"><i class="bi bi-pause-fill"></i></button>
                                    @else
                                        <button class="btn btn-success btn-sm" wire:click="toggleSimpleQueue('{{ $q['name'] ?? '' }}', true)" title="{{ __('Enable') }}"><i class="bi bi-play-fill"></i></button>
                                    @endif
                                    <button class="btn btn-danger btn-sm" wire:click="removeSimpleQueue('{{ $q['name'] ?? '' }}')" wire:confirm="{{ __('Remove queue \':name\'?', ['name' => $q['name'] ?? '']) }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No simple queues found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- QUEUE TREE --}}
    @if($activeTab==='tree')
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header {{ $editTreeId ? 'bg-warning text-dark' : 'bg-primary text-white' }}"><i class="bi bi-{{ $editTreeId ? 'pencil-square' : 'plus-circle' }} me-1"></i>{{ $editTreeId ? __('Edit Queue Tree Entry') : __('Add Queue Tree Entry') }}</div>
                <div class="card-body">
                    <form wire:submit.prevent="addQueueTree">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="qt_name" placeholder="upload-total">
                            @error('qt_name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Parent') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="qt_parent" placeholder="global">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Max Limit') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="qt_max_limit" placeholder="100M">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{!! __('Limit At :info', ['info' => '<small class="text-muted">(' . __('CIR') . ')</small>']) !!}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="qt_limit_at" placeholder="10M">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">{{ __('Priority (1=highest)') }}</label>
                            <input type="number" class="form-control form-control-sm" wire:model.defer="qt_priority" min="1" max="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Comment') }}</label>
                            <input type="text" class="form-control form-control-sm" wire:model.defer="qt_comment">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" wire:loading.attr="disabled" wire:target="addQueueTree">
                                <span wire:loading.remove wire:target="addQueueTree"><i class="bi bi-{{ $editTreeId ? 'save' : 'plus-lg' }} me-1"></i>{{ $editTreeId ? __('Update Entry') : __('Add Entry') }}</span>
                                <span wire:loading wire:target="addQueueTree"><span class="spinner-border spinner-border-sm"></span> {{ __('Saving...') }}</span>
                            </button>
                            @if($editTreeId)
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('editTreeId', null)">{{ __('Cancel') }}</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-diagram-3 me-1"></i>{!! __('Queue Tree on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-queue-tree">
                        <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Parent') }}</th><th>{{ __('Max Limit') }}</th><th>{{ __('Limit At') }}</th><th>{{ __('Priority') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse($queueTree as $i => $q)
                            <tr wire:key="row-qt-{{ $i }}-{{ $q['name'] ?? $i }}">
                                <td><strong>{{ $q['name'] ?? '-' }}</strong></td>
                                <td><span class="badge bg-secondary">{{ $q['parent'] ?? '-' }}</span></td>
                                <td><code class="text-danger">{{ $q['max-limit'] ?? '-' }}</code></td>
                                <td><code>{{ $q['limit-at'] ?? '-' }}</code></td>
                                <td><span class="badge bg-primary">{{ $q['priority'] ?? '-' }}</span></td>
                                <td class="d-flex gap-1">
                                    <div class="btn-group me-1">
                                        <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveUp('tree', {{ $i }})" {{ $loop->first ? 'disabled' : '' }}><i class="bi bi-caret-up-fill"></i></button>
                                        <button class="btn btn-outline-secondary btn-sm px-1 py-0" wire:click="moveDown('tree', {{ $i }})" {{ $loop->last ? 'disabled' : '' }}><i class="bi bi-caret-down-fill"></i></button>
                                    </div>
                                    <button class="btn btn-warning btn-sm" wire:click="editQueueTree({{ json_encode($q) }})"><i class="bi bi-pencil-square"></i></button>
                                    <button class="btn btn-danger btn-sm" wire:click="removeQueueTree('{{ $q['name'] ?? '' }}')" wire:confirm="{{ __('Remove \':name\'?', ['name' => $q['name'] ?? '']) }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">{{ __('No queue tree entries found.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- QUEUE TYPES --}}
    @if($activeTab==='types')
    <div class="card">
        <div class="card-header"><i class="bi bi-list-ul me-1"></i>{!! __('Queue Types on :router', ['router' => '<strong>' . e($selectedRouter) . '</strong>']) !!}</div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 data-table" wire:key="tbl-queue-types">
                <thead class="table-light"><tr><th>{{ __('Name') }}</th><th>{{ __('Kind') }}</th><th>{{ __('Details') }}</th></tr></thead>
                <tbody>
                    @forelse($queueTypes as $t)
                    <tr wire:key="row-qtype-{{ $loop->index }}-{{ $t['name'] ?? $loop->index }}">
                        <td><strong>{{ $t['name'] ?? '-' }}</strong></td>
                        <td><span class="badge bg-info text-dark">{{ $t['kind'] ?? '-' }}</span></td>
                        <td><small class="text-muted">{{ collect($t)->except(['name','kind'])->map(fn($v,$k)=>"$k=$v")->implode(', ') }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">{{ __('No queue types found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
