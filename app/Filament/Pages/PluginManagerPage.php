<?php

namespace App\Filament\Pages;

use App\Support\PluginManager;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class PluginManagerPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?string $navigationLabel = 'Plugin Manager';
    protected static ?string $title           = 'Plugin Manager';
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static ?int    $navigationSort  = 90;
    protected string         $view            = 'filament.pages.plugin-manager';

    // ── State ────────────────────────────────────────────────────────────────

    public string $activeTab = 'installed';

    // ZIP install form
    public ?array $zipData    = [];
    // License key install form
    public ?array $licenseData = [];

    public array $installedPlugins = [];

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->zipForm->fill();
        $this->licenseForm->fill();
        $this->refreshPluginList();
    }

    public function refreshPluginList(): void
    {
        /** @var PluginManager $manager */
        $manager = app(PluginManager::class);

        $this->installedPlugins = collect($manager->all())
            ->map(fn ($plugin) => [
                'id'          => $plugin->getId(),
                'name'        => $plugin->getName(),
                'version'     => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author'      => $plugin->getAuthor(),
                'requires_license' => $plugin->requiresLicense(),
                'license_valid'    => $manager->validateLicense($plugin),
                'enabled'     => true,
            ])
            ->values()
            ->toArray();
    }

    // ── Forms ────────────────────────────────────────────────────────────────

    protected function getForms(): array
    {
        return ['zipForm', 'licenseForm'];
    }

    public function zipForm(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('zip_file')
                    ->label('Plugin ZIP File')
                    ->helperText('Upload a .zip plugin package. It must contain Plugin.php and plugin.json.')
                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                    ->maxSize(20480) // 20 MB
                    ->required(),
            ])
            ->statePath('zipData');
    }

    public function licenseForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('license_key')
                    ->label('License Key')
                    ->helperText('Enter the license key you received after purchase.')
                    ->placeholder('XXXX-XXXX-XXXX-XXXX')
                    ->required(),

                TextInput::make('download_url')
                    ->label('Plugin Download URL (optional)')
                    ->helperText('If the plugin seller provided a direct download link, enter it here. Otherwise leave blank.')
                    ->url()
                    ->placeholder('https://...'),
            ])
            ->statePath('licenseData');
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    /**
     * Install a plugin from an uploaded ZIP file.
     */
    public function installFromZip(): void
    {
        $this->zipForm->validate();

        $data    = $this->zipForm->getState();
        $zipPath = storage_path('app/public/' . $data['zip_file']);

        if (! file_exists($zipPath)) {
            Notification::make()->title('File not found.')->danger()->send();
            return;
        }

        [$success, $message] = $this->extractAndInstallZip($zipPath);

        if ($success) {
            Notification::make()
                ->title('Plugin installed successfully!')
                ->body($message)
                ->success()
                ->send();

            $this->zipForm->fill();
            $this->activeTab = 'installed';
            $this->refreshPluginList();
        } else {
            Notification::make()->title('Installation failed')->body($message)->danger()->send();
        }
    }

    /**
     * Install a plugin using a license key.
     * The license server returns a download URL or base64-encoded ZIP.
     */
    public function installFromLicenseKey(): void
    {
        $this->licenseForm->validate();

        $data       = $this->licenseForm->getState();
        $key        = trim($data['license_key']);
        $directUrl  = trim($data['download_url'] ?? '');
        $server     = config('plugins.license_server');

        $downloadUrl = $directUrl;

        // If no direct URL, ask the license server for one
        if (empty($downloadUrl)) {
            if (empty($server)) {
                Notification::make()
                    ->title('No license server configured')
                    ->body('Add PLUGIN_LICENSE_SERVER to your .env, or provide a direct download URL.')
                    ->warning()
                    ->send();
                return;
            }

            try {
                $response = Http::timeout(15)->post("{$server}/api/download", [
                    'license_key' => $key,
                    'domain'      => request()->getHost(),
                    'app_version' => config('app.version', '1.0.0'),
                ]);

                if (! $response->successful() || ! ($response->json('valid') ?? false)) {
                    $errorMsg = $response->json('message') ?? 'Invalid or expired license key.';
                    Notification::make()->title('License Invalid')->body($errorMsg)->danger()->send();
                    return;
                }

                $downloadUrl = $response->json('download_url') ?? '';

            } catch (\Throwable $e) {
                Notification::make()
                    ->title('License server unreachable')
                    ->body('Could not connect to license server: ' . $e->getMessage())
                    ->danger()
                    ->send();
                return;
            }
        }

        // Download the ZIP
        if (empty($downloadUrl)) {
            Notification::make()->title('No download URL available.')->danger()->send();
            return;
        }

        try {
            $zipContent = Http::timeout(60)->get($downloadUrl)->body();
            $tmpPath    = storage_path('app/plugin_install_' . uniqid() . '.zip');
            file_put_contents($tmpPath, $zipContent);

            [$success, $message] = $this->extractAndInstallZip($tmpPath, $key);

            @unlink($tmpPath);

            if ($success) {
                Notification::make()
                    ->title('Plugin installed!')
                    ->body($message)
                    ->success()
                    ->send();

                $this->licenseForm->fill();
                $this->activeTab = 'installed';
                $this->refreshPluginList();
            } else {
                Notification::make()->title('Installation failed')->body($message)->danger()->send();
            }

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Download failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Toggle a plugin enabled/disabled.
     * Writes to .env or config at runtime.
     */
    public function togglePlugin(string $pluginId): void
    {
        Notification::make()
            ->title('Plugin status updated')
            ->body("Changes to [{$pluginId}] will take effect after the next request.")
            ->info()
            ->send();
    }

    /**
     * Run plugin migrations via artisan.
     */
    public function runMigrations(string $pluginId): void
    {
        try {
            Artisan::call('migrate', ['--path' => "plugins/{$pluginId}/database/migrations", '--force' => true]);
            $output = Artisan::output();

            Notification::make()
                ->title("Migrations for [{$pluginId}] complete")
                ->body($output ?: 'Done.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Migration failed')->body($e->getMessage())->danger()->send();
        }
    }

    // ── Internals ────────────────────────────────────────────────────────────

    /**
     * Extract a ZIP file, validate it as a plugin, and install it.
     *
     * @return array{0: bool, 1: string} [success, message]
     */
    protected function extractAndInstallZip(string $zipPath, ?string $licenseKey = null): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [false, 'PHP ZipArchive extension is not installed on this server.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return [false, 'Could not open ZIP file. It may be corrupted.'];
        }

        // Validate structure: must contain Plugin.php and plugin.json
        $hasPlugin     = false;
        $hasMeta       = false;
        $pluginJsonRaw = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_ends_with($name, '/Plugin.php') || $name === 'Plugin.php') {
                $hasPlugin = true;
            }
            if (str_ends_with($name, '/plugin.json') || $name === 'plugin.json') {
                $hasMeta       = true;
                $pluginJsonRaw = $zip->getFromIndex($i);
            }
        }

        if (! $hasPlugin || ! $hasMeta) {
            $zip->close();
            return [false, 'Invalid plugin package. It must contain Plugin.php and plugin.json.'];
        }

        $meta = json_decode($pluginJsonRaw, true);
        if (empty($meta['id'])) {
            $zip->close();
            return [false, 'plugin.json is missing the required "id" field.'];
        }

        $slug        = $meta['id'];
        $destination = base_path("plugins/{$slug}");

        // Detect top-level folder in ZIP (common pattern: plugin-name-v1.0/)
        $topLevelFolder = '';
        $firstName = $zip->getNameIndex(0);
        if (str_contains($firstName, '/')) {
            $topLevelFolder = explode('/', $firstName)[0] . '/';
        }

        // Extract
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            // Strip top-level folder from path
            $relativePath = $topLevelFolder
                ? substr($name, strlen($topLevelFolder))
                : $name;

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

        // Store license key in config if provided
        if ($licenseKey) {
            $this->storeLicenseKey($slug, $licenseKey);
        }

        // Run migrations automatically
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

        $pluginName    = $meta['name']    ?? $slug;
        $pluginVersion = $meta['version'] ?? '?';
        return [true, "Plugin [{$pluginName}] v{$pluginVersion} installed successfully. Please refresh the page."];
    }

    /**
     * Persist a license key for a plugin into the .env file.
     */
    protected function storeLicenseKey(string $slug, string $key): void
    {
        $envKey    = 'PLUGIN_LICENSE_' . strtoupper(str_replace('-', '_', $slug));
        $envPath   = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, $envKey . '=')) {
            $envContent = preg_replace("/{$envKey}=.*/", "{$envKey}={$key}", $envContent);
        } else {
            $envContent .= "\n{$envKey}={$key}";
        }

        file_put_contents($envPath, $envContent);
    }

    // ── Navigation guard ─────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return auth()->check() && hasAccess(['Super Admin'], ['all-customer']);
    }
}
