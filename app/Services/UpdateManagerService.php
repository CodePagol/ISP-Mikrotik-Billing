<?php

namespace App\Services;

use Exception;
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

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($slug) {
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
     * Perform Core System Update
     */
    public function performCoreUpdate(): array
    {
        $outputLogs = [];
        $baseDir = base_path();

        try {
            // 1. Git fetch and pull
            $gitPull = Process::path($baseDir)->timeout(180)->run('git pull origin main 2>&1');
            $outputLogs[] = "=== Git Pull ===\n" . $gitPull->output();

            if (!$gitPull->successful()) {
                return [
                    'success' => false,
                    'output'  => implode("\n\n", $outputLogs),
                    'message' => 'Git pull failed. Please check permissions or git status.',
                ];
            }

            // 2. Run Database Migrations
            Artisan::call('migrate', ['--force' => true]);
            $outputLogs[] = "=== Migrations ===\n" . Artisan::output();

            // 3. Clear Caches
            Artisan::call('optimize:clear');
            $outputLogs[] = "=== Cache Clear ===\n" . Artisan::output();

            // Clear update check cache
            Cache::forget('core_system_update_check');

            $newVersion = $this->getCoreCurrentVersion();

            return [
                'success'     => true,
                'output'      => implode("\n\n", $outputLogs),
                'new_version' => $newVersion,
                'message'     => "System updated successfully to v{$newVersion}!",
            ];
        } catch (Exception $e) {
            Log::error("[UpdateManagerService] Core update error: {$e->getMessage()}");
            return [
                'success' => false,
                'output'  => implode("\n\n", $outputLogs) . "\n\nError: " . $e->getMessage(),
                'message' => 'Update failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Perform Plugin Update via Git Pull & Migration
     */
    public function performPluginUpdate(string $slug): array
    {
        $outputLogs = [];
        $dir = base_path("plugins/{$slug}");

        if (!is_dir($dir . '/.git')) {
            return [
                'success' => false,
                'output'  => "Error: Directory plugins/{$slug} is not a Git repository.",
                'message' => "Plugin [{$slug}] is not a Git repository.",
            ];
        }

        try {
            // 1. Git Pull
            $gitPull = Process::path($dir)->timeout(180)->run('git pull origin main 2>&1');
            $outputLogs[] = "=== Git Pull [{$slug}] ===\n" . $gitPull->output();

            if (!$gitPull->successful()) {
                return [
                    'success' => false,
                    'output'  => implode("\n\n", $outputLogs),
                    'message' => 'Git pull failed for plugin.',
                ];
            }

            // 2. Run plugin database migrations if present
            if (is_dir("{$dir}/database/migrations")) {
                Artisan::call('migrate', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
                $outputLogs[] = "=== Migrations ===\n" . Artisan::output();
            }

            // 3. Clear caches
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Cache::forget("plugin_update_check_{$slug}");

            // Read new version from plugin.json
            $metaFile = "{$dir}/plugin.json";
            $newVersion = '1.0.0';
            if (file_exists($metaFile)) {
                $meta = json_decode(file_get_contents($metaFile), true) ?? [];
                $newVersion = $meta['version'] ?? '1.0.0';
            }

            return [
                'success'     => true,
                'output'      => implode("\n\n", $outputLogs),
                'new_version' => $newVersion,
                'message'     => "Plugin [{$slug}] updated successfully to v{$newVersion}!",
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
                    $latestTagObj = $tags[0];
                    $latestTag = ltrim(trim($latestTagObj['name'] ?? ''), 'v');
                    $hasUpdate = version_compare($latestTag, $currentVersion, '>');

                    return [
                        'has_update'      => $hasUpdate,
                        'current_version' => $currentVersion,
                        'latest_version'  => $latestTag ?: $currentVersion,
                        'release_name'    => "v{$latestTag}",
                        'release_date'    => 'Latest Tag',
                        'changelog_raw'   => "New release tag v{$latestTag} is available.",
                        'changelog_html'  => "<p>New release tag <strong>v{$latestTag}</strong> is available on GitHub.</p>",
                        'html_url'        => "https://github.com/{$repoSlug}/releases/tag/{$latestTagObj['name']}",
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
