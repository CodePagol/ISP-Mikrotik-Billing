<div class="zoom-in">
    <x-slot name="header">
        {{ __('MikroTik Backup Manager') }}
    </x-slot>

    <div class="row g-2 justify-content-center position-relative">
        
        <!-- Loading Overlay -->
        <div wire:loading wire:target="pollLogs, createBackup, deleteBackup, restoreBackup, updatedSelectedRouter" class="position-absolute w-100 h-100 z-3 rounded" style="background: rgba(255,255,255,0.7); top:0; left:0; backdrop-filter: blur(2px);">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-primary shadow-sm" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Executing Action...</span>
                </div>
            </div>
        </div>

        <div class="col-12" style="z-index: 2;">
            <x-mikrotik.section-form>
                <x-slot name="title">{{ __('RouterOS Snapshots') }}</x-slot>
                <x-slot name="aside">
                    
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body bg-light border-bottom d-flex flex-wrap gap-3 align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <label class="form-label mb-1 fw-bold text-secondary">{{ __('Target Router') }}</label>
                                    <select wire:model.live="selectedRouter" class="form-select bg-white shadow-sm border-secondary-subtle font-monospace text-primary">
                                        <option value="">{{ __('Select a router...') }}</option>
                                        @foreach($this->routers() as $rName)
                                            <option value="{{ $rName }}">{{ $rName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-4">
                                    <button wire:click="fetchBackups" class="btn btn-outline-secondary shadow-sm">
                                        <i class="bi bi-arrow-clockwise"></i> {{ __('Refresh List') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" wire:click="createBackup" class="btn btn-success px-4 fw-bold shadow-sm" {{ empty($selectedRouter) ? 'disabled' : '' }}>
                                    <i class="bi bi-plus-circle me-1"></i> {{ __('Create Native Backup') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 py-3">{{ __('Snapshot (Point-in-Time)') }}</th>
                                        <th class="py-3">{{ __('Hardware Backup (.backup)') }}</th>
                                        <th class="py-3">{{ __('Server Mirror (.rsc)') }}</th>
                                        <th class="pe-3 py-3 text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($backups as $snap)
                                        <tr class="align-middle border-bottom">
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse(str_replace('_', '', $snap['timestamp']))->format('M d, Y') }}</div>
                                                <div class="text-muted" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse(str_replace('_', '', $snap['timestamp']))->format('h:i:s A') }}</div>
                                            </td>
                                            <td>
                                                @if($snap['remote'])
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-cpu text-secondary fs-5"></i>
                                                        <div>
                                                            <div class="fw-medium text-primary font-monospace" style="font-size: 0.85rem;">{{ $snap['remote']['name'] }}</div>
                                                            <div class="text-muted small">{{ $snap['remote']['size'] }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted italic small"><i class="bi bi-x-circle me-1"></i> {{ __('Not on Router') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($snap['local'])
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-pc-display text-success fs-5"></i>
                                                        <div>
                                                            <div class="fw-medium text-success font-monospace" style="font-size: 0.85rem;">{{ $snap['local']['name'] }}</div>
                                                            <div class="text-muted small">{{ $snap['local']['size'] }}</div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted italic small"><i class="bi bi-x-circle me-1"></i> {{ __('No Mirror') }}</span>
                                                @endif
                                            </td>
                                            <td class="pe-3 text-end">
                                                <div class="btn-group shadow-sm">
                                                    @if($snap['local'])
                                                        <button type="button" wire:click="downloadBackupFile('{{ $snap['local']['name'] }}')" class="btn btn-sm btn-success" title="Download Text Config">
                                                            <i class="bi bi-download"></i> {{ __('Mirror') }}
                                                        </button>
                                                    @endif

                                                    @if($snap['remote'])
                                                        <button type="button" wire:click="restoreBackup('{{ $snap['remote']['name'] }}')" 
                                                                wire:confirm="{{ __('WARNING: Load config and reboot router :router?', ['router' => $selectedRouter]) }}"
                                                                class="btn btn-sm btn-outline-danger" title="Restore From Router">
                                                            <i class="bi bi-arrow-90deg-up"></i> {{ __('Restore') }}
                                                        </button>
                                                    @endif
                                                    
                                                    @php 
                                                        $delName = $snap['remote']['name'] ?? $snap['local']['name'];
                                                        $delId = $snap['remote']['id'] ?? $snap['local']['name'];
                                                     @endphp
                                                    <button type="button" wire:click="deleteBackup('{{ $delId }}', '{{ $delName }}')" 
                                                            wire:confirm="{{ __('Delete this snapshot (Remote and Local Mirror)?') }}"
                                                            class="btn btn-sm btn-outline-secondary" title="Delete Snapshot">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-5 text-center text-muted">
                                                <i class="bi bi-focus d-block fs-1 mb-2 opacity-25"></i>
                                                @if(empty($selectedRouter))
                                                    {{ __('Please select a router to view its available configuration snapshots.') }}
                                                @else
                                                    {{ __('No configuration snapshots found!') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-slot>
            </x-mikrotik.section-form>
        </div>
    </div>
</div>
