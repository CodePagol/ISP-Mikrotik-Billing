<?php

namespace App\Services;

use Exception;
use ZipArchive;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class UpdateManagerService
{
    protected string $userAgent = 'ISP-Mikrotik-Billing-UpdateEngine/2.0';

    /**
     * Get the current Core version from version.json (fallback: 2.0.0)
     */
    public function getCoreCurrentVersion(): string
    {
        $versionFile = base_path('version.json');
        if (file_exists($versionFile)) {
            $data = json_decode(file_get_contents($versionFile), true);
            if (!empty($data['version'])) {
                return ltrim(trim($data['version']), 'v');
            }
        }

        return config('app.version', '2.0.0');
    }

    /**
     * Get the core GitHub repository slug
     */
    public function getCoreRepository(): string
    {
        return 'CodePagol/ISP-Mikrotik-Billing';
    }

    /**
     * Check if a core system update is available on GitHub
     */
    public function checkCoreUpdate(bool $force = false): array
    {
        $cacheKey = 'core_system_update_check';
        if ($force) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addHours(6), function () {
            $currentVersion = $this->getCoreCurrentVersion();
            $repo = $this->getCoreRepository();

            return $this->fetchGitHubReleaseInfo($repo, $currentVersion);
        });
    }

    /**
     * Check if an update is available for a specific plugin
     */
    public function checkPluginUpdate(string $slug, bool $force = false): array
    {
        $cacheKey = "plugin_update_check_{$slug}";
        if ($force) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($slug) {
            $dir = base_path("plugins/{$slug}");
            $metaFile = "{$dir}/plugin.json";

            if (!file_exists($metaFile)) {
                return [
                    'has_update'      => false,
                    'current_version' => '1.0.0',
                    'error'           => 'plugin.json not found',
                ];
            }

            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
            $currentVersion = ltrim(trim($meta['version'] ?? '1.0.0'), 'v');

            // Find repository URL
            $repoUrl = $meta['repository'] ?? null;
            if (!$repoUrl && is_dir("{$dir}/.git")) {
                $gitUrlResult = Process::path($dir)->run('git config --get remote.origin.url');
                $repoUrl = trim($gitUrlResult->output());
            }

            if (!$repoUrl) {
                return [
                    'has_update'      => false,
                    'current_version' => $currentVersion,
                    'latest_version'  => $currentVersion,
                    'error'           => 'No repository URL configured',
                ];
            }

            $repoSlug = $this->extractRepoSlug($repoUrl);
            if (!$repoSlug) {
                return [
                    'has_update'      => false,
                    'current_version' => $currentVersion,
                    'latest_version'  => $currentVersion,
                    'error'           => 'Not a valid GitHub repository URL',
                ];
            }

            return $this->fetchGitHubReleaseInfo($repoSlug, $currentVersion);
        });
    }

    /**
     * Check updates for all installed plugins
     */
    public function checkAllPluginsUpdate(bool $force = false): array
    {
        $pluginsDir = base_path('plugins');
        if (!is_dir($pluginsDir)) {
            return [];
        }

        $results = [];
        $folders = File::directories($pluginsDir);
        foreach ($folders as $folder) {
            $slug = basename($folder);
            $results[$slug] = $this->checkPluginUpdate($slug, $force);
        }

        return $results;
    }

    /**
     * Check if Git CLI is installed and operable
     */
    public function isGitAvailable(): bool
    {
        try {
            $check = Process::run('git --version');
            return $check->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Perform Core System Update
     * Dual Mode: Uses Git pull (diff-only) if available, or cPanel Safe ZIP package merge fallback
     */
    public function performCoreUpdate(): array
    {
        $outputLogs = [];
        $baseDir = base_path();
        $repoSlug = $this->getCoreRepository();

        $updateInfo = $this->checkCoreUpdate();
        $latestTag = $updateInfo['latest_version'] ?? 'main';

        $usedMode = 'git';
        $canUseGit = is_dir("{$baseDir}/.git") && $this->isGitAvailable();

        if ($canUseGit) {
            try {
                $gitPull = Process::path($baseDir)->timeout(180)->run('git pull origin main 2>&1');
                $outputLogs[] = "=== Git Pull [Core System] ===\n" . $gitPull->output();

                if (!$gitPull->successful()) {
                    $outputLogs[] = "Git pull failed, falling back to direct ZIP package update...";
                    $canUseGit = false;
                }
            } catch (Exception $ge) {
                $outputLogs[] = "Git execution failed ({$ge->getMessage()}), falling back to direct ZIP package update...";
                $canUseGit = false;
            }
        }

        // If not Git or Git pull failed: Use ZIP Fallback (cPanel / Shared Hosting mode)
        if (!$canUseGit) {
            $usedMode = 'zip';
            // Exclude user-specific files during core overwrite:
            $excludes = [
                '.env',
                'storage',
                'plugins',
                'public/storage',
                '.git',
                'node_modules',
            ];
            $zipSuccess = $this->downloadAndExtractGitHubZip($repoSlug, $latestTag, $baseDir, $outputLogs, $excludes);
            if (!$zipSuccess) {
                return [
                    'success' => false,
                    'output'  => implode("\n\n", $outputLogs),
                    'message' => 'Core system update via ZIP failed.',
                ];
            }
        }

        try {
            // Run Database Migrations
            Artisan::call('migrate', ['--force' => true]);
            $outputLogs[] = "=== Migrations ===\n" . Artisan::output();

            // Clear Caches
            Artisan::call('optimize:clear');
            $outputLogs[] = "=== Cache Clear ===\n" . Artisan::output();

            // Clear update check cache
            Cache::forget('core_system_update_check');

            $newVersion = $this->getCoreCurrentVersion();

            return [
                'success'     => true,
                'output'      => implode("\n\n", $outputLogs),
                'new_version' => $newVersion,
                'mode'        => $usedMode,
                'message'     => "System updated successfully to v{$newVersion} (" . ($usedMode === 'git' ? 'Git Diff' : 'cPanel Safe ZIP Merge') . ")!",
            ];
        } catch (Exception $e) {
            Log::error("[UpdateManagerService] Core post-update error: {$e->getMessage()}");
            return [
                'success' => false,
                'output'  => implode("\n\n", $outputLogs) . "\n\nError: " . $e->getMessage(),
                'message' => 'Post-update steps failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Perform Plugin Update
     * Dual Mode: Uses Git pull (diff-only) if available, or cPanel Direct ZIP package update fallback
     */
    public function performPluginUpdate(string $slug): array
    {
        $outputLogs = [];
        $dir = base_path("plugins/{$slug}");

        if (!is_dir($dir)) {
            return [
                'success' => false,
                'output'  => "Error: Directory plugins/{$slug} not found.",
                'message' => "Plugin directory not found.",
            ];
        }

        $metaFile = "{$dir}/plugin.json";
        $meta = file_exists($metaFile) ? (json_decode(file_get_contents($metaFile), true) ?: []) : [];
        $repoUrl = $meta['repository'] ?? null;
        $repoSlug = $repoUrl ? $this->extractRepoSlug($repoUrl) : null;

        $updateInfo = $this->checkPluginUpdate($slug);
        $latestTag = $updateInfo['latest_version'] ?? 'main';

        $usedMode = 'git';
        $canUseGit = is_dir("{$dir}/.git") && $this->isGitAvailable();

        if ($canUseGit) {
            try {
                $gitPull = Process::path($dir)->timeout(180)->run('git pull origin main 2>&1');
                $outputLogs[] = "=== Git Pull [{$slug}] ===\n" . $gitPull->output();

                if (!$gitPull->successful()) {
                    $outputLogs[] = "Git pull failed, falling back to direct ZIP package update...";
                    $canUseGit = false;
                }
            } catch (Exception $ge) {
                $outputLogs[] = "Git execution failed ({$ge->getMessage()}), falling back to direct ZIP package update...";
                $canUseGit = false;
            }
        }

        // If not Git or Git pull failed: Use ZIP Fallback (cPanel / Shared Hosting mode)
        if (!$canUseGit) {
            $usedMode = 'zip';
            if (!$repoSlug) {
                return [
                    'success' => false,
                    'output'  => implode("\n\n", $outputLogs) . "\nNo valid repository URL configured in plugin.json to download ZIP archive.",
                    'message' => 'No repository URL configured for download.',
                ];
            }

            $zipSuccess = $this->downloadAndExtractGitHubZip($repoSlug, $latestTag, $dir, $outputLogs);
            if (!$zipSuccess) {
                return [
                    'success' => false,
                    'output'  => implode("\n\n", $outputLogs),
                    'message' => 'ZIP package update failed for plugin.',
                ];
            }
        }

        try {
            // Run plugin database migrations if present
            if (is_dir("{$dir}/database/migrations")) {
                Artisan::call('migrate', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
                $outputLogs[] = "=== Migrations ===\n" . Artisan::output();
            }

            // Clear caches
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Cache::forget("plugin_update_check_{$slug}");

            // Read new version from plugin.json
            $newVersion = '1.0.0';
            if (file_exists($metaFile)) {
                $freshMeta = json_decode(file_get_contents($metaFile), true) ?: [];
                $newVersion = $freshMeta['version'] ?? '1.0.0';
            }

            return [
                'success'     => true,
                'output'      => implode("\n\n", $outputLogs),
                'new_version' => $newVersion,
                'mode'        => $usedMode,
                'message'     => "Plugin [{$slug}] updated successfully to v{$newVersion} (" . ($usedMode === 'git' ? 'Git Diff' : 'cPanel ZIP Package') . ")!",
            ];
        } catch (Exception $e) {
            Log::error("[UpdateManagerService] Plugin update error for [{$slug}]: {$e->getMessage()}");
            return [
                'success' => false,
                'output'  => implode("\n\n", $outputLogs) . "\n\nError: " . $e->getMessage(),
                'message' => 'Update failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Download release ZIP archive from GitHub and extract/merge to target directory
     */
    protected function downloadAndExtractGitHubZip(string $repoSlug, string $tag, string $targetDir, array &$outputLogs, array $excludes = []): bool
    {
        $tempZip = storage_path('app/temp_update_' . uniqid() . '.zip');
        $tempExtractDir = storage_path('app/temp_extract_' . uniqid());

        try {
            $candidateUrls = [
                "https://github.com/{$repoSlug}/archive/refs/tags/v{$tag}.zip",
                "https://github.com/{$repoSlug}/archive/refs/tags/{$tag}.zip",
                "https://github.com/{$repoSlug}/archive/refs/heads/main.zip",
            ];

            $downloaded = false;
            $outputLogs[] = "=== Downloading ZIP Package from GitHub [{$repoSlug}] ===";

            foreach ($candidateUrls as $url) {
                $outputLogs[] = "Trying package URL: {$url}";
                $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                    ->timeout(180)
                    ->get($url);

                if ($response->successful() && strlen($response->body()) > 1000) {
                    File::ensureDirectoryExists(dirname($tempZip));
                    file_put_contents($tempZip, $response->body());
                    $downloaded = true;
                    $outputLogs[] = "Downloaded package successfully (" . round(strlen($response->body()) / 1024, 1) . " KB).";
                    break;
                }
            }

            if (!$downloaded || !file_exists($tempZip)) {
                $outputLogs[] = "Error: Failed to download release ZIP from GitHub.";
                return false;
            }

            // Extract with ZipArchive
            $zip = new ZipArchive();
            if ($zip->open($tempZip) !== true) {
                $outputLogs[] = "Error: Could not open downloaded ZIP archive.";
                return false;
            }

            File::ensureDirectoryExists($tempExtractDir);
            $zip->extractTo($tempExtractDir);
            $zip->close();
            @unlink($tempZip);

            // GitHub ZIP archives always contain a root directory (e.g. repo-1.0.1 or repo-main)
            $subDirs = File::directories($tempExtractDir);
            $sourceDir = !empty($subDirs) ? $subDirs[0] : $tempExtractDir;

            $outputLogs[] = "=== Extracting and Applying Update Files ===";
            $this->mergeDirectory($sourceDir, $targetDir, $excludes);

            // Clean up temp directory
            File::deleteDirectory($tempExtractDir);
            $outputLogs[] = "Update files applied successfully.";

            return true;
        } catch (Exception $e) {
            $outputLogs[] = "Error during ZIP update: " . $e->getMessage();
            @unlink($tempZip);
            if (is_dir($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
            return false;
        }
    }

    /**
     * Recursively merge files from source to destination directory with exclusions
     */
    protected function mergeDirectory(string $src, string $dst, array $excludes = []): void
    {
        File::ensureDirectoryExists($dst);
        $items = scandir($src);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $srcPath = $src . DIRECTORY_SEPARATOR . $item;
            $dstPath = $dst . DIRECTORY_SEPARATOR . $item;

            // Check if item name or relative path matches excludes
            if (in_array($item, $excludes, true)) {
                continue;
            }

            if (is_dir($srcPath)) {
                $this->mergeDirectory($srcPath, $dstPath, $excludes);
            } else {
                File::ensureDirectoryExists(dirname($dstPath));
                copy($srcPath, $dstPath);
            }
        }
    }

    /**
     * Fetch release or tag info from GitHub API
     */
    protected function fetchGitHubReleaseInfo(string $repoSlug, string $currentVersion): array
    {
        try {
            $headers = ['User-Agent' => $this->userAgent];
            $token = config('services.github.token') ?? env('GITHUB_TOKEN');
            if (!empty($token)) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            // Try releases first
            $releasesResponse = Http::withHeaders($headers)
                ->timeout(10)
                ->get("https://api.github.com/repos/{$repoSlug}/releases");

            if ($releasesResponse->successful()) {
                $releases = $releasesResponse->json();
                if (!empty($releases) && is_array($releases)) {
                    $latest = $releases[0];
                    $latestTag = ltrim(trim($latest['tag_name'] ?? ''), 'v');

                    $hasUpdate = version_compare($latestTag, $currentVersion, '>');

                    return [
                        'has_update'      => $hasUpdate,
                        'current_version' => $currentVersion,
                        'latest_version'  => $latestTag ?: $currentVersion,
                        'release_name'    => $latest['name'] ?? "Release v{$latestTag}",
                        'release_date'    => !empty($latest['published_at']) ? date('d M, Y', strtotime($latest['published_at'])) : '-',
                        'changelog_raw'   => $latest['body'] ?? 'No release notes provided.',
                        'changelog_html'  => Str::markdown($latest['body'] ?? 'No release notes provided.'),
                        'html_url'        => $latest['html_url'] ?? "https://github.com/{$repoSlug}",
                        'checked_at'      => now()->format('d M, Y h:i A'),
                    ];
                }
            }

            // Fallback to tags if releases are empty
            $tagsResponse = Http::withHeaders($headers)
                ->timeout(10)
                ->get("https://api.github.com/repos/{$repoSlug}/tags");

            if ($tagsResponse->successful()) {
                $tags = $tagsResponse->json();
                if (!empty($tags) && is_array($tags)) {
                    $tagNames = array_column($tags, 'name');
                    usort($tagNames, function ($a, $b) {
                        return version_compare(ltrim($b, 'v'), ltrim($a, 'v'));
                    });
                    $latestTagName = $tagNames[0];
                    $latestTag = ltrim(trim($latestTagName), 'v');
                    $hasUpdate = version_compare($latestTag, $currentVersion, '>');

                    // Attempt to fetch CHANGELOG.md from raw.githubusercontent.com
                    $changelogRaw = "New release tag v{$latestTag} is available on GitHub.";
                    try {
                        $rawChangelog = Http::timeout(6)->get("https://raw.githubusercontent.com/{$repoSlug}/main/CHANGELOG.md");
                        if ($rawChangelog->successful() && !empty($rawChangelog->body())) {
                            $changelogRaw = $rawChangelog->body();
                        }
                    } catch (Exception $ce) {
                        // ignore and use fallback message
                    }

                    return [
                        'has_update'      => $hasUpdate,
                        'current_version' => $currentVersion,
                        'latest_version'  => $latestTag ?: $currentVersion,
                        'release_name'    => "v{$latestTag}",
                        'release_date'    => 'Latest Tag',
                        'changelog_raw'   => $changelogRaw,
                        'changelog_html'  => Str::markdown($changelogRaw),
                        'html_url'        => "https://github.com/{$repoSlug}/releases/tag/{$latestTagName}",
                        'checked_at'      => now()->format('d M, Y h:i A'),
                    ];
                }
            }

            return [
                'has_update'      => false,
                'current_version' => $currentVersion,
                'latest_version'  => $currentVersion,
                'checked_at'      => now()->format('d M, Y h:i A'),
            ];

        } catch (Exception $e) {
            Log::warning("[UpdateManagerService] GitHub check failed for [{$repoSlug}]: {$e->getMessage()}");
            return [
                'has_update'      => false,
                'current_version' => $currentVersion,
                'latest_version'  => $currentVersion,
                'error'           => $e->getMessage(),
                'checked_at'      => now()->format('d M, Y h:i A'),
            ];
        }
    }

    /**
     * Extract owner/repo from GitHub URL
     */
    protected function extractRepoSlug(string $url): ?string
    {
        $url = trim($url);
        // Match https://github.com/owner/repo or git@github.com:owner/repo
        if (preg_match('#github\.com[:/]([a-zA-Z0-9_\-\.]+)/([a-zA-Z0-9_\-\.]+)#', $url, $matches)) {
            $owner = $matches[1];
            $repo  = preg_replace('/\.git$/', '', $matches[2]);
            return "{$owner}/{$repo}";
        }

        return null;
    }
}
