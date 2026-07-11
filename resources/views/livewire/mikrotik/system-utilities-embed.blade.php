<div class="row g-4 position-relative">
    
    <!-- Unified Loading Overlay to block the entire tab area during ANY action -->
    <div wire:loading class="position-absolute w-100 h-100 z-3 rounded" style="background: rgba(255,255,255,0.7); top:0; left:0; backdrop-filter: blur(2px);">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary shadow-sm" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">{{ __('Executing Action...') }}</span>
            </div>
        </div>
    </div>

    <div class="col-12" style="z-index: 2;">
        <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded border">
            {{ $this->cronSetupAction }}
            {{ $this->storageLinkAction }}
            {{ $this->clearCacheAction }}
            {{ $this->backupDatabaseAction }}
        </div>
    </div>

    <!-- Backups Table -->
    <div class="col-12" style="z-index: 2;">
        <div class="table-responsive border rounded bg-white shadow-sm">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3">{{ __('Backup File Snapshot') }}</th>
                        <th class="py-3">{{ __('File Size') }}</th>
                        <th class="py-3">{{ __('Date Created') }}</th>
                        <th class="pe-3 py-3 text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getBackupFiles() as $file)
                        <tr>
                            <td class="ps-3 font-monospace fw-medium text-primary">{{ $file['name'] }}</td>
                            <td>{{ $file['size'] }}</td>
                            <td>{{ $file['date'] }}</td>
                            <td class="pe-3 text-end">
                                <div class="btn-group">
                                    <button type="button" wire:loading.attr="disabled" wire:click="downloadBackupFile('{{ $file['name'] }}')" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                        <i class="heroicon-m-arrow-down-tray" style="width: 16px; height: 16px; display: inline-block;"></i> {{ __('Download') }}
                                    </button>
                                    <button type="button" wire:loading.attr="disabled" wire:click="restoreFromBackup('{{ $file['name'] }}')" 
                                             wire:confirm="{{ __('WARNING: This will immediately overwrite the entire live database with the snapshot \':name\'. This cannot be undone! Continue?', ['name' => $file['name']]) }}"
                                             class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="{{ __('Restore this Snapshot') }}">
                                        <i class="heroicon-m-arrow-up-tray" style="width: 16px; height: 16px; display: inline-block;"></i> {{ __('Restore') }}
                                    </button>
                                    <button type="button" wire:loading.attr="disabled" wire:click="deleteBackupFile('{{ $file['name'] }}')" 
                                             wire:confirm="{{ __('Permanent deletion of \':name\'. Are you sure?', ['name' => $file['name']]) }}"
                                             class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                        <i class="heroicon-m-trash" style="width: 16px; height: 16px; display: inline-block;"></i> {{ __('Delete') }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4 text-center text-muted">
                                <i class="heroicon-m-x-circle d-block fs-3 mb-2" style="width: 32px; height: 32px; margin: 0 auto;"></i>
                                {!! __('No backups have been generated yet. Click :backup to take a snapshot!', ['backup' => '<strong>' . __('Backup Database') . '</strong>']) !!}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
