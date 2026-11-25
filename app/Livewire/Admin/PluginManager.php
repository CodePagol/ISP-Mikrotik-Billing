<?php

namespace App\Livewire\Admin;

use App\Contracts\PluginContract;
use App\Services\UpdateManagerService;
use App\Support\PluginManager as PluginManagerService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

class PluginManager extends Component
{
    use WithFileUploads;

    public string $activeTab = 'installed'; // 'installed' | 'marketplace' | 'install' | 'docs'
    public string $installMethod = 'zip';   // 'zip' | 'git' | 'composer'

    // Marketplace state
    public string $marketplaceSearch = '';
    public string $marketplaceCategory = 'all';
    public ?string $installingPluginId = null;

    // 1. Upload ZIP state
    public $zipFile = null;
    public string $uploadLicenseKey = '';

    // 2. Git / GitHub state
    public string $gitUrl = '';
    public string $gitBranch = '';
    public string $gitLicenseKey = '';

    // 3. Composer package state
    public string $composerPackage = '';

    // Console output logger
    public ?string $consoleOutput = null;

    // License modal state
    public string $selectedPluginSlug = '';
    public string $licenseKeyInput = '';

    // Status notifications
    public ?string $successMessage = null;
    public ?string $errorMessage = null;

    // Update & Changelog Engine state
    public array $coreUpdateInfo = [];
    public bool $showChangelogModal = false;
    public ?array $activeChangelog = null;
    public bool $isApplyingUpdate = false;

    public function mount(): void
    {
        if (! hasAccess(['Super Admin'], ['plugin-manager', 'site-setup'])) {
            if (! auth()->user()->hasRole('Super Admin') && ! auth()->user()->can('site-setup')) {
                abort(403, 'Unauthorized access to Plugin Manager.');
            }
        }

        $this->checkUpdates(false);
    }

    public function checkUpdates(bool $force = true): void
    {
        $updateService = app(UpdateManagerService::class);
        $this->coreUpdateInfo = $updateService->checkCoreUpdate($force);
        $updateService->checkAllPluginsUpdate($force);

        if ($force) {
            $this->successMessage = __('Checked for updates successfully.');
        }
    }

    public function getPluginsProperty(): array
    {
        /** @var PluginManagerService $manager */
        $manager = app(PluginManagerService::class);
        /** @var UpdateManagerService $updateService */
        $updateService = app(UpdateManagerService::class);

        $plugins = [];
        foreach ($manager->all() as $plugin) {
            $slug = $plugin->getId();
            $metaPath = base_path("plugins/{$slug}/plugin.json");
            $meta = file_exists($metaPath) ? (json_decode(file_get_contents($metaPath), true) ?: []) : [];

            $requiresLicense = $plugin->requiresLicense();
            $licenseValid = $manager->validateLicense($plugin);
            $updateInfo = $updateService->checkPluginUpdate($slug);

            $plugins[] = [
                'id'               => $slug,
                'name'             => $plugin->getName(),
                'version'          => $plugin->getVersion(),
                'description'      => $plugin->getDescription(),
                'author'           => $plugin->getAuthor(),
                'author_url'       => $meta['author_url'] ?? null,
                'requires_license' => $requiresLicense,
                'license_valid'    => $licenseValid,
                'license_key'      => $plugin->getLicenseKey(),
                'has_migrations'   => is_dir(base_path("plugins/{$slug}/database/migrations")),
                'has_views'        => ! empty($plugin->getViewNamespaces()),
                'has_routes'       => ! empty($plugin->getRouteFiles()),
                'is_git_repo'      => is_dir(base_path("plugins/{$slug}/.git")),
                'filament_pages'   => count($plugin->getFilamentPages()),
                'update_info'      => $updateInfo,
            ];
        }

        return $plugins;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Marketplace & Catalog Engine
    // ──────────────────────────────────────────────────────────────────────────
    public function getMarketplacePluginsProperty(): array
    {
        $catalog = Cache::remember('marketplace_catalog_feed', now()->addMinutes(30), function () {
            $url = config('services.marketplace.url', 'https://raw.githubusercontent.com/CodePagol/ISP-Mikrotik-Billing/main/marketplace.json');
            try {
                $response = Http::timeout(10)->get($url . '?v=' . time());
                if ($response->successful() && is_array($response->json())) {
                    return $response->json();
                }
            } catch (\Throwable $e) {
                Log::warning("[PluginManager] Remote marketplace feed error: {$e->getMessage()}");
            }

            // Fallback to local catalog if present
            $localPath = base_path('marketplace.json');
            if (file_exists($localPath)) {
                return json_decode(file_get_contents($localPath), true) ?: [];
            }

            return [];
        });

        $enriched = [];
        foreach ($catalog as $item) {
            $slug = $item['id'] ?? '';
            if (empty($slug)) {
                continue;
            }

            $localMetaPath = base_path("plugins/{$slug}/plugin.json");
            $isInstalled = file_exists($localMetaPath);
            $installedVersion = null;
            $updateAvailable = false;

            if ($isInstalled) {
                $meta = json_decode(file_get_contents($localMetaPath), true) ?: [];
                $installedVersion = $meta['version'] ?? '1.0.0';
                $catalogVersion = $item['version'] ?? '1.0.0';
                if (version_compare($catalogVersion, $installedVersion, '>')) {
                    $updateAvailable = true;
                }
            }

            $item['is_installed'] = $isInstalled;
            $item['installed_version'] = $installedVersion;
            $item['update_available'] = $updateAvailable;

            // Search filter
            if (! empty($this->marketplaceSearch)) {
                $q = strtolower(trim($this->marketplaceSearch));
                $matchName = str_contains(strtolower($item['name'] ?? ''), $q);
                $matchDesc = str_contains(strtolower($item['description'] ?? ''), $q);
                $matchTags = in_array($q, array_map('strtolower', $item['tags'] ?? []));
                if (! $matchName && ! $matchDesc && ! $matchTags) {
                    continue;
                }
            }

            // Category filter
            if ($this->marketplaceCategory !== 'all') {
                if (empty($item['category']) || strtolower($item['category']) !== strtolower($this->marketplaceCategory)) {
                    continue;
                }
            }

            $enriched[] = $item;
        }

        return $enriched;
    }

    public function refreshMarketplace(): void
    {
        Cache::forget('marketplace_catalog_feed');
        $this->successMessage = __('Marketplace catalog refreshed successfully.');
    }

    public function installFromMarketplace(string $pluginId): void
    {
        $this->resetMessages();
        $this->installingPluginId = $pluginId;

        $target = null;
        foreach ($this->marketplacePlugins as $item) {
            if (($item['id'] ?? '') === $pluginId) {
                $target = $item;
                break;
            }
        }

        if (! $target) {
            $this->errorMessage = "Plugin [{$pluginId}] not found in marketplace catalog.";
            $this->installingPluginId = null;
            return;
        }

        $downloadUrl = $target['download_url'] ?? null;
        if (empty($downloadUrl)) {
            $this->errorMessage = "Direct download URL not available for plugin [{$pluginId}].";
            $this->installingPluginId = null;
            return;
        }

        try {
            $tempZip = storage_path('app/temp-plugins/' . uniqid('marketplace_') . '.zip');
            if (! is_dir(dirname($tempZip))) {
                mkdir(dirname($tempZip), 0755, true);
            }

            // Download ZIP package
            $response = Http::timeout(180)->withHeaders([
                'User-Agent' => 'ISP-Mikrotik-Billing-Marketplace-Installer',
            ])->get($downloadUrl);

            if (! $response->successful()) {
                $this->errorMessage = "Failed to download plugin package: HTTP " . $response->status();
                $this->installingPluginId = null;
                return;
            }

            file_put_contents($tempZip, $response->body());

            // Extract and install using native safe extractor
            [$success, $message] = $this->extractAndInstallZip($tempZip, null);

            if (file_exists($tempZip)) {
                @unlink($tempZip);
            }

            if ($success) {
                Cache::forget('marketplace_catalog_feed');
                $pluginName = $target['name'] ?? $pluginId;
                $this->successMessage = "Plugin [{$pluginName}] installed and activated successfully from Marketplace!";
            } else {
                $this->errorMessage = $message;
            }
        } catch (\Throwable $e) {
            Log::error("[PluginManager] Marketplace installation error [{$pluginId}]: {$e->getMessage()}");
            $this->errorMessage = "Installation failed: " . $e->getMessage();
        } finally {
            $this->installingPluginId = null;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 1. Install via ZIP
    // ──────────────────────────────────────────────────────────────────────────
    public function installZip(): void
    {
        $this->validate([
            'zipFile' => 'required|file|mimes:zip|max:20480',
        ]);

        $this->resetMessages();

        $realPath = $this->zipFile->getRealPath();
        [$success, $message] = $this->extractAndInstallZip($realPath, $this->uploadLicenseKey ?: null);

        if ($success) {
            $this->successMessage = $message;
            $this->reset(['zipFile', 'uploadLicenseKey']);
            $this->activeTab = 'installed';
        } else {
            $this->errorMessage = $message;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 2. Install via Git / GitHub Clone
    // ──────────────────────────────────────────────────────────────────────────
    public function installFromGit(): void
    {
        $this->validate([
            'gitUrl' => 'required|string',
        ]);

        $this->resetMessages();
        $this->consoleOutput = null;

        $url = trim($this->gitUrl);

        // Basic security check on URL to prevent command injection
        if (! preg_match('/^(https?:\/\/|git@)[\w\.\-\/@:]+(\.git)?$/', $url)) {
            $this->errorMessage = 'Invalid Git URL format. Must be a valid HTTP(S) or Git repository URL.';
            return;
        }

        $branch = trim($this->gitBranch);
        if ($branch && ! preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $branch)) {
            $this->errorMessage = 'Invalid branch name.';
            return;
        }

        $tempDir = storage_path('app/temp-plugins/' . uniqid('git_'));
        if (! is_dir(dirname($tempDir))) {
            mkdir(dirname($tempDir), 0755, true);
        }

        $branchCmd = $branch ? " -b " . escapeshellarg($branch) : "";
        $cmd = "git clone --depth=1 {$branchCmd} " . escapeshellarg($url) . " " . escapeshellarg($tempDir) . " 2>&1";

        $result = Process::path(base_path())->timeout(180)->run($cmd);
        $output = $result->output();
        $this->consoleOutput = $output;

        if (! $result->successful() || ! is_dir($tempDir)) {
            $this->errorMessage = "Git clone failed: " . ($output ?: 'Unknown error. Check Git URL and permissions.');
            File::deleteDirectory($tempDir);
            return;
        }

        // Validate plugin structure
        $hasPlugin = file_exists($tempDir . '/Plugin.php');
        $hasMeta   = file_exists($tempDir . '/plugin.json');

        if (! $hasPlugin || ! $hasMeta) {
            File::deleteDirectory($tempDir);
            $this->errorMessage = 'Invalid plugin repository! The repository root must contain both "Plugin.php" and "plugin.json".';
            return;
        }

        $meta = json_decode(file_get_contents($tempDir . '/plugin.json'), true);
        if (empty($meta['id'])) {
            File::deleteDirectory($tempDir);
            $this->errorMessage = 'plugin.json is missing the required "id" field.';
            return;
        }

        $slug        = $meta['id'];
        $destination = base_path("plugins/{$slug}");

        if (is_dir($destination)) {
            File::deleteDirectory($destination);
        }

        // Move cloned folder to destination
        File::moveDirectory($tempDir, $destination);

        // Store license key if provided
        if (! empty($this->gitLicenseKey)) {
            $this->storeLicenseKey($slug, trim($this->gitLicenseKey));
        }

        // Run migrations if present
        $migrationsPath = $destination . '/database/migrations';
        if (is_dir($migrationsPath)) {
            try {
                Artisan::call('migrate', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                Log::error("[PluginManager] Migration error after git clone [{$slug}]: {$e->getMessage()}");
            }
        }

        $pluginName    = $meta['name'] ?? $slug;
        $pluginVersion = $meta['version'] ?? '1.0.0';

        $this->successMessage = "Plugin [{$pluginName}] v{$pluginVersion} successfully cloned and installed from Git!";
        $this->reset(['gitUrl', 'gitBranch', 'gitLicenseKey']);
        $this->activeTab = 'installed';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. Install via Composer Require
    // ──────────────────────────────────────────────────────────────────────────
    public function installFromComposer(): void
    {
        $this->validate([
            'composerPackage' => 'required|string',
        ]);

        $this->resetMessages();
        $this->consoleOutput = null;

        $package = trim($this->composerPackage);

        // Validate package name (vendor/package or vendor/package:^1.0)
        if (! preg_match('/^[a-z0-9\-_.]+\/[a-z0-9\-_.]+(:\S+)?$/i', $package)) {
            $this->errorMessage = 'Invalid Composer package format. Example: vendor/package or vendor/package:^1.0';
            return;
        }

        // Run composer require
        $cmd = "composer require " . escapeshellarg($package) . " --no-interaction 2>&1";
        $result = Process::path(base_path())->timeout(300)->run($cmd);
        $output = $result->output();
        $this->consoleOutput = $output;

        if ($result->successful()) {
            $this->successMessage = "Composer package [{$package}] installed successfully!";
            $this->reset(['composerPackage']);
            $this->activeTab = 'installed';
        } else {
            $this->errorMessage = "Composer require failed. Please check the console output below.";
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. Git Pull / Update existing plugin
    // ──────────────────────────────────────────────────────────────────────────
    public function pullGitUpdate(string $slug): void
    {
        $this->resetMessages();
        $this->consoleOutput = null;

        $dir = base_path("plugins/{$slug}");
        if (! is_dir($dir . '/.git')) {
            $this->errorMessage = "Plugin [{$slug}] is not a Git repository.";
            return;
        }

        $result = Process::path($dir)->timeout(120)->run('git pull 2>&1');
        $output = $result->output();
        $this->consoleOutput = $output;

        if ($result->successful()) {
            // Run migrations if updated
            if (is_dir($dir . '/database/migrations')) {
                Artisan::call('migrate', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
            }
            $this->successMessage = "Git update completed for [{$slug}]: " . trim($output);
        } else {
            $this->errorMessage = "Git pull failed: " . trim($output);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 5. GitHub Release & Changelog Update Engine
    // ──────────────────────────────────────────────────────────────────────────
    public function openPluginChangelog(string $slug): void
    {
        $updateService = app(UpdateManagerService::class);
        $updateInfo = $updateService->checkPluginUpdate($slug);

        $manager = app(PluginManagerService::class);
        $plugin  = $manager->get($slug);

        $this->activeChangelog = [
            'type'            => 'plugin',
            'slug'            => $slug,
            'name'            => $plugin ? $plugin->getName() : $slug,
            'current_version' => $updateInfo['current_version'] ?? '1.0.0',
            'latest_version'  => $updateInfo['latest_version'] ?? '1.0.0',
            'release_name'    => $updateInfo['release_name'] ?? 'Latest Release',
            'release_date'    => $updateInfo['release_date'] ?? '-',
            'changelog_html'  => $updateInfo['changelog_html'] ?? '<p>No changelog details provided.</p>',
            'html_url'        => $updateInfo['html_url'] ?? '',
        ];

        $this->showChangelogModal = true;
    }

    public function openCoreChangelog(): void
    {
        $updateService = app(UpdateManagerService::class);
        $updateInfo = $this->coreUpdateInfo ?: $updateService->checkCoreUpdate();

        $this->activeChangelog = [
            'type'            => 'core',
            'slug'            => 'core',
            'name'            => 'ISP Mikrotik Billing Core',
            'current_version' => $updateInfo['current_version'] ?? '2.0.0',
            'latest_version'  => $updateInfo['latest_version'] ?? '2.0.0',
            'release_name'    => $updateInfo['release_name'] ?? 'Latest Release',
            'release_date'    => $updateInfo['release_date'] ?? '-',
            'changelog_html'  => $updateInfo['changelog_html'] ?? '<p>No changelog details provided.</p>',
            'html_url'        => $updateInfo['html_url'] ?? '',
        ];

        $this->showChangelogModal = true;
    }

    public function closeChangelogModal(): void
    {
        $this->showChangelogModal = false;
        $this->activeChangelog = null;
        $this->isApplyingUpdate = false;
    }

    public function applyUpdate(): void
    {
        if (empty($this->activeChangelog)) {
            return;
        }

        $this->resetMessages();
        $this->isApplyingUpdate = true;
        $updateService = app(UpdateManagerService::class);

        if ($this->activeChangelog['type'] === 'core') {
            $result = $updateService->performCoreUpdate();
            $this->consoleOutput = $result['output'] ?? '';

            if ($result['success']) {
                $this->successMessage = $result['message'];
                $this->checkUpdates(true);
            } else {
                $this->errorMessage = $result['message'];
            }
        } elseif ($this->activeChangelog['type'] === 'plugin') {
            $slug = $this->activeChangelog['slug'];
            $result = $updateService->performPluginUpdate($slug);
            $this->consoleOutput = $result['output'] ?? '';

            if ($result['success']) {
                $this->successMessage = $result['message'];
                $this->checkUpdates(true);
            } else {
                $this->errorMessage = $result['message'];
            }
        }

        $this->closeChangelogModal();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // License Key Management
    // ──────────────────────────────────────────────────────────────────────────
    public function openLicenseModal(string $slug): void
    {
        $this->selectedPluginSlug = $slug;
        $manager = app(PluginManagerService::class);
        $plugin  = $manager->get($slug);
        $this->licenseKeyInput = $plugin ? ($plugin->getLicenseKey() ?? '') : '';
    }

    public function saveLicenseKey(): void
    {
        if (empty($this->selectedPluginSlug)) {
            return;
        }

        $key = trim($this->licenseKeyInput);
        $this->storeLicenseKey($this->selectedPluginSlug, $key);

        cache()->forget('plugin_license_' . $this->selectedPluginSlug . '_' . md5($key));

        $this->successMessage = "License key for [{$this->selectedPluginSlug}] saved successfully.";
        $this->selectedPluginSlug = '';
        $this->licenseKeyInput = '';
    }

    public function runMigrations(string $slug): void
    {
        $this->resetMessages();

        $path = "plugins/{$slug}/database/migrations";
        if (! is_dir(base_path($path))) {
            $this->errorMessage = "No database migrations found for [{$slug}].";
            return;
        }

        try {
            Artisan::call('migrate', [
                '--path'  => $path,
                '--force' => true,
            ]);
            $output = trim(Artisan::output());
            $this->successMessage = "Migrations for [{$slug}] completed: " . ($output ?: 'Nothing to migrate.');
        } catch (\Throwable $e) {
            $this->errorMessage = "Migration failed: " . $e->getMessage();
        }
    }

    public function uninstallPlugin(string $slug): void
    {
        $this->resetMessages();

        /** @var PluginManagerService $manager */
        $manager = app(PluginManagerService::class);
        $plugin = $manager->get($slug);

        if ($plugin) {
            try {
                $plugin->uninstall();
            } catch (\Throwable $e) {
                Log::warning("[PluginManager] Uninstall callback failed for [{$slug}]: {$e->getMessage()}");
            }
        }

        $dir = base_path("plugins/{$slug}");
        if (is_dir($dir)) {
            File::deleteDirectory($dir);
            $this->successMessage = "Plugin [{$slug}] has been removed successfully.";
        } else {
            $this->errorMessage = "Plugin directory for [{$slug}] not found.";
        }
    }

    protected function extractAndInstallZip(string $zipPath, ?string $licenseKey = null): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [false, 'PHP ZipArchive extension is not installed on this server.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return [false, 'Could not open ZIP file. It may be corrupted.'];
        }

        $hasPlugin = false;
        $hasMeta = false;
        $pluginJsonRaw = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '/Plugin.php') || $name === 'Plugin.php') {
                $hasPlugin = true;
            }
            if (str_ends_with($name, '/plugin.json') || $name === 'plugin.json') {
                $hasMeta = true;
                $pluginJsonRaw = $zip->getFromIndex($i);
            }
        }

        if (! $hasPlugin || ! $hasMeta) {
            $zip->close();
            return [false, 'Invalid plugin package. The ZIP must contain Plugin.php and plugin.json.'];
        }

        $meta = json_decode($pluginJsonRaw, true);
        if (empty($meta['id'])) {
            $zip->close();
            return [false, 'plugin.json is missing the required "id" field.'];
        }

        $slug = preg_replace('/[^a-zA-Z0-9_\-]/', '', $meta['id']);
        if (empty($slug)) {
            $zip->close();
            return [false, 'plugin.json has an invalid "id" format.'];
        }

        $destination = base_path("plugins/{$slug}");

        $topLevelFolder = '';
        $firstName = $zip->getNameIndex(0);
        if (str_contains($firstName, '/')) {
            $topLevelFolder = explode('/', $firstName)[0] . '/';
        }

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $relativePath = $topLevelFolder ? substr($name, strlen($topLevelFolder)) : $name;

            // Prevent Zip Slip / path traversal
            if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/') || str_starts_with($relativePath, '\\')) {
                continue;
            }

            if ($relativePath === '' || str_ends_with($relativePath, '/')) {
                $dir = $destination . '/' . $relativePath;
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                continue;
            }

            $targetPath = $destination . '/' . $relativePath;
            $targetDir  = dirname($targetPath);
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            file_put_contents($targetPath, $zip->getFromIndex($i));
        }

        $zip->close();

        if ($licenseKey) {
            $this->storeLicenseKey($slug, $licenseKey);
        }

        $migrationsPath = $destination . '/database/migrations';
        if (is_dir($migrationsPath)) {
            try {
                Artisan::call('migrate', [
                    '--path'  => "plugins/{$slug}/database/migrations",
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                Log::error("[PluginManager] Migration error for [{$slug}]: {$e->getMessage()}");
            }
        }

        $pluginName    = $meta['name'] ?? $slug;
        $pluginVersion = $meta['version'] ?? '1.0.0';

        return [true, "Plugin [{$pluginName}] v{$pluginVersion} installed successfully!"];
    }

    protected function storeLicenseKey(string $slug, string $key): void
    {
        $envKey  = 'PLUGIN_LICENSE_' . strtoupper(str_replace('-', '_', $slug));
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);
        $escaped = preg_quote($envKey, '/');

        if (preg_match("/^{$escaped}=.*/m", $content)) {
            $content = preg_replace("/^{$escaped}=.*/m", "{$envKey}=\"{$key}\"", $content);
        } else {
            $content .= "\n{$envKey}=\"{$key}\"\n";
        }

        file_put_contents($envPath, $content);
    }

    protected function resetMessages(): void
    {
        $this->successMessage = null;
        $this->errorMessage   = null;
    }

    public function render()
    {
        return view('livewire.admin.plugin-manager', [
            'plugins' => $this->plugins,
        ])->layout('layouts.app');
    }
}
