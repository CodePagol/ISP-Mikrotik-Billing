<?php

namespace App\Support;

use App\Contracts\PluginContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PluginManager
 *
 * Discovers, registers, and boots all plugins in the plugins/ directory.
 * Plugins must implement App\Contracts\PluginContract (or extend AbstractPlugin).
 *
 * Usage:
 *   app('plugins')->all()          — all loaded plugins
 *   app('plugins')->get('my-slug') — single plugin
 *   app('plugins')->has('my-slug') — check if active
 */
class PluginManager
{
    /** @var array<string, PluginContract> */
    protected array $plugins = [];

    protected string $pluginsPath;

    public function __construct()
    {
        $this->pluginsPath = config('plugins.path', base_path('plugins'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Discovery
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Scan the plugins/ directory and load all enabled plugins.
     */
    public function discover(): void
    {
        if (! is_dir($this->pluginsPath)) {
            return;
        }

        $enabledPlugins = config('plugins.enabled', '*');
        $directories    = array_filter(glob($this->pluginsPath . '/*'), 'is_dir');

        foreach ($directories as $dir) {
            $slug = basename($dir);

            // Skip if not in the enabled list (unless '*' = all)
            if ($enabledPlugins !== '*' && ! in_array($slug, (array) $enabledPlugins)) {
                continue;
            }

            $this->loadFromDirectory($dir);
        }
    }

    /**
     * Load a single plugin from a directory path.
     */
    public function loadFromDirectory(string $dir): void
    {
        $entryFile = $dir . '/Plugin.php';
        $metaFile  = $dir . '/plugin.json';
        $slug      = basename($dir);

        if (! file_exists($entryFile)) {
            Log::warning("[PluginManager] Plugin.php not found in [{$slug}], skipping.");
            return;
        }

        $meta = [];
        if (file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true) ?? [];
        }

        try {
            // Require the Plugin.php so the class is available
            require_once $entryFile;

            // Resolve the class name: prefer plugin.json 'class' key, else auto-detect
            $className = $meta['class'] ?? $this->resolveClassName($dir);

            if (! class_exists($className)) {
                Log::warning("[PluginManager] Class [{$className}] not found for plugin [{$slug}].");
                return;
            }

            /** @var PluginContract $plugin */
            $plugin = new $className();

            if (! ($plugin instanceof PluginContract)) {
                Log::warning("[PluginManager] [{$className}] does not implement PluginContract.");
                return;
            }

            $this->plugins[$plugin->getId()] = $plugin;

        } catch (\Throwable $e) {
            Log::error("[PluginManager] Failed to load [{$slug}]: {$e->getMessage()}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Register & Boot
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Register all discovered plugins (called in ServiceProvider::register).
     */
    public function registerAll(\Illuminate\Foundation\Application $app): void
    {
        foreach ($this->plugins as $plugin) {
            try {
                // Merge config files before registering
                foreach ($plugin->getConfigFiles() as $path => $key) {
                    if (file_exists($path)) {
                        $app['config']->set(
                            $key,
                            array_merge(require $path, $app['config']->get($key, []))
                        );
                    }
                }

                $plugin->register($app);

            } catch (\Throwable $e) {
                Log::error("[PluginManager] Error registering [{$plugin->getId()}]: {$e->getMessage()}");
            }
        }
    }

    /**
     * Boot all registered plugins (called in ServiceProvider::boot).
     */
    public function bootAll(\Illuminate\Foundation\Application $app): void
    {
        foreach ($this->plugins as $plugin) {
            try {
                // Load route files
                foreach ($plugin->getRouteFiles() as $routeFile) {
                    if (file_exists($routeFile)) {
                        \Illuminate\Support\Facades\Route::middleware('web')->group($routeFile);
                    }
                }

                // Register Blade view namespaces
                foreach ($plugin->getViewNamespaces() as $namespace => $path) {
                    if (is_dir($path)) {
                        view()->addNamespace($namespace, $path);
                    }
                }

                // Register migration paths (picked up by `php artisan migrate`)
                if (config('plugins.auto_migrate', true)) {
                    foreach ($plugin->getMigrationPaths() as $migrationPath) {
                        if (is_dir($migrationPath)) {
                            $app['migrator']->path($migrationPath);
                        }
                    }
                }

                $plugin->boot($app);

            } catch (\Throwable $e) {
                Log::error("[PluginManager] Error booting [{$plugin->getId()}]: {$e->getMessage()}");
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Filament Integration
    // ──────────────────────────────────────────────────────────────────────────

    /** @return array<class-string> */
    public function getAllFilamentPages(): array
    {
        return collect($this->plugins)->flatMap(fn ($p) => $p->getFilamentPages())->toArray();
    }

    /** @return array<class-string> */
    public function getAllFilamentResources(): array
    {
        return collect($this->plugins)->flatMap(fn ($p) => $p->getFilamentResources())->toArray();
    }

    /** @return array<class-string> */
    public function getAllFilamentWidgets(): array
    {
        return collect($this->plugins)->flatMap(fn ($p) => $p->getFilamentWidgets())->toArray();
    }

    /** @return array<\Filament\Navigation\NavigationItem> */
    public function getAllNavigationItems(): array
    {
        return collect($this->plugins)->flatMap(fn ($p) => $p->getNavigationItems())->toArray();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  License Validation (optional — only for premium plugins)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Validate a plugin's license key.
     * Returns true for free plugins or when no license server is configured.
     */
    public function validateLicense(PluginContract $plugin): bool
    {
        // Free plugin — no validation needed
        if (! $plugin->requiresLicense()) {
            return true;
        }

        $key = $plugin->getLicenseKey();
        if (empty($key)) {
            Log::warning("[PluginManager] No license key set for premium plugin [{$plugin->getId()}].");
            return false;
        }

        $server = config('plugins.license_server');

        // No license server configured → honor-system mode, allow it
        if (empty($server)) {
            return true;
        }

        $graceHours = (int) config('plugins.license_grace_hours', 72);
        $cacheKey   = 'plugin_license_' . $plugin->getId() . '_' . md5($key);

        return Cache::remember($cacheKey, now()->addHours($graceHours), function () use ($plugin, $key, $server) {
            try {
                $response = Http::timeout(5)->post("{$server}/api/validate", [
                    'plugin'      => $plugin->getId(),
                    'version'     => $plugin->getVersion(),
                    'license_key' => $key,
                    'domain'      => request()->getHost(),
                ]);

                return $response->successful() && (bool) ($response->json('valid') ?? false);

            } catch (\Throwable $e) {
                // License server unreachable → fail open (grace period)
                Log::warning("[PluginManager] License server unreachable for [{$plugin->getId()}]: {$e->getMessage()}");
                return true;
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Install / Uninstall
    // ──────────────────────────────────────────────────────────────────────────

    public function install(string $pluginId): bool
    {
        $plugin = $this->get($pluginId);
        if (! $plugin) {
            return false;
        }

        try {
            $plugin->install();
            return true;
        } catch (\Throwable $e) {
            Log::error("[PluginManager] Install failed [{$pluginId}]: {$e->getMessage()}");
            return false;
        }
    }

    public function uninstall(string $pluginId): bool
    {
        $plugin = $this->get($pluginId);
        if (! $plugin) {
            return false;
        }

        try {
            $plugin->uninstall();
            return true;
        } catch (\Throwable $e) {
            Log::error("[PluginManager] Uninstall failed [{$pluginId}]: {$e->getMessage()}");
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Accessors
    // ──────────────────────────────────────────────────────────────────────────

    /** @return array<string, PluginContract> */
    public function all(): array
    {
        return $this->plugins;
    }

    public function get(string $id): ?PluginContract
    {
        return $this->plugins[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->plugins[$id]);
    }

    public function count(): int
    {
        return count($this->plugins);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Internals
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Guess the fully-qualified class name from a plugin directory.
     *
     * Convention: plugins/my-plugin/Plugin.php → Plugins\MyPlugin\Plugin
     */
    protected function resolveClassName(string $dir): string
    {
        $slug       = basename($dir);
        $studlyName = str_replace(['-', '_', ' '], '', ucwords($slug, '-_ '));

        return "Plugins\\{$studlyName}\\Plugin";
    }
}
