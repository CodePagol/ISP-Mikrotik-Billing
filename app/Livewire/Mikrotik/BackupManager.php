<?php

namespace App\Livewire\Mikrotik;

use App\Http\Controllers\MikrotikController;
use App\Models\RouterList;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class BackupManager extends Component
{
    public string $selectedRouter = '';

    public array $backups = [];

    public bool $isLoading = false;

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['mikrotik-setup'])) {
            abort(403, 'Unauthorized action.');
        }

        $this->selectedRouter = $this->routers()->first() ?? '';
        if ($this->selectedRouter) {
            $this->fetchBackups();
        }
    }

    public function updatedSelectedRouter(): void
    {
        $this->fetchBackups();
    }

    public function routers(): Collection
    {
        return RouterList::where('action', 'connected')->pluck('router_name');
    }

    public function fetchBackups(): void
    {
        $this->backups = [];
        if (! $this->selectedRouter) {
            return;
        }

        try {
            $ctrl = app(MikrotikController::class);

            // 1. Fetch REMOTE backups from the router hardware
            $remoteData = $ctrl->singleRead(
                $this->selectedRouter,
                '/file/print',
                ':put [/file print as-value where name~"\\\\.backup"]',
                ['?name' => '~.backup']
            );

            // Grouping logic for consolidation
            $snapshots = [];

            foreach ($remoteData as $item) {
                if (isset($item['name']) && str_ends_with((string) $item['name'], '.backup')) {
                    // Extract timestamp like 20260403_163159 from AutoBackup_20260403_163159
                    $nameParts = explode('_', str_replace('.backup', '', $item['name']));
                    $timestamp = count($nameParts) >= 3 ? ($nameParts[1].'_'.$nameParts[2]) : $item['name'];

                    $snapshots[$timestamp]['remote'] = [
                        'id' => $item['.id'] ?? $item['id'] ?? '',
                        'name' => $item['name'],
                        'size' => $this->formatSize($item['size'] ?? 'N/A'),
                        'time' => $item['creation-time'] ?? $item['last-modified'] ?? 'N/A',
                        'raw_time' => $item['creation-time'] ?? $item['last-modified'] ?? '0',
                    ];
                }
            }

            // 2. Fetch LOCAL mirrored configurations from the server folder
            $localDir = base_path('backups');
            if (is_dir($localDir)) {
                $localFiles = File::files($localDir);
                foreach ($localFiles as $file) {
                    $filename = $file->getFilename();
                    if (str_starts_with($filename, $this->selectedRouter) && str_ends_with($filename, '.rsc')) {
                        // Extract timestamp like 20260403_163159 from RouterName_20260403_163159.rsc
                        $nameParts = explode('_', str_replace('.rsc', '', $filename));
                        $timestamp = count($nameParts) >= 3 ? ($nameParts[1].'_'.$nameParts[2]) : $filename;

                        $snapshots[$timestamp]['local'] = [
                            'name' => $filename,
                            'size' => number_format($file->getSize() / 1024, 2).' KB',
                            'time' => date('Y-m-d H:i:s', $file->getMTime()),
                            'raw_time' => $file->getMTime(),
                        ];
                    }
                }
            }

            // Transform grouped snapshots into a sortable array
            foreach ($snapshots as $ts => $data) {
                $primaryTime = $data['local']['raw_time'] ?? ($data['remote']['raw_time'] ?? 0);
                $this->backups[] = [
                    'timestamp' => $ts,
                    'remote' => $data['remote'] ?? null,
                    'local' => $data['local'] ?? null,
                    'sort_time' => is_numeric($primaryTime) ? $primaryTime : strtotime((string) $primaryTime),
                ];
            }

            // Centralized chronological sorting for all snapshots
            usort($this->backups, function ($a, $b) {
                return $b['sort_time'] <=> $a['sort_time'];
            });

        } catch (\Exception $e) {
            flash()->error('Failed to fetch snapshots: '.$e->getMessage());
        }
    }

    protected function formatSize($size): string
    {
        if (is_numeric($size)) {
            return number_format((int) $size / 1024, 2).' KB';
        }

        return (string) $size;
    }

    public function createBackup(): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        try {
            $controller = app(MikrotikController::class);
            $result = $controller->createBackup($this->selectedRouter, 'AutoBackup');

            if (! empty($result['warnings'])) {
                foreach ($result['warnings'] as $warning) {
                    flash()->warning($warning);
                }
            }

            if ($result['success']) {
                flash()->success($result['message']);
            } else {
                flash()->error('Backup failed: '.$result['message']);
            }

            $this->fetchBackups();

        } catch (\Exception $e) {
            flash()->error('Backup failed: '.$e->getMessage());
        }
    }

    public function deleteBackup(string $id, string $name): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        try {
            $ctrl = app(MikrotikController::class);

            if (str_ends_with($name, '.rsc')) {
                // Only delete the LOCAL mirrored .rsc file — .rsc is never stored on the router
                $path = base_path('backups/'.$name);
                if (file_exists($path)) {
                    unlink($path);
                    flash()->success("Local mirror '{$name}' deleted.");
                } else {
                    flash()->warning("Local mirror '{$name}' not found on server.");
                }
            } else {
                // Delete binary .backup from router flash + its local .rsc mirror
                $ctrl->singleWrite(
                    $this->selectedRouter,
                    '/file remove [find name="'.$name.'"]'
                );
                flash()->success("Remote backup '{$name}' deleted from router.");

                // Also remove local .rsc mirror if it exists (same timestamp)
                $rscName = str_replace('AutoBackup_', $this->selectedRouter.'_', str_replace('.backup', '.rsc', $name));
                $rscPath = base_path('backups/'.$rscName);
                if (file_exists($rscPath)) {
                    unlink($rscPath);
                    flash()->success("Local mirror '{$rscName}' also deleted.");
                }
            }

            $this->fetchBackups();
        } catch (\Exception $e) {
            flash()->error('Failed to delete backup: '.$e->getMessage());
        }
    }

    public function restoreBackup(string $name): void
    {
        if (! $this->selectedRouter) {
            return;
        }

        try {
            app(MikrotikController::class)->singleWrite(
                $this->selectedRouter,
                '/system backup load name='.$name
            );

            flash()->success("Restore initiated. The router '{$this->selectedRouter}' will now reboot and apply the configuration.");
        } catch (\Exception $e) {
            flash()->error('Restore Failed: '.$e->getMessage());
        }
    }

    public function downloadBackupFile(string $name): mixed
    {
        if (str_ends_with($name, '.rsc')) {
            // Initiate browser download for the LOCAL mirrored configuration
            $path = base_path('backups/'.$name);
            if (file_exists($path)) {
                return response()->download($path);
            }
        }

        flash()->warning('Proprietary binary .backup files can only be downloaded via WinBox/FTP. Local .rsc mirrors can be downloaded normally.');

        return null;
    }

    public function render()
    {
        return view('livewire.mikrotik.backup-manager')->layout('layouts.app');
    }
}
