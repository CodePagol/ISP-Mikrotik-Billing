<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SystemLogViewer extends Component
{
    public string $selectedFile = 'laravel.log';

    public string $searchQuery = '';

    public string $selectedLevel = 'ALL';

    public int $logsLimit = 100;

    protected $queryString = [
        'selectedFile' => ['except' => 'laravel.log'],
        'selectedLevel' => ['except' => 'ALL'],
        'searchQuery' => ['except' => ''],
    ];

    public function updatedSelectedFile(): void
    {
        $this->reset(['searchQuery', 'selectedLevel']);
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedLevel(): void
    {
        $this->resetPage();
    }

    private function resetPage(): void
    {
        // No pagination component utilized, resetting filters works natively
    }

    public function clearLog(string $fileName): void
    {
        if (! Gate::allows('viewLogViewer')) {
            abort(403, 'Unauthorized.');
        }

        $filePath = storage_path('logs/'.basename($fileName));
        if (file_exists($filePath)) {
            file_put_contents($filePath, '');
            flash()->success("Successfully cleared log file: {$fileName}");
        }
    }

    public function deleteLog(string $fileName): void
    {
        if (! Gate::allows('viewLogViewer')) {
            abort(403, 'Unauthorized.');
        }

        if ($fileName === 'laravel.log') {
            flash()->error('Active laravel.log file cannot be deleted, you can only clear it.');

            return;
        }

        $filePath = storage_path('logs/'.basename($fileName));
        if (file_exists($filePath)) {
            unlink($filePath);
            $this->selectedFile = 'laravel.log';
            flash()->success("Successfully deleted log file: {$fileName}");
        }
    }

    public function getLogFilesProperty(): array
    {
        $logDir = storage_path('logs');
        if (! is_dir($logDir)) {
            return [];
        }

        $files = glob($logDir.'/*.log');
        $logFiles = [];

        foreach ($files as $file) {
            $name = basename($file);
            $logFiles[] = [
                'name' => $name,
                'size' => $this->formatBytes(filesize($file)),
                'modified' => filemtime($file),
            ];
        }

        usort($logFiles, fn ($a, $b) => $b['modified'] - $a['modified']);

        return $logFiles;
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }

    private function parseLogFile(string $filePath, ?string $levelFilter = null, ?string $searchQuery = null, int $limit = 100): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $fileSize = filesize($filePath);
        if ($fileSize === 0) {
            return [];
        }

        $file = fopen($filePath, 'r');
        if (! $file) {
            return [];
        }

        $chunkSize = 1048576; // 1MB chunks
        $position = $fileSize;
        $buffer = '';
        $logs = [];
        $currentStackTrace = [];

        while ($position > 0 && count($logs) < $limit) {
            $readSize = min($position, $chunkSize);
            $position -= $readSize;

            fseek($file, $position);
            $chunk = fread($file, $readSize);
            $buffer = $chunk.$buffer;

            $lines = explode("\n", $buffer);

            if ($position > 0) {
                $buffer = array_shift($lines);
            } else {
                $buffer = '';
            }

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if (empty($line)) {
                    continue;
                }

                if (preg_match('/^\[(?P<date>[\d\-\s:,]+)\] (?P<env>\w+)\.(?P<level>\w+): (?P<message>.*)/', $line, $matches)) {
                    $logLevel = strtoupper($matches['level']);
                    $logMessage = $matches['message'];
                    $logDate = $matches['date'];
                    $logEnv = $matches['env'];

                    if ($levelFilter && $levelFilter !== 'ALL' && strtoupper($levelFilter) !== $logLevel) {
                        $currentStackTrace = [];

                        continue;
                    }

                    if ($searchQuery && stripos($logMessage, $searchQuery) === false && stripos(implode("\n", $currentStackTrace), $searchQuery) === false) {
                        $currentStackTrace = [];

                        continue;
                    }

                    $stackTraceString = implode("\n", array_reverse($currentStackTrace));

                    $logs[] = [
                        'id' => uniqid('log_', true),
                        'date' => $logDate,
                        'env' => $logEnv,
                        'level' => $logLevel,
                        'message' => $logMessage,
                        'stack_trace' => $stackTraceString,
                    ];

                    $currentStackTrace = [];

                    if (count($logs) >= $limit) {
                        break;
                    }
                } else {
                    $currentStackTrace[] = $line;
                }
            }
        }

        fclose($file);

        return $logs;
    }

    public function render()
    {
        if (! Gate::allows('viewLogViewer')) {
            abort(403, 'Unauthorized.');
        }

        $filePath = storage_path('logs/'.basename($this->selectedFile));
        $logs = $this->parseLogFile($filePath, $this->selectedLevel, $this->searchQuery, $this->logsLimit);

        // Group counts for basic dashboard stats
        $levels = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

        return view('livewire.admin.system-log-viewer', [
            'logs' => $logs,
            'files' => $this->logFiles,
            'levels' => $levels,
        ])->layout('layouts.app');
    }
}
