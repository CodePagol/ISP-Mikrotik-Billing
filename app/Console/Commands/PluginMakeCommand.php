<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PluginMakeCommand extends Command
{
    protected $signature   = 'plugin:make {slug : The plugin slug (e.g. my-plugin)}
                                          {--author= : Author name}
                                          {--premium : Generate a premium plugin with license check}';

    protected $description = 'Scaffold a new plugin in the plugins/ directory';

    public function handle(): int
    {
        $slug   = Str::slug($this->argument('slug'));
        $studly = str_replace(['-', '_', ' '], '', ucwords($slug, '-_ '));
        $author = $this->option('author') ?? 'Your Name';
        $isPremium = $this->option('premium');

        $pluginPath = base_path("plugins/{$slug}");

        if (is_dir($pluginPath)) {
            $this->error("Plugin [{$slug}] already exists at plugins/{$slug}/");
            return self::FAILURE;
        }

        $this->line('');
        $this->line("  🔌 Creating plugin <comment>{$slug}</comment>...");
        $this->line('');

        // Create directories
        $dirs = [
            $pluginPath,
            "{$pluginPath}/Filament/Pages",
            "{$pluginPath}/Filament/Resources",
            "{$pluginPath}/Filament/Widgets",
            "{$pluginPath}/database/migrations",
            "{$pluginPath}/routes",
            "{$pluginPath}/config",
            "{$pluginPath}/resources/views",
        ];

        foreach ($dirs as $dir) {
            mkdir($dir, 0755, true);
            $this->line("  <fg=gray>created</> {$this->relativePath($dir)}");
        }

        // plugin.json
        $this->writeFile("{$pluginPath}/plugin.json", $this->stub_pluginJson($slug, $studly, $author, $isPremium));

        // Plugin.php
        $this->writeFile("{$pluginPath}/Plugin.php", $this->stub_pluginPhp($slug, $studly, $author, $isPremium));

        // Example Filament Page
        $this->writeFile("{$pluginPath}/Filament/Pages/{$studly}Page.php", $this->stub_filamentPage($slug, $studly));

        // Example route file
        $this->writeFile("{$pluginPath}/routes/web.php", $this->stub_routes($slug));

        // Example config
        $this->writeFile("{$pluginPath}/config/{$slug}.php", $this->stub_config($slug));

        // Example view
        $this->writeFile("{$pluginPath}/resources/views/page.blade.php", $this->stub_view($studly));

        // .gitkeep in migrations
        file_put_contents("{$pluginPath}/database/migrations/.gitkeep", '');

        $this->line('');
        $this->components->info("Plugin <comment>{$slug}</comment> created successfully!");
        $this->line('');
        $this->line('  <fg=yellow>Next steps:</>');
        $this->line("  1. Edit <comment>plugins/{$slug}/Plugin.php</comment> to customise your plugin");
        $this->line("  2. Add migrations to <comment>plugins/{$slug}/database/migrations/</comment>");
        $this->line("  3. Run <comment>php artisan plugin:install {$slug}</comment>");
        $this->line("  4. Read <comment>PLUGIN_DEVELOPMENT.md</comment> for all available hooks");
        $this->line('');

        return self::SUCCESS;
    }

    protected function writeFile(string $path, string $content): void
    {
        file_put_contents($path, $content);
        $this->line("  <fg=gray>created</> " . $this->relativePath($path));
    }

    protected function relativePath(string $path): string
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
    }

    // ─── Stubs ────────────────────────────────────────────────────────────────

    protected function stub_pluginJson(string $slug, string $studly, string $author, bool $premium): string
    {
        return json_encode([
            'id'               => $slug,
            'name'             => ucwords(str_replace('-', ' ', $slug)),
            'version'          => '1.0.0',
            'description'      => 'Describe what your plugin does.',
            'author'           => $author,
            'author_url'       => 'https://yoursite.com',
            'requires_core'    => '1.0.0',
            'requires_license' => $premium,
            'class'            => "Plugins\\{$studly}\\Plugin",
            'tags'             => [],
            'support_url'      => 'https://yoursite.com/support',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }

    protected function stub_pluginPhp(string $slug, string $studly, string $author, bool $premium): string
    {
        $licenseMethod = $premium
            ? "\n    public function requiresLicense(): bool { return true; }\n"
            : '';

        return <<<PHP
<?php

namespace Plugins\\{$studly};

use App\\Support\\AbstractPlugin;
use App\\Support\\Hooks;
use Filament\\Navigation\\NavigationItem;

/**
 * {$studly} Plugin
 *
 * Author: {$author}
 * Docs:   PLUGIN_DEVELOPMENT.md
 */
class Plugin extends AbstractPlugin
{
    public function getId(): string      { return '{$slug}'; }
    public function getName(): string    { return '{$studly}'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getAuthor(): string  { return '{$author}'; }{$licenseMethod}

    // ── Register your Filament pages, resources, widgets ─────────────────────

    public function getFilamentPages(): array
    {
        return [
            \\Plugins\\{$studly}\\Filament\\Pages\\{$studly}Page::class,
        ];
    }

    public function getFilamentResources(): array
    {
        return [
            // \\Plugins\\{$studly}\\Filament\\Resources\\MyModelResource::class,
        ];
    }

    public function getFilamentWidgets(): array
    {
        return [
            // \\Plugins\\{$studly}\\Filament\\Widgets\\MyWidget::class,
        ];
    }

    // ── Add navigation items to the sidebar ───────────────────────────────────

    public function getNavigationItems(): array
    {
        return [
            NavigationItem::make('{$studly}')
                ->url(fn () => \\Plugins\\{$studly}\\Filament\\Pages\\{$studly}Page::getUrl())
                ->icon('heroicon-o-puzzle-piece')
                ->group('{$studly}')  // Navigation group label
                ->sort(50),
        ];
    }

    // ── Routes, views, migrations, config ─────────────────────────────────────

    public function getMigrationPaths(): array
    {
        return [\$this->pluginPath('database/migrations')];
    }

    public function getRouteFiles(): array
    {
        return [\$this->pluginPath('routes/web.php')];
    }

    public function getViewNamespaces(): array
    {
        // Usage: view('{$slug}::page') or @include('{$slug}::partials.foo')
        return ['{$slug}' => \$this->pluginPath('resources/views')];
    }

    public function getConfigFiles(): array
    {
        // Usage: config('{$slug}.my_setting')
        return [\$this->pluginPath('config/{$slug}.php') => '{$slug}'];
    }

    // ── Hooks: extend or modify the core app ──────────────────────────────────

    protected function registerHooks(): void
    {
        // Example: run code after a payment
        // Hooks::action('payment.success', function(\$customer, float \$amount) {
        //     // Your code here
        // });

        // Example: add a column to the customer table
        // Hooks::filter('customer.table.columns', function(array \$columns) {
        //     \$columns[] = \\Filament\\Tables\\Columns\\TextColumn::make('my_field');
        //     return \$columns;
        // });

        // See PLUGIN_DEVELOPMENT.md for all available hooks.
    }

    // ── Install / Uninstall ───────────────────────────────────────────────────

    public function install(): void
    {
        \\Illuminate\\Support\\Facades\\Artisan::call('migrate', [
            '--path'  => 'plugins/{$slug}/database/migrations',
            '--force' => true,
        ]);
    }
}
PHP;
    }

    protected function stub_filamentPage(string $slug, string $studly): string
    {
        return <<<PHP
<?php

namespace Plugins\\{$studly}\\Filament\\Pages;

use Filament\\Pages\\Page;

class {$studly}Page extends Page
{
    protected static ?string \$navigationIcon  = 'heroicon-o-puzzle-piece';
    protected static ?string \$navigationLabel = '{$studly}';
    protected static ?string \$title           = '{$studly}';
    protected static ?string \$navigationGroup = '{$studly}';
    protected static string  \$view            = '{$slug}::page';

    // public static function canAccess(): bool
    // {
    //     return auth()->check();
    // }
}
PHP;
    }

    protected function stub_routes(string $slug): string
    {
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

/*
|--------------------------------------------------------------------------
| {$slug} Plugin Routes
|--------------------------------------------------------------------------
|
| Add your plugin's web routes here. They are loaded automatically.
|
*/

// Route::prefix('{$slug}')
//     ->middleware(['web', 'auth'])
//     ->group(function () {
//         // Route::get('/', [MyController::class, 'index'])->name('{$slug}.index');
//     });
PHP;
    }

    protected function stub_config(string $slug): string
    {
        return <<<PHP
<?php

/*
|--------------------------------------------------------------------------
| {$slug} Plugin Configuration
|--------------------------------------------------------------------------
|
| Access: config('{$slug}.setting_name')
| Override: Add to your .env or config/{$slug}.php in the main app.
|
*/

return [

    'enabled' => env(strtoupper(str_replace('-', '_', '{$slug}')) . '_ENABLED', true),

    // Add your settings here:
    // 'api_key' => env('{$slug}_API_KEY'),
    // 'timeout' => 30,

];
PHP;
    }

    protected function stub_view(string $studly): string
    {
        return <<<BLADE
<div>
    <h1 class="text-2xl font-bold mb-4">{$studly}</h1>

    <p class="text-gray-500">
        Your plugin page. Edit
        <code>plugins/.../resources/views/page.blade.php</code>
        to customise this view.
    </p>
</div>
BLADE;
    }
}
